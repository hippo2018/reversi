from . import data

# 石を置く（盤面、x、y, 裏返せるマスの, プレイヤー）
def put(board, x, y, sqs, player):
    board[y][x] = player   # 石を置いたマス
    for sq in sqs:
        board[sq['y']][sq['x']] = player   # 裏返したマス

# 石を置けるか確認（戻り値は、裏返せるマスのリスト）
def test_put(board, x, y, player):
    # 石を置けるか確認
    if in_board(x, y) == False: return []   # 盤面範囲外
    if board[y][x] != data.BLANK: return [] # すでに石がある

    # 8方向のマスのリストを得る
    lines = get8dir(board, x, y)

    # 裏返せるマスのリストを得る
    sqs = []
    for line in lines:
        sqs.extend(get_reverse(line, player))   # 裏返せるマスのリストを追加
    return sqs

# 盤面の範囲内か
def in_board(x, y):
    return 0 <= x < data.W and 0 <= y < data.H

# 基点のXYから8方向のマスを取得
def get8dir(board, x, y):
    dirs = [    # 8方向
        {'x': -1, 'y': -1}, {'x': 0, 'y': -1}, {'x':  1, 'y': -1}, 
        {'x': -1, 'y':  0},                    {'x':  1, 'y':  0},
        {'x': -1, 'y':  1}, {'x': 0, 'y':  1}, {'x':  1, 'y':  1}
    ]
    lines = []
    for dir in dirs:
        lines.append(get_line(board, x, y, dir['x'], dir['y']))
    return lines

# 1方向のマスの一覧を得る
def get_line(board, start_x, start_y, dir_x, dir_y):
    res = []
    move = 1    # マス移動位置
    while True:
        x = start_x + move * dir_x
        y = start_y + move * dir_y
        if not in_board(x, y): break    # 範囲外
        res.append({'x': x, 'y': y, 'p': board[y][x]})
        move += 1
    return res

# 裏返せるマスのリストを得る
def get_reverse(line, player):
    enemy = 1 - player

    # 前提条件
    if len(line) < 2: return []         # 2マスない → はさめない
    if line[0]['p'] != enemy: return [] # 1マス目が敵でない → はさめない
                                        # 1マス目が敵 → 処理を継続する

    # マスをたどっていく
    res = [line[0]]     # 1マス目を格納
    for sq in line[1:]:
        if sq['p'] == enemy: res.append(sq) # 敵マス→リストに追加
        if sq['p'] == data.BLANK: return [] # 空マス→失敗
        if sq['p'] == player: return res;   # 自石→成功 リストを返す
    return []   # 末尾まで自石ではさめなかった
