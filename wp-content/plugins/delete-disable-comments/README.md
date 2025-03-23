# Delete & Disable Comments

A WordPress plugin to manage and disable comments. Delete spam comments, backup and remove all comments, or disable comments site-wide.

=== Delete & Disable Comments ===
Contributors: ostheimer
Tags: comments, spam, delete, disable, backup
Requires at least: 5.0
Tested up to: 6.4.3
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

## Description

This plugin provides a simple and efficient way to manage comments on your WordPress site. It offers three main features:

1. **Delete Spam Comments**: Remove all comments marked as spam from your database.
2. **Delete All Comments**: Remove all comments from your website with the option to create a backup first.
3. **Disable Comments**: Toggle comments on or off for your entire website.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/delete-disable-comments` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Tools->Delete & Disable Comments screen to manage your comments.

## Usage

After installation, you can find the plugin under the Tools menu in your WordPress admin area.

### Delete Spam Comments
- Click the "Delete Spam Comments" button to remove all spam comments from your database.
- A confirmation message will be shown after successful deletion.

### Delete All Comments
- Before deleting all comments, you can download a backup by clicking the "Download Backup" button.
- Click "Delete All Comments" to remove all comments from your website.
- A confirmation message will be shown after successful deletion.

### Disable Comments
- Use the toggle switch to enable or disable comments site-wide.
- When disabled, all existing comments will be hidden and new comments will be prevented.
- The status will be preserved even after theme changes.

## Security

- All actions require admin privileges
- Nonce verification for all operations
- Data sanitization and validation
- Secure file operations
- XSS prevention through escaping

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Support

For support, please create an issue in the [GitHub repository](https://github.com/ostheimer/delete-disable-wp-comments).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

## Changelog

### 1.0.0
* Initial release
* Added spam comment deletion
* Added all comments deletion with backup
* Added site-wide comment toggle
* Added German translations

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

## 🙏 Acknowledgments

Special thanks to all contributors and the WordPress community for their support and feedback. 