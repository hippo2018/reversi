# ウィンドウの設定
win_title = 'リバーシ'
win_x = 100
win_y = 100
win_w = 900
win_h = 600
win_bg = '#ffffcc'

# キャンバスの設定
cnvs_x = 0
cnvs_y = 0
cnvs_w = win_h
cnvs_h = win_h
cnvs_sq = win_h / 8

# マージン
margin = 10

# リセット ボタンの設定
btn_x = cnvs_x + cnvs_w + margin
btn_y = margin
btn_w = win_w - btn_x - margin
btn_h = 40
btn_font = ('', 16, 'bold')

# 入力欄の設定
txt_x = btn_x
txt_y = btn_y + btn_h + margin
txt_w = btn_w
txt_h = win_h - txt_y - margin
