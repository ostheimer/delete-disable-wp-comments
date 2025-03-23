# Delete & Disable Comments

A powerful WordPress plugin for managing and disabling comments.

## 🌟 Features

- **Delete Spam Comments**: Remove all comments marked as spam with a single click
- **Delete All Comments**: Delete all comments with backup option
- **Disable Comments**: Toggle the comment functionality for the entire website
- **Multilingual**: Available in:
  - German (Formal) - de_DE
  - German (Austria) - de_AT
  - German (Switzerland) - de_CH
  - German (Standard) - de
  - English (USA) - en_US
  - English (GB) - en_GB
  - English (Standard) - en

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## 💻 Installation

1. Download the plugin ZIP file
2. Go to your WordPress dashboard "Plugins" → "Add New"
3. Click "Upload Plugin"
4. Select the downloaded ZIP file
5. Click "Install Now"
6. After installation, click "Activate"

## 🔧 Usage

### Delete Spam Comments
1. Navigate to "Comments" → "Delete & Disable Comments"
2. Click "Delete Spam Comments"
3. Confirm the action in the dialog

### Delete All Comments
1. Navigate to "Comments" → "Delete & Disable Comments"
2. Optional: Click "Download Backup" to create a backup
3. Click "Delete All Comments"
4. Confirm the action in the dialog

### Disable Comments
1. Navigate to "Comments" → "Delete & Disable Comments"
2. Use the toggle switch to enable/disable comments
3. The change takes effect immediately

## 🔒 Security

- Only administrators have access to plugin functions
- All actions require confirmation
- CSRF protection through WordPress nonces
- Backup option before deleting all comments

## 🌐 Advanced Comment Disabling

When comments are disabled:
- Comment REST API endpoints are disabled
- Comment links are removed from post meta
- Comment widgets are disabled
- Comment support is removed for all post types
- Comment section is hidden in the frontend
- Theme-specific comment styles are removed
- Comment-related Gutenberg blocks are removed

## 🛠 Troubleshooting

### Comments Still Appear After Disabling
1. Check if your theme has hardcoded comments
2. Clear your browser and server cache
3. Temporarily disable other plugins that might affect comments

### Translations Not Showing
1. Ensure the correct language is set in WordPress
2. Verify that language files are loading correctly
3. Clear the WordPress cache

## 📝 Changelog

### Version 1.0.0 (2024-03-23)
- Initial release
- Implemented core functionality
- Added multilingual support

## 🤝 Contributing

Found a bug or have suggestions for improvements? Feel free to create an issue or pull request on GitHub.

## 📄 License

This plugin is licensed under GPL v2 or later. See [LICENSE](LICENSE) for details.

## 👥 Authors

- Andreas Ostheimer
- [GitHub Repository](https://github.com/ostheimer/delete-disable-wp-comments)

## 🙏 Acknowledgments

Special thanks to all contributors and the WordPress community for their support and feedback. 