import config
from gui import tarea

sq_view = ['[o]', '[x]', '[ ]', '[*]']

# テキスト出力
def print_t(t):
    if config.ui_type == config.TYPE_CUI:
        print(t)
    if config.ui_type == config.TYPE_GUI:
        tarea.vprint(t)

# 情報出力
def print_info(info):
    t_board = to_string_board(info['board'], info['valid_board'])
    t_info = to_string_info(info)
    print_t(t_board + '\n' + t_info)

# 情報テキスト
def to_string_info(info):
    c = info['count']
    t_score = f'Score 0: {c[0]}, 1: {c[1]}'

    p = info['player']
    t_player = f'Player {p}: {sq_view[p]}'

    return t_score + '\n\n' + t_player

# 盤面テキスト
def to_string_board(board, valid_board):
    lines = ['    x0 x1 x2 x3 x4 x5 x6 x7']
    for y, sq_row in enumerate(board):  # 1行取得
        line = f'y{y} '
        for x, sq in enumerate(sq_row): # 1マス取得
            if valid_board[y][x]:
                line += sq_view[-1]
            else:
                line += sq_view[sq]
        lines.append(line)
    return '\n'.join(lines)

# COM位置出力
def print_com_pos(pos):
    print_t(f'com: {pos['x']} {pos['y']}\n')
