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