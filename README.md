# MCQHome WordPress Theme

A comprehensive WordPress theme for MCQ-based educational platform with multi-user roles (Admin, Institution, Teacher, Student).

## Features

- Multi-user role system (Admin, Institution, Teacher, Student)
- Custom post types for MCQs, MCQ Sets, and Institutions
- Responsive design with Tailwind CSS
- Role-specific dashboards
- MCQ creation and management system
- Assessment delivery system
- Progress tracking and analytics
- Social features (follow system)
- SEO optimized
- WordPress Customizer integration

## Installation

### Quick Install (Recommended)

1. **Download the latest release** from [GitHub Releases](https://github.com/bsobro/mcqhome-wordpress-theme/releases)
2. **Extract and upload** to `/wp-content/themes/mcqhome-theme/`
3. **Activate** the theme in WordPress Admin → Appearance → Themes

### Developer Install

```bash
# Clone the repository
cd /path/to/wordpress/wp-content/themes/
git clone https://github.com/bsobro/mcqhome-wordpress-theme.git mcqhome-theme
cd mcqhome-theme

# Install dependencies and build
npm install
npm run build
```

📖 **Detailed instructions**: See [INSTALLATION.md](INSTALLATION.md)

## Development

### Prerequisites

- Node.js and npm
- WordPress development environment

### Build Process

The theme uses Tailwind CSS for styling and webpack for JavaScript bundling.

**Development mode (with file watching):**

```bash
npm run dev
```

**Production build:**

```bash
npm run build
```

**JavaScript development:**

```bash
npm run dev-js
```

**JavaScript production build:**

```bash
npm run build-js
```

### File Structure

```
mcqhome-theme/
├── assets/
│   ├── css/
│   │   └── main.css (compiled)
│   └── js/
│       ├── main.js
│       └── customizer.js
├── inc/
│   ├── customizer.php
│   └── template-functions.php
├── src/
│   ├── css/
│   │   └── main.css (source)
│   └── js/
│       └── main.js (source)
├── functions.php
├── header.php
├── footer.php
├── index.php
├── sidebar.php
├── style.css
├── tailwind.config.js
├── webpack.config.js
└── package.json
```

## Theme Activation

Upon activation, the theme will:

- Create default pages (Dashboard, Browse MCQs, Institutions, Teachers)
- Set up default theme options
- Schedule necessary cron jobs
- Flush rewrite rules

## Customization

The theme includes WordPress Customizer support for:

- Colors (Primary, Secondary)
- Typography (Body Font, Heading Font)
- Layout (Container Width, Header Layout)

## Requirements

- WordPress 6.0+
- PHP 8.0+
- Node.js (for development)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

GPL v2 or later

## Links

- 🏠 **Demo Site**: [Coming Soon]
- 📖 **Documentation**: [GitHub Wiki](https://github.com/bsobro/mcqhome-wordpress-theme/wiki)
- 🐛 **Issues**: [GitHub Issues](https://github.com/bsobro/mcqhome-wordpress-theme/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/bsobro/mcqhome-wordpress-theme/discussions)

## Support

- 📧 **Email**: support@mcqhome.com
- 🆘 **Issues**: [Report bugs or request features](https://github.com/bsobro/mcqhome-wordpress-theme/issues)
- 📚 **Documentation**: [Installation Guide](INSTALLATION.md) | [Contributing Guide](CONTRIBUTING.md)

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.
