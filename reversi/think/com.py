from rev import data, info, op
import copy

# COMの指し手を取得
def get_pos():
    param = think(data.board, data.player, nest=0)
    return param['sq']

# 思考（COMの内部処理）
def think(board, player, nest):
    param = {'value': -9999, 'sq': None}
    valid_sqs = info.get_valid_sqs(board, player)
    for sq in valid_sqs:
        x, y = sq['x'], sq['y']
        value = eval_table(x, y)    # 盤面評価表の利用
        value += eval_next(x, y, board, player, nest)   # 次手確認
        if value > param['value'] or param['sq'] is None:
            param = {'value': value, 'sq': sq}
    return param

# 盤面評価表の利用
def eval_table(x, y):
    value_table = [
        [64,  1,  8,  4,  4,  8,  1, 64],
        [ 1,  1, 10, 12, 12, 10,  1,  1],
        [ 8, 10, 14, 16, 16, 14, 10,  8],
        [ 4, 12, 16,  1,  1, 16, 12,  4],
        [ 4, 12, 16,  1,  1, 16, 12,  4],
        [ 8, 10, 14, 16, 16, 14, 10,  8],
        [ 1,  1, 10, 12, 12, 10,  1,  1],
        [64,  1,  8,  4,  4,  8,  1, 64]
    ]
    return value_table[y][x]

# 次手確認（石を配置した場合の"次の"プレイヤーの点数を引く）
def eval_next(x, y, board, player, nest):
    # nestが2以上のとき
    if nest >= 2: return 0

    # 裏返し処理
    board2 = copy.deepcopy(board)   # 深い複製
    sqs = op.test_put(board2, x, y, player)
    op.put(board2, x, y, sqs, player)       # 石を置く

    # 相手方手番
    enemy = 1 - player
    param = think(board2, enemy, nest + 1)  # 再度思考
    return - param['value']     # 次の手番の最大評価値を引く
