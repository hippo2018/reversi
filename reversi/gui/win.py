import tkinter as tk
from . import gui

# ウィンドウ初期化
def init_win():
    root = tk.Tk()
    root.title(gui.win_title)
    root.geometry(f'{gui.win_w}x{gui.win_h}+{gui.win_x}+{gui.win_y}')
    root.resizable(False, False)
    root.config(bg=gui.win_bg)
    return root
