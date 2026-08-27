from . import command
from rev import info, data
from ui import proc, render
from think import com

# メイン ループ
def loop():
    proc.start()    # 開始

    while True:
        if info.info['is_end']:
            break

        if data.player == 0:
            print('input: x y => "3 2"')
            res = command.get()
            print(' ')

            if res['err']:
                print('Error')
                continue
        else:
            res = com.get_pos()
            render.print_com_pos(res)

        proc.put(res['x'], res['y'])
