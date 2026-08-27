import tkinter as tk
from . import gui
from rev import data, info

# 描画全体
def draw(canvas):
    canvas.delete(tk.ALL)   # 全削除

    # 描画
    for y in range(data.H):
        for x in range(data.W):
            rect = get_rect(x, y)
            draw_sq(canvas, rect)   # マス

            sq = data.board[y][x]
            if sq <= 1: draw_token(canvas, rect, sq)    # 石

            valid_sq = info.info['valid_board'][y][x]
            if valid_sq : draw_view(canvas, rect, 'valid')  # 配置可能マス

    # 配置マス
    put_x = info.info['put_x']; put_y = info.info['put_y']
    if put_x != -1: draw_view(canvas, get_rect(put_x, put_y), 'put')

# マス座標取得（左上xy、右下xy）
def get_rect(x, y):
    sq = gui.cnvs_sq
    rect = (x * sq, y * sq, (x + 1) * sq, (y + 1) * sq)
    return rect

# マス描画
def draw_sq(canvas, rect):
    x0, y0, x1, y1 = rect
    pad = 2

    # ずらしながら立体風に描画
    canvas.create_rectangle(x0, y0, x1, y1, fill='#006600', width=0)
    x0 += pad; y0 += pad; x1 -= pad; y1 -= pad
    canvas.create_rectangle(x0, y0, x1, y1, fill='#008800', width=0)
    x0 += pad; y0 += pad
    canvas.create_rectangle(x0, y0, x1, y1, fill='#00aa00', width=0)

# 石描画
def draw_token(canvas, rect, player):
    x0, y0, x1, y1 = rect
    margin = 8
    pad = 4

    # 外描画
    x0 += margin; y0 += margin; x1 -= margin; y1 -= margin
    col = ['#101010', '#f0f0f0'][player]
    canvas.create_oval(x0, y0, x1, y1, fill=col, width=0)

    # 内描画
    x0 += pad; y0 += pad; x1 -= pad; y1 -= pad
    col = ['#202020', '#ffffff'][player]
    canvas.create_oval(x0, y0, x1, y1, fill=col, width=0)

# 表示描画（valid:配置可能マス、put:配置マス）
def draw_view(canvas, rect, view='valid'):
    x0, y0, x1, y1 = rect
    pad = 4

    # 表示描画
    x0 += pad; y0 += pad; x1 -= pad; y1 -= pad
    col = {'valid': '#ffff88', 'put': '#88ffff'}[view]
    canvas.create_rectangle(x0, y0, x1, y1, fill='', outline=col, width=pad * 2)
