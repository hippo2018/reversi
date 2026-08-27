from . import board_group

# 盤面
BLANK = 2   # 空マス
W = 8   # 盤面横幅
H = 8   # 高さ
board = board_group.get('default')

# 盤面変更
def change_board(x, y, state):
    try:
        board[y][x] = state
    except Exception:
        pass

# ------------------------------------------------------------
# プレイヤー
player = 0

# 次のプレイヤー
def next():
    global player
    player = 1 - player

# ------------------------------------------------------------
# 直前に置いたマス（GUI用）
put_x = -1
put_y = -1
put_player = 2

# 置いたマスの記録
def record_put(x, y, player):
    global put_x, put_y, put_player
    put_x = x
    put_y = y
    put_player = player

# ------------------------------------------------------------
# リセット
def reset():
    global board, player, put_x, put_y, put_player
    board = board_group.get('default')
    player = 0
    put_x = -1
    put_y = -1
    put_player = 2
