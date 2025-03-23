describe('Manage Comments Plugin Tests', () => {
  before(() => {
    // Create test comments and spam comments
    cy.task('mysql:query', `
      INSERT INTO wp_comments (comment_post_ID, comment_author, comment_author_email, comment_content, comment_approved)
      VALUES 
      (1, 'Test User 1', 'test1@example.com', 'This is a normal comment 1', '1'),
      (1, 'Test User 2', 'test2@example.com', 'This is a normal comment 2', '1'),
      (1, 'Test User 3', 'test3@example.com', 'This is a normal comment 3', '1'),
      (1, 'Spam User 1', 'spam1@example.com', 'This is a spam comment 1', 'spam'),
      (1, 'Spam User 2', 'spam2@example.com', 'This is a spam comment 2', 'spam'),
      (1, 'Spam User 3', 'spam3@example.com', 'This is a spam comment 3', 'spam')
    `)
  })

  beforeEach(() => {
    // Login to WordPress admin
    cy.visit('/wp-login.php')
    cy.wait(200)
    cy.get('#user_login').type('comment')
    cy.get('#user_pass').type('comment')
    cy.get('#wp-submit').click()

    // Navigate to plugin page
    cy.visit('/wp-admin/admin.php?page=manage-comments')
    cy.wait(1000)
  })

  it('should display all UI elements correctly', () => {
    cy.get('h2').contains('Delete Spam Comments')
    cy.get('h2').contains('Delete All Comments')
    cy.get('h2').contains('Disable Comments')
    cy.get('#delete-spam-comments').should('exist')
    cy.get('#backup-comments').should('exist')
    cy.get('#delete-all-comments').should('exist')
    cy.get('#toggle-comments').should('exist')
  })

  it('should delete spam comments', () => {
    cy.task('mysql:query', 'SELECT COUNT(*) as count FROM wp_comments WHERE comment_approved = "spam"')
      .then((result) => {
        const initialCount = result[0].count
        cy.get('#delete-spam-comments').click()
        cy.on('window:confirm', () => true)
        cy.contains(`${initialCount} spam comments have been deleted`).should('be.visible')
        cy.task('mysql:query', 'SELECT COUNT(*) as count FROM wp_comments WHERE comment_approved = "spam"')
          .then((result) => {
            expect(result[0].count).to.be.lessThan(initialCount)
          })
      })
  })

  it('should download comments backup', () => {
    const today = new Date()
    const formattedDate = today.toISOString().split('T')[0] // Format: YYYY-MM-DD
    cy.get('#backup-comments').click()
    cy.readFile(`cypress/downloads/comments-backup-${formattedDate}.csv`, { timeout: 10000 }).should('exist')
  })

  it('should delete all comments', () => {
    cy.task('mysql:query', 'SELECT COUNT(*) as count FROM wp_comments')
      .then((result) => {
        const initialCount = result[0].count
        cy.get('#delete-all-comments').click()
        cy.on('window:confirm', () => true)
        cy.contains(`${initialCount} comments have been deleted`).should('be.visible')
        cy.task('mysql:query', 'SELECT COUNT(*) as count FROM wp_comments')
          .then((result) => {
            expect(result[0].count).to.equal(0)
          })
      })
  })

  it('should toggle comments', () => {
    // Get initial state
    cy.get('#toggle-comments').then(($toggle) => {
      const isChecked = $toggle.prop('checked')
      cy.get('#toggle-comments').click({ force: true })
      cy.wait(2000)

      if (!isChecked) {
        cy.contains('Kommentare wurden site-weit deaktiviert').should('be.visible')
      } else {
        cy.contains('Kommentare wurden site-weit aktiviert').should('be.visible')
      }
    })
  })

  it('should maintain toggle state and appearance after page reload', () => {
    // Überprüfe die Layout-Struktur
    cy.get('.toggle-container').should('exist')
        .and('have.css', 'display', 'flex')
        .and('have.css', 'align-items', 'center')

    // Get initial state
    cy.get('#toggle-comments').then(($toggle) => {
      const initialState = $toggle.prop('checked')
      
      // Toggle state
      cy.get('#toggle-comments').click({ force: true })
      cy.wait(2000)

      // Verify changed state
      cy.get('#toggle-comments').should('have.prop', 'checked', !initialState)
      cy.get('.comment-status')
          .should('have.class', initialState ? 'enabled' : 'disabled')
          .invoke('text')
          .then((text) => text.trim())
          .should('eq', initialState ? 
              'Comments are currently enabled' : 
              'Comments are currently disabled')
      cy.get('.comment-status')
          .should('have.css', 'color', initialState ? 
              'rgb(34, 113, 177)' : // #2271b1
              'rgb(204, 204, 204)') // #cccccc

      // Reload page
      cy.reload()
      cy.wait(2000)

      // Verify state persists
      cy.get('#toggle-comments').should('have.prop', 'checked', !initialState)
      cy.get('.comment-status')
          .should('have.class', initialState ? 'enabled' : 'disabled')
          .invoke('text')
          .then((text) => text.trim())
          .should('eq', initialState ? 
              'Comments are currently enabled' : 
              'Comments are currently disabled')
      cy.get('.comment-status')
          .should('have.css', 'color', initialState ? 
              'rgb(34, 113, 177)' : // #2271b1
              'rgb(204, 204, 204)') // #cccccc
    })
  })

  after(() => {
    // Clean up all test comments
    cy.task('mysql:query', 'DELETE FROM wp_comments WHERE comment_author LIKE "Test User%" OR comment_author LIKE "Spam User%"')
  })
}) 