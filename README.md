# Delete & Disable Comments

A WordPress plugin that helps site administrators manage comments by deleting spam comments, removing all comments with backup, or disabling comments site-wide.

## Description

This plugin provides a simple way to:
- Delete all spam comments
- Create a backup of existing comments
- Delete all comments
- Disable comments site-wide

## Installation

1. Upload the plugin files to `/wp-content/plugins/delete-disable-comments`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Tools->Delete & Disable Comments screen to manage comments

## Frequently Asked Questions

### Is this action reversible?

Once comments are deleted, they cannot be recovered unless you have created a backup first.

## Screenshots

1. The main plugin interface under Tools->Delete & Disable Comments

## Changelog

### 1.0.0
* Initial release

## Requirements

* WordPress 5.0 or higher
* PHP 7.2 or higher

## License

This plugin is licensed under the GPL v2 or later.

* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.

## Tested up to

* WordPress 6.4.3

## Stable tag

* 1.0.0

## 🌟 Features

See the [Plugin README](wp-content/plugins/delete-disable-comments/README.md) for detailed feature information.

## 📋 Technical Requirements

### WordPress Environment
- WordPress: 5.0 or higher
- PHP: 7.4 or higher
- MySQL: 5.6 or higher
- Apache/Nginx with mod_rewrite

### Development Environment
- Node.js 16 or higher
- npm 8 or higher
- Docker and Docker Compose
- Composer (for PHP dependencies)
- Git 2.25 or higher

### Browser Support
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

## 📁 Project Structure

```
.
├── wp-content/plugins/delete-disable-comments/  # Plugin source code
│   ├── admin/                # Admin interface files
│   │   └── admin-page.php   # Main admin interface implementation
│   ├── css/                 # Stylesheet files
│   │   └── admin-style.css  # Admin interface styling
│   ├── includes/            # Core functionality files
│   │   ├── functions.php    # Core plugin functions
│   │   └── check-languages.php  # Language support functions
│   ├── js/                  # JavaScript files
│   │   └── admin-script.js  # Admin interface interactions
│   ├── languages/           # Translation files
│   │   ├── *.po            # Translation source files
│   │   ├── *.mo            # Compiled translation files
│   │   └── *.pot           # Translation template
│   ├── templates/           # Template files
│   │   └── blank.php       # Empty comments template
│   ├── README.md           # Plugin documentation
│   └── delete-disable-comments.php  # Main plugin file
│
├── cypress/                 # End-to-end tests
│   └── e2e/                # Test specifications
│       └── manage-comments.cy.js  # Comment management tests
│
├── documentation/          # Additional documentation
│
├── .cursor/                # IDE settings (ignored)
├── node_modules/           # npm dependencies (ignored)
│
├── .gitignore             # Git ignore rules
├── README.md              # Repository documentation
├── cypress.config.js      # Cypress configuration
├── docker-compose.yml     # Docker environment setup
├── package.json           # Project dependencies and scripts
└── package-lock.json      # Locked dependencies versions
```

### Key Files and Directories

- **Plugin Files** (`wp-content/plugins/delete-disable-comments/`):
  - `delete-disable-comments.php`: Main plugin file with core setup, hooks, and filters
  - `admin/`: WordPress admin interface implementation
    - `admin-page.php`: Implements the plugin's admin panel with all controls
  - `includes/`: Core plugin functionality and helpers
    - `functions.php`: Contains all core functions for comment management
    - `check-languages.php`: Handles language file loading and checks
  - `languages/`: Translation files for multiple languages
    - `.po` files: Source translation files (editable)
    - `.mo` files: Compiled translation files (binary)
    - `.pot` file: Translation template
  - `templates/`: Template files for frontend rendering
    - `blank.php`: Empty template for disabled comments
  - `css/` & `js/`: Asset files for styling and interactions
    - `admin-style.css`: Styles for the admin interface
    - `admin-script.js`: JavaScript for admin panel interactions

- **Development Files**:
  - `cypress/`: End-to-end test files and configurations
    - `manage-comments.cy.js`: Tests for all comment management features
  - `documentation/`: Additional development documentation
  - `docker-compose.yml`: Docker environment configuration with WordPress, MySQL, and PHPMyAdmin
  - `package.json`: npm dependencies and development scripts

- **Configuration Files**:
  - `.gitignore`: Specifies which files Git should ignore
  - `cypress.config.js`: Cypress test runner configuration with WordPress-specific settings

## 🔄 Development Workflow

