<div align="center">

# AFN PHP Framework

### Web Application Framework

*Rapid development of secure, scalable PHP apps with a flexible, extensible core.*

[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.0%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-v5.6%2B-blue.svg)](https://www.mysql.com/)
[![CSS](https://img.shields.io/badge/CSS-4.9%25-orange.svg)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![Repo Size](https://img.shields.io/github/repo-size/afnx/afn-php-framework)](https://github.com/afnx/afn-php-framework)

[✨ Features](#features) • [🚀 Installation](#installation) • [📖 Configuration](#configuration) • [🤝 Contributing](#contributing)

</div>

---

AFN PHP Framework is a customizable and lightweight PHP+MySQL web framework designed to streamline the development of modern web applications. With its modular architecture, intuitive routing, and built-in support for MySQL, it empowers developers to build scalable, secure, and maintainable solutions quickly. Whether you're creating a simple website or a complex system, AFN PHP Framework offers flexibility and extensibility for projects of any size.

## Features

- **MVC Architecture**: Clean separation of concerns using the Model-View-Controller pattern.
- **Customizable**: Easily extend and modify core components to suit your application's needs.
- **Database Integration**: Built-in support for MySQL with secure, simple database interactions.
- **Routing System**: Flexible routing for clean, SEO-friendly URLs.
- **Template Engine**: Lightweight templating for rapid UI development.
- **Form Validation**: Simplified form processing and validation.
- **Session & Authentication**: Basic user authentication and session management.
- **Asset Management**: Organize CSS, JS, and other static assets efficiently.
- **Error Handling & Logging**: Developer-friendly error reporting and logging.

## Getting Started

### Prerequisites

- [PHP 7.0+](https://www.php.net/)
- [MySQL 5.6+](https://www.mysql.com/)
- [Composer](https://getcomposer.org/) (optional, for dependency management)
- Web server ([Apache](https://httpd.apache.org/), [Nginx](https://nginx.org/))

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/afnx/afn-php-framework.git
   cd afn-php-framework
   ```

2. **Configure your site**
   - Open the configuration files under `config` and update your database and app settings.
   - Set up your virtual host to point to the `www/` directory.

3. **Install dependencies**
   - If using Composer, run:
     ```bash
     composer install
     ```

4. **Run database migrations (if available)**
   ```bash
   php migrate.php
   ```

## Configuration

### Database

- Configure your MySQL credentials in the `config/database.php` file.
- Database migration scripts are provided for initial setup.

### Routing

- Routes are defined automaticaly when you create a Controller with routing.
- Controllers are located in `app/controllers/`.

### Templating

- Views are located in `resources/views`.
- Use simple placeholders and helpers for dynamic HTML.

## Folder Structure

```
afn-php-framework/
├── app/           # Application logic (core files, controllers, models)
├── config/        # Configuration files
├── www/           # Public web root (index.php, assets)
├── resourcees/    # Views components
├── library/       # Composer dependencies
└── README.md
```

## Contributing

We welcome contributions from developers of all skill levels! 

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## License

This project is licensed under the [MIT License](LICENSE).

## Authors

- **Ali Fuat Numanoglu** - *Initial work* - [@afnx](https://github.com/afnx)

## Support

- 🐛 [Report Issues](https://github.com/afnx/afn-php-framework/issues)
- 📧 [Contact Me](https://alifuatnumanoglu.com/contact)
- ⭐ **If this project helped you, please give it a star!**

---

<sub>Made with ❤️ by Ali Fuat Numanoglu</sub>
