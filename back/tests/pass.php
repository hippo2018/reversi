<?php

declare(strict_types=1);

require_once __DIR__ . '/../ReversiGame.php';

// 起動中のAPIに対して実行: php back/tests/pass.php
// 回帰確認用の新しい対局を1件作成します。
$base = $argv[1] ?? 'http://127.0.0.1:8001/api.php';

function request(string $action, ?array $body = null): array
{
    global $base;
    $context = stream_context_create(['http' => [
        'method' => $body === null ? 'GET' : 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR),
        'ignore_errors' => true,
    ]]);
    $response = file_get_contents($base . '?action=' . $action, false, $context);
    $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    if (isset($data['error'])) {
        throw new RuntimeException($data['error']);
    }
    return $data;
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// 白だけ置けない場合は、黒に手番が戻る。
$board = array_fill(0, 8, array_fill(0, 8, ReversiGame::BLACK));
$board[0][1] = ReversiGame::WHITE;
$board[0][2] = ReversiGame::BLANK;
$events = [];
check(ReversiGame::normalizePlayerAfterTurn($board, ReversiGame::WHITE, $events) === ReversiGame::BLACK, 'White did not pass');
check(count($events) === 1 && $events[0]['type'] === 'pass' && $events[0]['player'] === ReversiGame::WHITE, 'Missing white pass event');

// 空きマスが残っていても、両者が置けなければ終局する。
$board[0][1] = ReversiGame::BLACK;
$events = [];
ReversiGame::normalizePlayerAfterTurn($board, ReversiGame::WHITE, $events);
$ended = ReversiGame::status($board, ReversiGame::WHITE);
check($ended['isEnd'] && $ended['winner'] === ReversiGame::BLACK && $events === [], 'Both blocked should end without repeated passes');

// 実際の対局で黒のパスによる停止を再現した手順。
$sequence = explode(' ', 'D3 F3 E6 D6 F7 F4 C6 B4 E1 F8 F2 G6 C1 C2 A5 A6 H3 H6 D8 G1 H4 B1 B2 B8 A2 H1 B7 A7');
$game = request('new');
$passes = [0 => 0, 1 => 0];
$continued = false;
for ($turn = 0; !$game['isEnd'] && $turn < 60; $turn++) {
    check($game['player'] === 0 && count($game['validMoves']['current']) > 0, 'Game stalled before black could move');
    if (isset($sequence[$turn])) {
        $square = $sequence[$turn];
        $move = ['x' => ord($square[0]) - ord('A'), 'y' => (int)$square[1] - 1];
    } else {
        $move = $game['validMoves']['current'][0];
    }
    $game = request('move', ['id' => $game['id'], ...$move]);
    foreach ($game['events'] as $index => $event) {
        if ($event['type'] === 'pass') {
            $passes[$event['player']]++;
            if ($event['player'] === 0) {
                check(($game['events'][$index + 1]['type'] ?? '') === 'computer', 'COM did not continue after black passed');
                $continued = true;
            }
        }
    }
    $saved = request('state&id=' . $game['id']);
    check($saved['board'] === $game['board'] && $saved['player'] === $game['player'], 'Saved state differs from response');
}
check($continued, 'Black pass regression was not exercised');
check($game['isEnd'], 'Game did not finish');
check($game['validMoves']['black'] === [] && $game['validMoves']['white'] === [], 'Game ended with legal moves');
check($game['winner'] !== null, 'Missing result');
echo 'PASS: black passes=' . $passes[0] . ', white passes=' . $passes[1] . ', final=' . json_encode($game['counts']) . PHP_EOL;
