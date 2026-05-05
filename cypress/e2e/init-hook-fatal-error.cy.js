/**
 * Regression coverage for https://github.com/ostheimer/delete-disable-wp-comments/issues/1
 *
 * Background:
 *   In v1.0.1 the `ddwpc_init()` function (registered on the public `init`
 *   action with priority 100) iterated over every post in the database and
 *   called `wp_update_post()` for each one whenever the operator had toggled
 *   "Disable comments site-wide" to ON. This had two pathological effects:
 *
 *     1. Every uncached frontend / AJAX / REST / cron request triggered N
 *        `wp_update_post()` calls (N = number of posts in the DB), which
 *        in turn fired `save_post`, `transition_post_status`, and
 *        `wp_after_insert_post` hooks — making any third-party plugin that
 *        listens on those hooks fire repeatedly and slowly.
 *     2. Plugins like WPML eventually crashed with an `Uncaught TypeError`
 *        because the post-context they expected to receive in those hooks
 *        was `null` when the loop fired from the public `init`. The whole
 *        site went down with HTTP 500 / WSOD.
 *
 *   The fix moves the bulk-close operation out of `init` and into:
 *     - the activation hook,
 *     - the explicit toggle action, and
 *     - a new manual "Close all comments now" admin button.
 *   It also replaces `wp_update_post()` with a single safe SQL UPDATE so
 *   it never triggers `save_post`-listening plugins.
 *
 * These tests document the contract and serve as a baseline that can be
 * ported to Playwright if the project moves test runners.
 */

const ADMIN = 'comment';

describe('Bug fix: init hook no longer crashes the site (#1)', () => {
    before(() => {
        // Reset to a known state: 25 fixture posts with comments enabled, plugin off.
        cy.task('mysql:query', `DELETE FROM wp_posts WHERE post_title LIKE 'ddwpc-fixture-%'`);
        for (let i = 0; i < 25; i++) {
            cy.task('mysql:query', `
                INSERT INTO wp_posts (
                    post_title, post_content, post_status, comment_status, ping_status,
                    post_type, post_date, post_date_gmt, post_modified, post_modified_gmt, post_author
                ) VALUES (
                    'ddwpc-fixture-${i}', 'body ${i}', 'publish', 'open', 'open',
                    'post', NOW(), UTC_TIMESTAMP(), NOW(), UTC_TIMESTAMP(), 1
                )
            `);
        }
        cy.task('mysql:query', `UPDATE wp_options SET option_value='0' WHERE option_name='ddwpc_disable_comments'`);
    });

    after(() => {
        // Cleanup: remove fixture posts and reset toggle to a sane default.
        cy.task('mysql:query', `DELETE FROM wp_posts WHERE post_title LIKE 'ddwpc-fixture-%'`);
        cy.task('mysql:query', `UPDATE wp_options SET option_value='0' WHERE option_name='ddwpc_disable_comments'`);
    });

    beforeEach(() => {
        cy.visit('/wp-login.php');
        cy.get('#user_login').type(ADMIN);
        cy.get('#user_pass').type(ADMIN);
        cy.get('#wp-submit').click();
        cy.url().should('include', '/wp-admin', { timeout: 10000 });
    });

    it('frontend stays HTTP 200 after enabling site-wide disable', () => {
        // Toggle ON via the settings page.
        cy.visit('/wp-admin/tools.php?page=delete-disable-comments');
        cy.intercept('POST', '**/admin-ajax.php').as('toggle');
        cy.get('#toggle-comments').then($t => {
            if (!$t.is(':checked')) {
                cy.wrap($t).click({ force: true });
                cy.wait('@toggle').its('response.body.success').should('eq', true);
            }
        });

        // Frontend must respond 200, not 500. Repeat a few times to make sure
        // the regression (which fired on every uncached hit) cannot sneak back in.
        for (let i = 0; i < 5; i++) {
            cy.request({ url: '/?ddwpc_test=' + i, failOnStatusCode: false })
                .its('status').should('eq', 200);
        }
    });

    it('all fixture posts get closed in the DB after toggling on', () => {
        cy.visit('/wp-admin/tools.php?page=delete-disable-comments');
        cy.get('#toggle-comments').then($t => {
            if (!$t.is(':checked')) {
                cy.intercept('POST', '**/admin-ajax.php').as('toggle');
                cy.wrap($t).click({ force: true });
                cy.wait('@toggle').its('response.body.success').should('eq', true);
            }
        });

        cy.task('mysql:query', `
            SELECT COUNT(*) AS open_count
            FROM wp_posts
            WHERE post_title LIKE 'ddwpc-fixture-%'
              AND (comment_status <> 'closed' OR ping_status <> 'closed')
        `).then(rows => {
            expect(rows[0].open_count).to.eq(0);
        });
    });

    it('shows a "Close all comments now" button when posts are still open and closes them', () => {
        // Bring the toggle ON…
        cy.visit('/wp-admin/tools.php?page=delete-disable-comments');
        cy.get('#toggle-comments').then($t => {
            if (!$t.is(':checked')) {
                cy.intercept('POST', '**/admin-ajax.php').as('toggle');
                cy.wrap($t).click({ force: true });
                cy.wait('@toggle').its('response.body.success').should('eq', true);
            }
        });

        // …then re-open a couple of posts directly in the DB to simulate the
        // "imported posts after toggle" scenario.
        cy.task('mysql:query', `
            UPDATE wp_posts
            SET comment_status='open', ping_status='open'
            WHERE post_title IN ('ddwpc-fixture-0', 'ddwpc-fixture-1', 'ddwpc-fixture-2')
        `);

        cy.reload();

        cy.get('[data-cy="open-posts-notice"]').should('be.visible');
        cy.get('[data-cy="open-posts-count"]').should('contain.text', '3');

        cy.intercept('POST', '**/admin-ajax.php').as('closeAll');
        cy.get('[data-cy="close-all-now-btn"]').click();
        cy.wait('@closeAll').its('response.body.success').should('eq', true);
        cy.get('@closeAll').its('response.body.data.closed').should('eq', 3);
        cy.get('@closeAll').its('response.body.data.remaining').should('eq', 0);
    });

    it('toggling OFF does not write any post rows', () => {
        // Capture the modification timestamps before the off-toggle.
        cy.task('mysql:query', `
            SELECT MAX(post_modified) AS max_modified
            FROM wp_posts
            WHERE post_title LIKE 'ddwpc-fixture-%'
        `).then(beforeRows => {
            const before = beforeRows[0].max_modified;

            cy.visit('/wp-admin/tools.php?page=delete-disable-comments');
            cy.get('#toggle-comments').then($t => {
                if ($t.is(':checked')) {
                    cy.intercept('POST', '**/admin-ajax.php').as('toggleOff');
                    cy.wrap($t).click({ force: true });
                    cy.wait('@toggleOff').its('response.body.success').should('eq', true);
                }
            });

            // Wait briefly so any async hooks would have time to fire.
            cy.wait(500);

            cy.task('mysql:query', `
                SELECT MAX(post_modified) AS max_modified
                FROM wp_posts
                WHERE post_title LIKE 'ddwpc-fixture-%'
            `).then(afterRows => {
                expect(String(afterRows[0].max_modified)).to.eq(String(before));
            });
        });
    });
});
