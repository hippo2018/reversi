<?php

declare(strict_types=1);

require_once __DIR__ . '/ReversiGame.php';

if (is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $pdo = database();
    $action = $_GET['action'] ?? 'state';
    $input = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($input)) {
        $input = [];
    }

    $response = match ($action) {
        'new' => createGame($pdo),
        'state' => readGame($pdo, (int)($_GET['id'] ?? $input['id'] ?? 0)),
        'move' => playMove($pdo, $input),
        default => throw new InvalidArgumentException('未対応の操作です。'),
    };

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code($e instanceof InvalidArgumentException ? 400 : 500);
    if (!$e instanceof InvalidArgumentException) {
        error_log((string)$e);
    }
    echo json_encode(['error' => $e instanceof InvalidArgumentException ? $e->getMessage() : 'サーバーでエラーが発生しました。管理者にお問い合わせください。'], JSON_UNESCAPED_UNICODE);
}

function database(): PDO
{
    $dataDir = defined('REVERSI_DATA_DIR') ? REVERSI_DATA_DIR : __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $dataDir . '/reversi.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            board TEXT NOT NULL,
            player INTEGER NOT NULL,
            last_move TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )'
    );
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS moves (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game_id INTEGER NOT NULL,
            player INTEGER NOT NULL,
            x INTEGER NOT NULL,
            y INTEGER NOT NULL,
            flips INTEGER NOT NULL,
            created_at TEXT NOT NULL,
            FOREIGN KEY(game_id) REFERENCES games(id)
        )'
    );

    return $pdo;
}

function createGame(PDO $pdo): array
{
    $board = ReversiGame::defaultBoard();
    $now = now();
    $stmt = $pdo->prepare('INSERT INTO games (board, player, last_move, created_at, updated_at) VALUES (:board, :player, NULL, :created, :updated)');
    $stmt->execute([
        ':board' => encodeJson($board),
        ':player' => ReversiGame::BLACK,
        ':created' => $now,
        ':updated' => $now,
    ]);

    return gamePayload((int)$pdo->lastInsertId(), $board, ReversiGame::BLACK, null, []);
}

function readGame(PDO $pdo, int $id): array
{
    [$gameId, $board, $player, $lastMove] = loadGame($pdo, $id);

    return gamePayload($gameId, $board, $player, $lastMove, []);
}

function playMove(PDO $pdo, array $input): array
{
    $id = (int)($input['id'] ?? 0);
    $x = (int)($input['x'] ?? -1);
    $y = (int)($input['y'] ?? -1);
    [$gameId, $board, $player] = loadGame($pdo, $id);

    if ($player !== ReversiGame::BLACK) {
        throw new InvalidArgumentException('現在はプレイヤーの手番ではありません。');
    }

    $events = [];
    [$board, $flips] = ReversiGame::applyMove($board, $x, $y, ReversiGame::BLACK);
    $lastMove = ['player' => ReversiGame::BLACK, 'x' => $x, 'y' => $y, 'flips' => $flips];
    recordMove($pdo, $gameId, ReversiGame::BLACK, $x, $y, $flips);

    $player = ReversiGame::normalizePlayerAfterTurn($board, ReversiGame::WHITE, $events);
    // 黒がパスした場合は、黒が打てるようになるか終局するまでCOMが続けて打つ。
    while ($player === ReversiGame::WHITE && ReversiGame::validMoves($board, ReversiGame::WHITE) !== []) {
        [$board, $computerMove] = ReversiGame::playComputerTurn($board);
        if ($computerMove !== null) {
            $lastMove = $computerMove;
            $events[] = [
                'type' => 'computer',
                'player' => ReversiGame::WHITE,
                'message' => '白が ' . chr(65 + $computerMove['x']) . ($computerMove['y'] + 1) . ' に置きました。',
            ];
            recordMove($pdo, $gameId, ReversiGame::WHITE, $computerMove['x'], $computerMove['y'], $computerMove['flips']);
        }
        $player = ReversiGame::normalizePlayerAfterTurn($board, ReversiGame::BLACK, $events);
    }

    saveGame($pdo, $gameId, $board, $player, $lastMove);

    return gamePayload($gameId, $board, $player, $lastMove, $events);
}

function gamePayload(int $id, array $board, int $player, ?array $lastMove, array $events): array
{
    return [
        'id' => $id,
        ...ReversiGame::status($board, $player, $lastMove, $events),
    ];
}

function loadGame(PDO $pdo, int $id): array
{
    if ($id <= 0) {
        throw new InvalidArgumentException('ゲームIDが不正です。');
    }

    $stmt = $pdo->prepare('SELECT id, board, player, last_move FROM games WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new InvalidArgumentException('ゲームが見つかりません。');
    }

    return [
        (int)$row['id'],
        decodeJson($row['board']),
        (int)$row['player'],
        $row['last_move'] ? decodeJson($row['last_move']) : null,
    ];
}

function saveGame(PDO $pdo, int $id, array $board, int $player, ?array $lastMove): void
{
    $stmt = $pdo->prepare('UPDATE games SET board = :board, player = :player, last_move = :last_move, updated_at = :updated WHERE id = :id');
    $stmt->execute([
        ':board' => encodeJson($board),
        ':player' => $player,
        ':last_move' => $lastMove === null ? null : encodeJson($lastMove),
        ':updated' => now(),
        ':id' => $id,
    ]);
}

function recordMove(PDO $pdo, int $gameId, int $player, int $x, int $y, int $flips): void
{
    $stmt = $pdo->prepare('INSERT INTO moves (game_id, player, x, y, flips, created_at) VALUES (:game_id, :player, :x, :y, :flips, :created_at)');
    $stmt->execute([
        ':game_id' => $gameId,
        ':player' => $player,
        ':x' => $x,
        ':y' => $y,
        ':flips' => $flips,
        ':created_at' => now(),
    ]);
}

function encodeJson(mixed $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function decodeJson(string $json): mixed
{
    return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
}

function now(): string
{
    return (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
}
