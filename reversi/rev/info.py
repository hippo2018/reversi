from . import data, op

info = {}

# 情報更新
def update():
    global info
    info = {}

    info['board'] = data.board
    info['player'] = data.player

    # マス数え
    info['count'] = [
        count_sq(data.board, 0),
        count_sq(data.board, 1),
    ]

    # 勝敗結果
    info['result'] = ['DRAW', 'DRAW']
    if info['count'][0] > info['count'][1]:
        info['result'] = ['WIN', 'LOSE']
    if info['count'][0] < info['count'][1]:
        info['result'] = ['LOSE', 'WIN']

    # 有効マス一覧取得
    info['valid_sqs'] = [
        get_valid_sqs(data.board, 0),
        get_valid_sqs(data.board, 1),
    ]

    # 終了確認
    info['valid_len'] = [
        len(info['valid_sqs'][0]),
        len(info['valid_sqs'][1]),
    ]
    info['is_end'] = info['valid_len'][0] == 0 and info['valid_len'][1] == 0   # 終了

    # 置けるか確認
    info['can_put'] = info['valid_len'][data.player] != 0

    # 置けるマスの盤面
    info['valid_board'] = [[False] * data.W for i in range(data.H)]
    for sq in info['valid_sqs'][data.player]:
        info['valid_board'][sq['y']][sq['x']] = True

    # 直前に置いたマス（GUI用）
    info['put_x'] = data.put_x
    info['put_y'] = data.put_y
    info['put_player'] = data.put_player

# マス数え
def count_sq(board, player):
    count = 0
    for y in range(data.H):
        for x in range(data.W):
            if board[y][x] == player:
                count += 1
    return count

# 有効マス一覧取得
def get_valid_sqs(board, player):
    valid_sqs = []
    for y in range(data.H):
        for x in range(data.W):
            sqs = op.test_put(board, x, y, player)
            if len(sqs) > 0:
                valid_sqs.append({'x': x, 'y': y})
    return valid_sqs
