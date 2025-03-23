# Delete & Disable Comments

A WordPress plugin for efficient comment management. This repository contains both the plugin source code and development environment.

## 🌟 Features

See the [Plugin README](wp-content/plugins/delete-disable-comments/README.md) for detailed feature information.

## 🛠 Development Setup

### Prerequisites

- Node.js 16 or higher
- npm 8 or higher
- Docker and Docker Compose
- PHP 7.4 or higher (for local development)
- Composer (for PHP dependencies)

### Getting Started

```bash
# Clone repository
git clone https://github.com/ostheimer/delete-disable-wp-comments.git

# Change to project directory
cd delete-disable-wp-comments

# Install dependencies
npm install

# Start development environment
docker-compose up -d
```

### Development Environment

The development environment includes:
- WordPress installation with the plugin
- MySQL database
- PHPMyAdmin for database management
- Cypress for end-to-end testing

### Available Commands

```bash
# Run tests
npm test

# Open Cypress Test Runner
npm run cypress

# Create plugin ZIP
npm run build
```

## 🧪 Testing

### End-to-End Tests
The project uses Cypress for end-to-end testing. Tests are located in the `cypress/e2e` directory.

To run the tests:
1. Ensure the development environment is running
2. Execute `npm test` for headless testing
3. Or run `npm run cypress` for interactive testing

### Test User
Default test user credentials:
- Username: `comment`
- Password: `comment`

## 📦 Building

To create a distribution ZIP file:

```bash
npm run build
```

This creates `delete-disable-comments.zip` in the `wp-content/plugins` directory.

## 🌍 Internationalization

The plugin supports multiple languages. Translation files are located in:
`wp-content/plugins/delete-disable-comments/languages/`

Currently supported:
- German (Standard, Formal, Austria, Switzerland)
- English (US, UK)

## 👥 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

This project is licensed under the GPLv2 or later - see the [LICENSE](LICENSE) file for details.

## 🤝 Support

For plugin usage and documentation, see the [Plugin README](wp-content/plugins/delete-disable-comments/README.md).

For development questions or issues, please create a [GitHub Issue](https://github.com/ostheimer/delete-disable-wp-comments/issues). 