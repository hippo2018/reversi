<?php

declare(strict_types=1);

final class ReversiGame
{
    public const BLACK = 0;
    public const WHITE = 1;
    public const BLANK = 2;
    public const SIZE = 8;

    private const DIRECTIONS = [
        [-1, -1], [0, -1], [1, -1],
        [-1, 0],           [1, 0],
        [-1, 1],  [0, 1],  [1, 1],
    ];

    private const VALUE_TABLE = [
        [64, 1, 8, 4, 4, 8, 1, 64],
        [1, 1, 10, 12, 12, 10, 1, 1],
        [8, 10, 14, 16, 16, 14, 10, 8],
        [4, 12, 16, 1, 1, 16, 12, 4],
        [4, 12, 16, 1, 1, 16, 12, 4],
        [8, 10, 14, 16, 16, 14, 10, 8],
        [1, 1, 10, 12, 12, 10, 1, 1],
        [64, 1, 8, 4, 4, 8, 1, 64],
    ];

    public static function defaultBoard(): array
    {
        $board = array_fill(0, self::SIZE, array_fill(0, self::SIZE, self::BLANK));
        $board[3][3] = self::WHITE;
        $board[3][4] = self::BLACK;
        $board[4][3] = self::BLACK;
        $board[4][4] = self::WHITE;

        return $board;
    }

    public static function validMoves(array $board, int $player): array
    {
        $moves = [];
        for ($y = 0; $y < self::SIZE; $y++) {
            for ($x = 0; $x < self::SIZE; $x++) {
                $reversible = self::testMove($board, $x, $y, $player);
                if (count($reversible) > 0) {
                    $moves[] = ['x' => $x, 'y' => $y, 'flips' => count($reversible)];
                }
            }
        }

        return $moves;
    }

    public static function applyMove(array $board, int $x, int $y, int $player): array
    {
        $reversible = self::testMove($board, $x, $y, $player);
        if (count($reversible) === 0) {
            throw new InvalidArgumentException('そのマスには置けません。');
        }

        $board[$y][$x] = $player;
        foreach ($reversible as $sq) {
            $board[$sq['y']][$sq['x']] = $player;
        }

        return [$board, count($reversible)];
    }

    public static function status(array $board, int $player, ?array $lastMove = null, array $events = []): array
    {
        $blackMoves = self::validMoves($board, self::BLACK);
        $whiteMoves = self::validMoves($board, self::WHITE);
        $counts = [
            self::BLACK => self::countStones($board, self::BLACK),
            self::WHITE => self::countStones($board, self::WHITE),
        ];
        $isEnd = count($blackMoves) === 0 && count($whiteMoves) === 0;

        $winner = null;
        if ($isEnd) {
            if ($counts[self::BLACK] > $counts[self::WHITE]) {
                $winner = self::BLACK;
            } elseif ($counts[self::BLACK] < $counts[self::WHITE]) {
                $winner = self::WHITE;
            } else {
                $winner = 'draw';
            }
        }

        return [
            'board' => $board,
            'player' => $player,
            'counts' => ['black' => $counts[self::BLACK], 'white' => $counts[self::WHITE]],
            'validMoves' => [
                'black' => self::stripFlipCounts($blackMoves),
                'white' => self::stripFlipCounts($whiteMoves),
                'current' => self::stripFlipCounts($player === self::BLACK ? $blackMoves : $whiteMoves),
            ],
            'canPut' => count($player === self::BLACK ? $blackMoves : $whiteMoves) > 0,
            'isEnd' => $isEnd,
            'winner' => $winner,
            'lastMove' => $lastMove,
            'events' => $events,
        ];
    }

    public static function playComputerTurn(array $board): array
    {
        $move = self::bestMove($board, self::WHITE, 0);
        if ($move === null) {
            return [$board, null];
        }

        [$board, $flips] = self::applyMove($board, $move['x'], $move['y'], self::WHITE);
        $move['player'] = self::WHITE;
        $move['flips'] = $flips;

        return [$board, $move];
    }

    public static function normalizePlayerAfterTurn(array $board, int $preferredPlayer, array &$events): int
    {
        $preferredMoves = self::validMoves($board, $preferredPlayer);
        if (count($preferredMoves) > 0) {
            return $preferredPlayer;
        }

        $other = 1 - $preferredPlayer;
        if (count(self::validMoves($board, $other)) > 0) {
            $events[] = [
                'type' => 'pass',
                'player' => $preferredPlayer,
                'message' => self::playerName($preferredPlayer) . 'は置けるマスがないためパスしました。',
            ];
            return $other;
        }

        return $preferredPlayer;
    }

    public static function playerName(int $player): string
    {
        return $player === self::BLACK ? '黒' : '白';
    }

    private static function testMove(array $board, int $x, int $y, int $player): array
    {
        if (!self::inBoard($x, $y) || $board[$y][$x] !== self::BLANK) {
            return [];
        }

        $result = [];
        foreach (self::DIRECTIONS as [$dx, $dy]) {
            array_push($result, ...self::getReverseLine($board, $x, $y, $dx, $dy, $player));
        }

        return $result;
    }

    private static function getReverseLine(array $board, int $x, int $y, int $dx, int $dy, int $player): array
    {
        $enemy = 1 - $player;
        $line = [];
        $step = 1;

        while (true) {
            $nx = $x + $dx * $step;
            $ny = $y + $dy * $step;
            if (!self::inBoard($nx, $ny)) {
                return [];
            }

            $cell = $board[$ny][$nx];
            if ($cell === $enemy) {
                $line[] = ['x' => $nx, 'y' => $ny];
            } elseif ($cell === $player) {
                return count($line) > 0 ? $line : [];
            } else {
                return [];
            }
            $step++;
        }
    }

    private static function bestMove(array $board, int $player, int $depth): ?array
    {
        $moves = self::validMoves($board, $player);
        $best = null;

        foreach ($moves as $move) {
            $value = self::VALUE_TABLE[$move['y']][$move['x']];
            $value += self::evaluateNext($board, $move['x'], $move['y'], $player, $depth);
            $move['score'] = $value;

            if ($best === null || $value > $best['score']) {
                $best = $move;
            }
        }

        return $best;
    }

    private static function evaluateNext(array $board, int $x, int $y, int $player, int $depth): int
    {
        if ($depth >= 2) {
            return 0;
        }

        [$nextBoard] = self::applyMove($board, $x, $y, $player);
        $enemyBest = self::bestMove($nextBoard, 1 - $player, $depth + 1);

        return $enemyBest === null ? 0 : -$enemyBest['score'];
    }

    private static function countStones(array $board, int $player): int
    {
        $count = 0;
        foreach ($board as $row) {
            foreach ($row as $cell) {
                if ($cell === $player) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private static function inBoard(int $x, int $y): bool
    {
        return $x >= 0 && $x < self::SIZE && $y >= 0 && $y < self::SIZE;
    }

    private static function stripFlipCounts(array $moves): array
    {
        return array_map(
            fn (array $move): array => ['x' => $move['x'], 'y' => $move['y']],
            $moves
        );
    }
}
