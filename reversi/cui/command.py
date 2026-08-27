import sys

# 入力の取得
def get():
    res = {'x': 0, 'y': 0, 'err': False}

    try:
        line = input()
        pos = line.split()
        res['x'] = int(pos[0])
        res['y'] = int(pos[1])
    except KeyboardInterrupt:   # Ctrl+C
        sys.exit()
    except Exception:
        res['err'] = True

    return res
