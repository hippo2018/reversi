from rev import data, op, info
from ui import render

# 開始
def start():
    info.update()  # 情報更新
    render.print_info(info.info)    # 情報出力

    # 初回スキップ用処理（デバッグ時用）
    if info.info['valid_len'][0] == 0:
        data.next()

# 石置き
def put(x, y):
    # 石置きテスト（裏返せるマスのリストを得る）
    sqs = op.test_put(data.board, x, y, data.player)
    if len(sqs) == 0:
        render.print_t('Illegal Input.')
        return

    # 実際に石を置く
    op.put(data.board, x, y, sqs, data.player)

    # 次の手番に移行
    data.next()     # 次のプレイヤー
    info.update()   # 情報更新
    render.print_info(info.info)    # 情報出力

    # 進行確認
    if info.info['is_end']:
        render.print_t(f'Game End.  {info.info['result'][0]}\n')
    elif not info.info['can_put']:
        render.print_t('Skip.\n')
        data.next()     # 次のプレイヤー
        info.update()   # 情報更新
        render.print_info(info.info)    # 情報出力