### Branch Strategy
```
main              # Production-ready code
├── develop       # Development branch
│   ├── feature/* # New features
│   ├── bugfix/*  # Bug fixes
│   └── test/*    # Test implementations
└── release/*     # Release preparation
```

# [WordPress Plugin Directory] Review in Progress: Delete & Disable Comments
👋 helpstring - welcome to your plugin review!


Thanks for uploading your plugin "Delete & Disable Comments", we can begin with the review.

We found issues with your plugin code and/or functionality preventing it from being approved immediately. We have pended your submission in order to help you correct all issues so that it may be approved and published.

Who are we?


We are a group of volunteers who help you identify common issues so that you can make your plugin more secure, compatible, reliable and compliant with the guidelines.

We are devoting our time to reviewing your plugin, we ask that you honor this by reading this email in its entirety, addressing any issues listed, testing your changes, and uploading a corrected version of your code if all is well.

The review process

Your plugin is checked by a volunteer who will send you the issues found in this email. This is the current step.
You will read this email in its entirety, checking each issue as well as the links to the documentation and examples provided. In case of any doubt, you will reply to this email asking for clarification.
Then you will thoroughly fix any issues, test your plugin, upload a corrected version of your plugin and reply to this email.
As soon as the volunteer is able, they/she/he will check your corrected plugin again. Please, be patient waiting for a reply.
If there are no further issues, the plugin will be approved 🎉
If there are still issues, the process will go back to step 1 until all the issues have been addressed 🫷

⚠️ When you reply we will be reviewing your entire plugin again, so please do not reply until you are sure you have addressed all of the issues listed, otherwise your submission will be delayed and eventually rejected.

Understanding the Review Queue

When you reply to this email, your plugin will re-enter a queue to be reviewed by a volunteer. Here's how the system works:
Priority when there is significant progress: Plugins submitted with meaningful improvements will be reviewed faster.
Fewer review cycles means faster review: If you've only needed 1 or 2 reviews, your plugin will likely be approved in a matter of days. However, if multiple reviews are needed, the wait time between reviews increases significantly and could take weeks or even months.

⭐ How to Speed Up Approval
To get your plugin approved faster, focus on thoroughly fixing all identified issues before resubmitting.
You will get faster approvals if you take the time to review and test your plugin instead of rushing to upload an update.

This approach encourages authors to invest time in improving their plugins.

Other details


Remember that in addition to code quality, security and functionality, we require all plugins to adhere to the guidelines that you accepted when submitting this plugin. Please keep them in mind when making changes to your plugin: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/.

Finally, should you at any time wish to alter your permalink (aka the plugin slug) "delete-disable-comments", you must explicitly tell us what you would like it to be. Just changing the display name is not sufficient, and we require you to clearly state your desired permalink. Remember, permalinks cannot be altered after approval.

List of issues found



## You haven't added yourself to the "Contributors" list for this plugin.

In your readme file, the "Contributors" parameter is a case-sensitive, comma-separated list of all WordPress.org usernames that have contributed to the code.

Your username is not in this list, you need to add yourself if you want to appear listed as a contributor to this plugin.

If you don't want to appear that's fine, this is not mandatory, we're warning you just in case.

Analysis result:

# WARNING: None of the listed contributors "ostheimer" is the WordPress.org username of the owner of the plugin "helpstring".

## Calling core loading files directly

Calling core files like wp-config.php, wp-blog-header.php, wp-load.php directly via an include is not permitted.

These calls are prone to failure as not all WordPress installs have the exact same file structure. In addition it opens your plugin to security issues, as WordPress can be easily tricked into running code in an unauthenticated manner.

Your code should always exist in functions and be called by action hooks. This is true even if you need code to exist outside of WordPress. Code should only be accessible to people who are logged in and authorized, if it needs that kind of access. Your plugin's pages should be called via the dashboard like all the other settings panels, and in that way, they'll always have access to WordPress functions.

https://developer.wordpress.org/plugins/hooks/

There are some exceptions to the rule in certain situations and for certain core files. In that case, we expect you to use require_once to load them and to use a function from that file immediately after loading it.

If you are trying to "expose" an endpoint to be accessed directly by an external service, you have some options.
You can expose a 'page' use query_vars and/or rewrite rules to create a virtual page which calls a function. A practical example.
You can create an AJAX endpoint.
You can create a REST API endpoint.

Example(s) from your plugin:
includes/functions.php:12 require_once(ABSPATH . 'wp-load.php');
delete-disable-comments.php:32 require_once(ABSPATH . 'wp-includes/locale.php');
includes/functions.php:13 require_once(ABSPATH . 'wp-admin/includes/admin.php');
delete-disable-comments.php:31 require_once(ABSPATH . 'wp-includes/l10n.php');


