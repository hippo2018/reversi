import sys

TYPE_CUI = 'cui'
TYPE_GUI = 'gui'
ui_type = TYPE_CUI

# 実行時引数で初期化
def init_from_args():
    if len(sys.argv) >= 2 and sys.argv[1] == TYPE_GUI:
        global ui_type
        ui_type = TYPE_GUI
