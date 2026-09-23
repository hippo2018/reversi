import { useEffect, useMemo, useState } from 'react'
import './App.css'

type Cell = 0 | 1 | 2
type Player = 0 | 1
type Move = {
  x: number
  y: number
}
type LastMove = Move & {
  player: Player
  flips: number
}
type GameState = {
  id: number
  board: Cell[][]
  player: Player
  counts: {
    black: number
    white: number
  }
  validMoves: {
    black: Move[]
    white: Move[]
    current: Move[]
  }
  canPut: boolean
  isEnd: boolean
  winner: Player | 'draw' | null
  lastMove: LastMove | null
  events: Array<{ message: string }>
}

const apiBase = import.meta.env.VITE_API_BASE ?? '/api.php'
const labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']

function App() {
  const [game, setGame] = useState<GameState | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [message, setMessage] = useState('対局を準備しています')
  const [error, setError] = useState('')

  const validMoveKeys = useMemo(() => {
    return new Set(game?.validMoves.current.map((move) => `${move.x}:${move.y}`) ?? [])
  }, [game])

  const startGame = async () => {
    setIsLoading(true)
    setError('')
    try {
      const nextGame = await requestGame('new')
      setGame(nextGame)
      setMessage('黒の手番です。ガイドが出ているマスに置けます。')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'ゲームを開始できませんでした。')
    } finally {
      setIsLoading(false)
    }
  }

  useEffect(() => {
    startGame()
  }, [])

  const playMove = async (x: number, y: number) => {
    if (!game || game.isEnd || !validMoveKeys.has(`${x}:${y}`)) {
      return
    }

    setIsLoading(true)
    setError('')
    try {
      const nextGame = await requestGame('move', { id: game.id, x, y })
      setGame(nextGame)
      setMessage(buildStatusMessage(nextGame))
    } catch (err) {
      setError(err instanceof Error ? err.message : '石を置けませんでした。')
    } finally {
      setIsLoading(false)
    }
  }

  const leadText = game ? getLeadText(game) : '黒 0 - 白 0'

  return (
    <main className="app-shell">
      <section className="play-area" aria-label="リバーシ">
        <div className="headline">
          <p className="eyebrow">React + PHP + SQLite</p>
          <h1>リバーシ</h1>
          <p>{message}</p>
        </div>

        <div className="board-wrap">
          <div className="corner-label" />
          {labels.map((label) => (
            <div className="axis-label" key={label}>{label}</div>
          ))}
          {game?.board.map((row, y) => (
            <div className="board-row" key={y}>
              <div className="row-label">{y + 1}</div>
              {row.map((cell, x) => {
                const isValid = validMoveKeys.has(`${x}:${y}`)
                const isLast = game.lastMove?.x === x && game.lastMove?.y === y

                return (
                  <button
                    aria-label={`${labels[x]}${y + 1}`}
                    className={`cell ${isValid ? 'valid' : ''} ${isLast ? 'last' : ''}`}
                    disabled={isLoading || !isValid || game.isEnd}
                    key={`${x}-${y}`}
                    onClick={() => playMove(x, y)}
                    type="button"
                  >
                    {cell !== 2 && <span className={`stone ${cell === 0 ? 'black' : 'white'}`} />}
                  </button>
                )
              })}
            </div>
          ))}
        </div>
      </section>

      <aside className="side-panel">
        <div className="scoreboard">
          <div>
            <span className="small-label">黒</span>
            <strong>{game?.counts.black ?? 0}</strong>
          </div>
          <div>
            <span className="small-label">白</span>
            <strong>{game?.counts.white ?? 0}</strong>
          </div>
        </div>

        <div className="status-box">
          <span className="small-label">状況</span>
          <strong>{leadText}</strong>
          <p>{game?.isEnd ? resultText(game) : `現在の手番: ${game?.player === 0 ? '黒' : '白'}`}</p>
        </div>

        <div className="status-box">
          <span className="small-label">置けるマス</span>
          <strong>{game?.validMoves.current.length ?? 0}</strong>
          <p>{game?.validMoves.current.map((move) => `${labels[move.x]}${move.y + 1}`).join(' / ') || 'なし'}</p>
        </div>

        {game?.events.length ? (
          <div className="status-box">
            <span className="small-label">直近の動き</span>
            {game.events.map((event, index) => (
              <p key={`${event.message}-${index}`}>{event.message}</p>
            ))}
          </div>
        ) : null}

        {error && <p className="error">{error}</p>}

        <button className="primary-action" disabled={isLoading} onClick={startGame} type="button">
          新しい対局
        </button>
      </aside>
    </main>
  )
}

async function requestGame(action: string, body?: Record<string, unknown>) {
  const response = await fetch(`${apiBase}?action=${action}`, {
    method: body ? 'POST' : 'GET',
    headers: body ? { 'Content-Type': 'application/json' } : undefined,
    body: body ? JSON.stringify(body) : undefined,
  })
  const data = await response.json()
  if (!response.ok) {
    throw new Error(data.error ?? '通信に失敗しました。')
  }
  return data as GameState
}

function buildStatusMessage(game: GameState) {
  if (game.isEnd) {
    return resultText(game)
  }
  if (game.events.length > 0) {
    return game.events[game.events.length - 1].message
  }
  return `${game.player === 0 ? '黒' : '白'}の手番です。`
}

function resultText(game: GameState) {
  if (game.winner === 'draw') {
    return '引き分けです。'
  }
  return `${game.winner === 0 ? '黒' : '白'}の勝ちです。`
}

function getLeadText(game: GameState) {
  const black = game.counts.black
  const white = game.counts.white
  if (black === white) {
    return `黒 ${black} - 白 ${white}`
  }
  return black > white ? `黒が${black - white}枚リード` : `白が${white - black}枚リード`
}

export default App
