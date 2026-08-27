import tkinter as tk
import tkinter.messagebox as messagebox
from . import gui, board_render, tarea
from rev import data, info
from ui import render, proc
from think import com

# 初期化
def init(root):
    global canvas
    canvas = tk.Canvas(root, width=gui.cnvs_w, height=gui.cnvs_h, borderwidth=-2)
    canvas.place(x=gui.cnvs_x, y=gui.cnvs_y)
    canvas.bind('<Button-1>', put)
    draw()

# 描画
def draw():
    board_render.draw(canvas)

# キャンバス石置き
def put(event):
    # ユーザーでないなら終了
    if data.player != 0: return

    # クリック座標の計算
    brd_x = int(event.x // gui.cnvs_sq)
    brd_y = int(event.y // gui.cnvs_sq)

    # 石置き
    tarea.vprint('')
    data.record_put(brd_x, brd_y, data.player)
    proc.put(brd_x, brd_y)
    draw()

    # 処理の分岐
    if info.info['is_end']:
        canvas.after(1000, end)         # 終了時処理
    elif data.player == 1:
        canvas.after(500, exec_com)     # COM時処理

# COM実行
def exec_com():
    res = com.get_pos()
    render.print_com_pos(res)
    data.record_put(res['x'], res['y'], data.player)
    proc.put(res['x'], res['y'])
    draw()

    # 処理の分岐
    if info.info['is_end']:
        canvas.after(1000, end)     # 終了時処理
    elif data.player == 1:
        canvas.after(500, exec_com) # 人スキップ時処理

# ゲーム終了
def end():
    messagebox.showinfo('ゲーム終了', info.info['result'][0])