## Determine files and directories locations correctly

WordPress provides several functions for easily determining where a given file or directory lives.

We detected that the way your plugin references some files, directories and/or URLs may not work with all WordPress setups. This happens because there are hardcoded references or you are using the WordPress internal constants.

Let's improve it, please check out the following documentation:

https://developer.wordpress.org/plugins/plugin-basics/determining-plugin-and-content-directories/

It contains all the functions available to determine locations correctly.

Most common cases in plugins can be solved using the following functions:
For where your plugin is located: plugin_dir_path() , plugin_dir_url() , plugins_url()
For the uploads directory: wp_upload_dir() (Note: If you need to write files, please do so in a folder in the uploads directory, not in your plugin directories).

Example(s) from your plugin:
delete-disable-comments.php:25 require_once(dirname(dirname(dirname(__DIR__))) . '/wp-load.php');
includes/functions.php:8 define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
delete-disable-comments.php:54 $wp_mofile = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo';


ℹ️ In order to determine your plugin location, you would need to use the __FILE__ variable for this to work properly.
Note that this variable depends on the location of the file making the call. As this can create confusion, a common practice is to save its value in a define() in the main file of your plugin so that you don't have to worry about this.

Example: Your main plugin file.
define( 'MYPREFIX_PLUGIN_FILE', __FILE__ );
define( 'MYPREFIX_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MYPREFIX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

Example: Any file of your plugin.
require_once MYPREFIX_PLUGIN_DIR . 'admin/class-init.php';


function myprefix_scripts() {
 wp_enqueue_script( 'myprefix-script', MYPREFIX_PLUGIN_URL . 'js/script.js', array(), MYPREFIX_VERSION );
 // Or alternatively
 wp_enqueue_script( 'myprefix-script', plugins_url( 'js/script.js', MYPREFIX_PLUGIN_FILE ), array(), MYPREFIX_VERSION );
}
add_action( 'wp_enqueue_scripts', 'myprefix_scripts' );


Example(s) from your plugin:
delete-disable-comments.php:54 $wp_mofile = WP_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo';


## Saving data in the plugin folder and/or asking users to edit/write to plugin.

We cannot accept a plugin that forces (or tells) users to edit the plugin files in order to function, or saves data in the plugin folder.

Plugin folders are deleted when upgraded, so using them to store any data is problematic. Also bear in mind, any data saved in a plugin folder is accessible by the public. This means anyone can read it and use it without the site-owner's permission.

It is preferable that you save your information to the database, via the Settings API, especially if it's privileged data.

If that's not possible, because you're uploading media files, you should use the media uploader.

If you can't do either of those, you must save the data outside the plugins folder. We recommend using the uploads directory, creating a folder there with the slug of your plugin as name, as that will make your plugin compatible with multisite and other one-off configurations.

Please refer to the following links:

https://developer.wordpress.org/plugins/settings/
https://developer.wordpress.org/reference/functions/media_handle_upload/
https://developer.wordpress.org/reference/functions/wp_handle_upload/
https://developer.wordpress.org/reference/functions/wp_upload_dir/

Example(s) from your plugin:
includes/check-languages.php:60 $wp_filesystem->put_contents($po_file, $po_content);
# ↳ Detected: plugin_dir_path
includes/check-languages.php:63 $wp_filesystem->put_contents($mo_file, '');
# ↳ Detected: plugin_dir_path
includes/check-languages.php:24 $wp_filesystem->mkdir($lang_dir, FS_CHMOD_DIR);
# ↳ Detected: plugin_dir_path


## Generic function/class/define/namespace/option names

All plugins must have unique function names, namespaces, defines, class and option names. This prevents your plugin from conflicting with other plugins or themes. We need you to update your plugin to use more unique and distinct names.

A good way to do this is with a prefix. For example, if your plugin is called "Delete & Disable Comments" then you could use names like these:
function deleamdi_save_post(){ ... }
class DELEAMDI_Admin { ... }
update_option( 'deleamdi_settings', $settings );
define( 'DELEAMDI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
global $deleamdi_options;
namespace helpstring\deletedisablecomments;

Disclaimer: These are just examples that may have been self-generated from your plugin name, we trust you can find better options. If you have a good alternative, please use it instead, this is just an example.

Don't try to use two (2) or three (3) letter prefixes anymore. We host nearly 100-thousand plugins on WordPress.org alone. There are tens of thousands more outside our servers. Believe us, you're going to run into conflicts.

You also need to avoid the use of __ (double underscores), wp_ , or _ (single underscore) as a prefix. Those are reserved for WordPress itself. You can use them inside your classes, but not as stand-alone function.

Please remember, if you're using _n() or __() for translation, that's fine. We're only talking about functions you've created for your plugin, not the core functions from WordPress. In fact, those core features are why you need to not use those prefixes in your own plugin! You don't want to break WordPress for your users.

Related to this, using if (!function_exists('NAME')) { around all your functions and classes sounds like a great idea until you realize the fatal flaw. If something else has a function with the same name and their code loads first, your plugin will break. Using if-exists should be reserved for shared libraries only.

Remember: Good prefix names are unique and distinct to your plugin. This will help you and the next person in debugging, as well as prevent conflicts.

Analysis result:
# This plugin is using the prefix "delete" for 20 element(s).

# Cannot use "disable" as a prefix.
includes/functions.php:290 update_option('disable_comments', $disabled ? '1' : '0');
# Cannot use "delete" as a prefix.
includes/functions.php:24 function delete_spam_comments
includes/functions.php:86 function delete_all_comments
includes/functions.php:357 function delete_disable_comments_get_status
admin/admin-page.php:7 function delete_disable_comments_admin_page
delete-disable-comments.php:40 function delete_disable_comments_load_textdomain
delete-disable-comments.php:77 function delete_disable_comments_admin_enqueue_scripts
delete-disable-comments.php:131 function delete_disable_comments_admin_menu
delete-disable-comments.php:147 function delete_disable_comments_admin_page
delete-disable-comments.php:196 function delete_disable_comments_activate
delete-disable-comments.php:203 function delete_disable_comments_deactivate
delete-disable-comments.php:210 function delete_disable_comments_init
delete-disable-comments.php:367 function delete_disable_comments_override_template
delete-disable-comments.php:376 function delete_disable_comments_disable_feeds
delete-disable-comments.php:385 function delete_disable_comments_admin_bar_render
delete-disable-comments.php:394 function delete_disable_comments_dequeue_scripts
delete-disable-comments.php:402 function delete_disable_comments_hide_existing_comments
delete-disable-comments.php:411 function delete_disable_comments_disable_rest_endpoints
# Cannot use "check" as a prefix.
includes/check-languages.php:7 function check_and_create_language_files
# Cannot use "create" as a prefix.
includes/check-languages.php:32 function create_mo_file

# Looks like there are elements not using common prefixes.
includes/functions.php:143 function backup_comments
includes/functions.php:254 function toggle_comments
delete-disable-comments.php:198 add_option('disable_comments', false);


Note: Options and Transients must be prefixed.

This is really important because the options are stored in a shared location and under the name you have set. If two plugins use the same name for options, they will find an interesting conflict when trying to read information introduced by the other plugin.

Also, once your plugin has active users, changing the name of an option is going to be really tricky, so let's make it robust from the very beginning.

Example(s) from your plugin:
delete-disable-comments.php:198 add_option('disable_comments', false);


## Allowing Direct File Access to plugin files

Direct file access occurs when someone directly queries a PHP file. This can be done by entering the complete path to the file in the browser's URL bar or by sending a POST request directly to the file.

For files that only contain class or function definitions, the risk of something funky happening when accessed directly is minimal. However, for files that contain executable code (e.g., function calls, class instance creation, class method calls, or inclusion of other PHP files), the risk of security issues is hard to predict because it depends on the specific case, but it can exist and it can be high.

You can easily prevent this by adding the following code at the top of all PHP files that could potentially execute code if accessed directly:
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

Example(s) from your plugin:
includes/functions.php:7 


👉 Your next steps


Please, before replying make sure to perform the following actions:
Read this email.
Take the time to understand the issues shared, check the included examples, check the documentation, research the issue on internet, and gain a better understanding of what's happening and how you can fix it. We want you to thoroughly understand these issues so that you can take them into account when maintaining your plugin in the future.
Note that there may be false positives - we are humans and make mistakes, we apologize if there is anything we have gotten wrong.
If you have doubts you can ask us for clarification, when asking us please be clear, concise, direct and include an example.
You can make use of tools like PHPCS or Plugin Check to further help you with finding all the issues.
Fix your plugin.
Test your plugin on a clean WordPress installation. You can try Playground.
Go to "Add your plugin" and upload an updated version of this plugin. You can update the code there whenever you need to, along the review process, and we will check the latest version.
Reply to this email telling us that you have updated it, and let us know if there is anything we need to know or have in mind.
Please do not list the changes made as we will review the whole plugin again, just share anything you want to clarify.

ℹ️ To make this process as quick as possible and to avoid burden on the volunteers devoting their time to review this plugin's code, we ask you to thoroughly check all shared issues and fix them before sending the code back to us. I know we already asked you to do so, and it is because we are really trying to make it very clear.

Disclaimers


Please note that due to the significant effort this kind of reviews require, we do a basic review the first time that we review your plugin. Once the issues we shared above are fixed, we will do a more in-depth review which might surface other issues.

While we try to make our reviews as exhaustive as possible we, like you, are humans and may have missed things. We appreciate your patience and understanding.

We recommend that you get ahead of us by checking for some common issues that require a more thorough review such as the use of nonces or determining plugin and content directories correctly.

We encourage all plugin authors to use tools like Plugin Check to ensure that most basic issues are fixed first. If you haven't used it yet, give it a try, it will save us both time and speed up the review process.
Please note: Automated tools can give false positives, or may miss issues. Plugin Check and other tools cannot guarantee that our reviewers won't find an issue that needs fixing or clarification.

We again remind you that should you wish to alter your permalink (not the display name, the plugin slug) "delete-disable-comments", you must explicitly tell us what you would like it to be. Just changing the display name is not sufficient. We require you to clearly state, in the body of your email what your desired permalink is. Permalinks cannot be altered after approval, and we generally do not accept requests to rename them, should you fail to inform us during the review. If you previously asked for a permalink change and got a reply that is has been processed, you're all good! While these emails will still use the original display name, you don't need to panic. If you did not get a reply that we processed the permalink, let us know immediately.

If the corrections we requested in this initial review are not completed within 3 months (90 days), we will reject this submission in order to keep our queue manageable.

If you have questions, concerns, or need clarification, please reply to this email and just ask us.

Review ID: F1 delete-disable-comments/helpstring/1Apr25/T1 1Apr25/3.3


--
WordPress Plugin Review Team | plugins@wordpress.org
https://make.wordpress.org/plugins/
https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
https://wordpress.org/plugins/plugin-check/
{#HS:2894356916-737426#} 

## Plugin Review TODOs

Based on the WordPress Plugin Directory review feedback:

1.  **Contributors-Liste korrigieren:** Füge den korrekten WordPress.org-Benutzernamen (`helpstring`) zur "Contributors"-Liste hinzu (aktuell `ostheimer`). - **Erledigt**
2.  **Direkte Aufrufe von Core-Dateien entfernen:** Entferne `require_once` für Core-Dateien (z.B. `wp-load.php`) in `includes/functions.php` und `delete-disable-comments.php`. - **Erledigt**
3.  **Dateipfade korrekt bestimmen:** Ersetze unsichere Pfadbestimmungen durch WordPress-Funktionen (`plugin_dir_path()`, `plugin_dir_url()`, `wp_upload_dir()`) und definiere Konstanten in der Hauptdatei. - **Erledigt**
4.  **Datenspeicherung im Plugin-Ordner verhindern:** Ändere `includes/check-languages.php`, sodass Sprachdateien nicht in den Plugin-Ordner geschrieben werden. - **Erledigt**
5.  **Eindeutige Namen verwenden (Präfix):** Füge allen Funktionen, Klassen, Konstanten, Hooks und Optionen (z.B. `delete_spam_comments`, `toggle_comments`, `disable_comments`) einen eindeutigen Präfix hinzu (z.B. `ddc_`). - **Erledigt**
6.  **Direkten Dateizugriff verhindern:** Füge `if ( ! defined( 'ABSPATH' ) ) exit;` am Anfang aller relevanten PHP-Dateien hinzu. - **Erledigt**
7.  **Direkte Core-File-Loads entfernen:** Entferne `require_once(dirname(dirname(dirname(__DIR__))) . '/wp-load.php');` und ähnliche direkte Aufrufe in `delete-disable-comments.php` und `includes/functions.php`. - **Erledigt**
8.  **load_plugin_textdomain()-Aufruf entfernen:** Entferne den manuellen Aufruf von `load_plugin_textdomain()` in `delete-disable-comments.php`, da WordPress.org ab Core 4.6 die Übersetzungen automatisch lädt. - **Ausstehend**
9.  **Längerer eindeutiger Präfix:** Ersetze den zu kurzen Präfix `ddc_` durch einen einzigartigen Präfix mit mindestens 5 Zeichen in allen Funktionen, Klassen, Konstanten, Optionen und Hooks. - **Ausstehend**