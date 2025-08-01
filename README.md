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

1. Upload the theme files to your WordPress themes directory
2. Activate the theme in WordPress admin
3. Install dependencies and build assets:

```bash
npm install
npm run build
```

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

## Support

For support and documentation, visit the theme documentation or contact the development team.
