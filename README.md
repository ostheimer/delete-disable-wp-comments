# Delete & Disable Comments

A WordPress plugin for efficient comment management. This plugin allows administrators to delete spam comments, backup and delete all comments, and globally disable comments.

## 🌟 Features

- **Delete Spam Comments**: Remove all comments marked as spam with a single click
- **Manage All Comments**: 
  - Backup option before deletion
  - Complete deletion of all comments
- **Globally Disable Comments**:
  - Disable comment REST API endpoints
  - Remove comment links from post meta
  - Disable comment widgets
  - Remove comment support for all post types
  - Additional filters to hide comment sections
  - Remove theme-specific comment styles
  - Remove comment-related Gutenberg blocks

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## 💻 Installation

1. Download the plugin ZIP file
2. Go to your WordPress dashboard "Plugins" > "Add New"
3. Click "Upload Plugin"
4. Select the downloaded ZIP file
5. Click "Install Now"
6. Activate the plugin

## 🛠 Development

### Setting up the Development Environment

```bash
# Clone repository
git clone https://github.com/ostheimer/delete-disable-wp-comments.git

# Change to project directory
cd delete-disable-wp-comments

# Install dependencies
npm install

# Start Docker containers
docker-compose up -d
```

### Available Commands

```bash
# Run tests
npm test

# Run Cypress tests
npm run cypress

# Create plugin ZIP
npm run build
```

## 🌍 Internationalization

The plugin is available in:
- German (Standard, Formal, Austria, Switzerland)
- English (US, UK)

## 🔒 Security

- Only administrators have access to plugin functions
- Confirmation dialogs before destructive actions
- Backup option before deleting comments
- Security nonces for all AJAX actions

## 📝 Changelog

### Version 1.0.0
- Initial public release
- Implemented core functionality
- Added multilingual support
- Implemented security features

## 👥 Contributors

- Andreas Ostheimer ([@ostheimer](https://github.com/ostheimer))

## 📄 License

This project is licensed under the GPLv2 or later - see the [LICENSE](LICENSE) file for details.

## 🤝 Support

For questions or issues, please create a [GitHub Issue](https://github.com/ostheimer/delete-disable-wp-comments/issues). 