import tkinter as tk
import tkinter.scrolledtext as scrolledtext
from . import gui

# 初期化
def init(root):
    global tarea, tarea_text
    tarea = scrolledtext.ScrolledText(root)
    tarea.place(x=gui.txt_x, y=gui.txt_y, width=gui.txt_w, height=gui.txt_h)
    tarea_text = ''

# リセット
def reset():
    global tarea_text
    tarea_text = ''
    tarea.delete('1.0', tk.END)     # 先頭から末尾まで消す

# 仮想プリント
def vprint(text):
    try:
        global tarea_text
        tarea_text += text + '\n'
        tarea.delete('1.0', tk.END)     # 先頭から末尾まで消す
        tarea.insert('1.0', tarea_text) # 先頭にテキストを挿入
        tarea.see(tk.END)               # 末尾に移動
    except Exception:
        pass
