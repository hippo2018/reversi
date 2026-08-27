# python -B main.py   or   python -B main.py gui

import config
from cui import start as cui
from gui import start as gui

#from rev import data, board_group
#data.board = board_group.get(['win', 'lose', 'skip_e', 'skip_m'][3])

config.init_from_args()
if config.ui_type == config.TYPE_CUI:
    cui.loop()      # メイン ループ
if config.ui_type == config.TYPE_GUI:
    gui.start()     # 開始
