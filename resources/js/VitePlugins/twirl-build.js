import {promises as fsPromises, readFileSync, writeFileSync} from 'fs'

// prepend "(()=>{" to js files

const twirlBuildPlugin = () => ({
  name: 'twirl-build-plugin',
  async closeBundle() {
    try {

      const assets = [
          'twirl.js',
      ]

      for (let i in assets) {
        let filePath = 'public/js/' + assets[i]
        let data = readFileSync(filePath)
        let insert = Buffer.from('(()=>{')

        // Use writeFileSync without the third argument for encoding
        writeFileSync(filePath, insert)
        writeFileSync(filePath, data, {flag: 'a'}) // Use { flag: 'a' } for append mode

        // append "})()" to app.js
        await fsPromises.appendFile(filePath, '})()')
      }
    } catch (e) {
      console.error(e)
    }
  },
})

export default twirlBuildPlugin
