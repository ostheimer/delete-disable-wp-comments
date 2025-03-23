const { defineConfig } = require('cypress')
const mysql = require('mysql2')

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8080',
    env: {
      admin_username: 'comment',
      admin_password: 'comment'
    },
    chromeWebSecurity: false,
    viewportWidth: 1280,
    viewportHeight: 800,
    setupNodeEvents(on, config) {
      // MySQL connection configuration
      const connection = mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: 'somewordpress',
        database: 'wordpress',
        port: 3306
      })

      // Register a task to execute MySQL queries
      on('task', {
        'mysql:query': (query) => {
          return new Promise((resolve, reject) => {
            connection.query(query, (error, results) => {
              if (error) reject(error)
              else resolve(results)
            })
          })
        }
      })
    },
    downloadsFolder: 'cypress/downloads'
  }
}) 