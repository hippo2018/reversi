import tkinter as tk
from . import gui, tarea, board
from rev import data
from ui import proc

# 初期化
def init(root):
    btn = tk.Button(root, text='RESET', command=push, font=gui.btn_font)
    btn.place(x=gui.btn_x, y=gui.btn_y, width=gui.btn_w, height=gui.btn_h)

# クリック時の処理
def push():
    tarea.reset()
    data.reset()
    proc.start()    # 開始
    board.draw()
