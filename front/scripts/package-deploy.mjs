import { cp, mkdir, copyFile } from 'node:fs/promises'
import { fileURLToPath } from 'node:url'
import path from 'node:path'

const root = fileURLToPath(new URL('../../', import.meta.url))
const release = path.join(root, 'release')
// Keep previous packages intact; never copy the development database.
const output = path.join(release, `reversi-${new Date().toISOString().replace(/[:.]/g, '-')}`)
const publicDir = path.join(output, 'public')
await mkdir(publicDir, { recursive: true })
await cp(path.join(root, 'front/dist'), publicDir, { recursive: true })
for (const name of ['api.php', 'ReversiGame.php']) {
  await copyFile(path.join(root, 'back', name), path.join(publicDir, name))
}
await cp(path.join(root, 'deploy/public'), publicDir, { recursive: true })
await cp(path.join(root, 'deploy/storage'), path.join(output, 'storage'), { recursive: true })
await copyFile(path.join(root, 'deploy/README.md'), path.join(output, 'README.md'))
console.log(`Upload package: ${output}`)
