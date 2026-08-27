from . import win, tarea, button, board
from rev import info, data
from ui import proc

# 開始
def start():
    info.update()  # 情報更新

    root = win.init_win()
    tarea.init(root)
    button.init(root)
    board.init(root)

    proc.start()    # 開始
    if data.player == 1:
        board.exec_com()

    root.mainloop()
