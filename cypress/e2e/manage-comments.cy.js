describe('Delete & Disable Comments Plugin', () => {
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
    `);

    // Ensure comments are enabled in WordPress options
    cy.task('mysql:query', `
      UPDATE wp_options 
      SET option_value = '0' 
      WHERE option_name = 'disable_comments'
    `);
    
    // Reset post comment status to open
    cy.task('mysql:query', `
      UPDATE wp_posts 
      SET comment_status = 'open', 
          ping_status = 'open' 
      WHERE ID = 1
    `);
  })

  beforeEach(() => {
    cy.visit('/wp-admin')
    cy.get('#user_login').type('comment')
    cy.get('#user_pass').type('comment')
    cy.get('#wp-submit').click()
    cy.visit('/wp-admin/tools.php?page=delete-disable-comments')
  })

  it('should show all UI elements', () => {
    cy.get('[data-cy="delete-spam-btn"]').should('be.visible')
    cy.get('[data-cy="delete-all-btn"]').should('be.visible')
    cy.get('[data-cy="backup-btn"]').should('be.visible')
    cy.get('#toggle-comments').should('be.visible')
  })

  it('should delete spam comments', () => {
    cy.get('[data-cy="delete-spam-btn"]').click()
    cy.get('[data-cy="confirm-dialog"]').should('be.visible')
    cy.get('[data-cy="confirm-dialog-confirm"]').click()
    cy.get('[data-cy="status-message"]')
        .should('be.visible')
        .and('contain', 'Spam comments deleted successfully')
  })

  it('should download comment backup', () => {
    cy.get('[data-cy="backup-btn"]').click()
    cy.get('[data-cy="status-message"]')
        .should('be.visible')
        .and('contain', 'Backup downloaded successfully')
  })

  it('should delete all comments', () => {
    cy.get('[data-cy="delete-all-btn"]').click()
    cy.get('[data-cy="confirm-dialog"]').should('be.visible')
    cy.get('[data-cy="confirm-dialog-confirm"]').click()
    cy.get('[data-cy="status-message"]')
        .should('be.visible')
        .and('contain', 'All comments deleted successfully')
  })

  it('should toggle comments', () => {
    // Check initial state
    cy.get('#toggle-comments').should('not.be.checked')
    cy.get('.toggle-label').should('contain', 'Comments are currently enabled')

    // Click toggle
    cy.get('#toggle-comments').click()

    // Verify toggle is checked and label updated
    cy.get('#toggle-comments').should('be.checked')
    cy.get('.toggle-label').should('contain', 'Comments are currently disabled')

    // Toggle back
    cy.get('#toggle-comments').click()

    // Verify toggle is unchecked and label updated
    cy.get('#toggle-comments').should('not.be.checked')
    cy.get('.toggle-label').should('contain', 'Comments are currently enabled')
  })

  it('should maintain toggle state and appearance after page reload', () => {
    // Enable comments
    cy.get('#toggle-comments').click()
    cy.get('#toggle-comments').should('be.checked')
    cy.get('.toggle-label').should('contain', 'Comments are currently disabled')

    // Reload page
    cy.reload()

    // Verify state is maintained
    cy.get('#toggle-comments').should('be.checked')
    cy.get('.toggle-label').should('contain', 'Comments are currently disabled')

    // Clean up - disable comments
    cy.get('#toggle-comments').click()
    cy.get('#toggle-comments').should('not.be.checked')
    cy.get('.toggle-label').should('contain', 'Comments are currently enabled')
  })

  after(() => {
    // Ensure comments are enabled after tests
    cy.get('#toggle-comments').then($toggle => {
      if ($toggle.is(':checked')) {
        cy.get('#toggle-comments').click()
      }
    })
  })
}) 