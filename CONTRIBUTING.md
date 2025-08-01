# Contributing to MCQHome WordPress Theme

Thank you for your interest in contributing to the MCQHome WordPress Theme! This document provides guidelines and information for contributors.

## Development Setup

### Prerequisites

- Node.js (v16 or higher)
- npm or yarn
- WordPress development environment
- Git

### Getting Started

1. **Clone the repository**

   ```bash
   git clone https://github.com/bsobro/mcqhome-wordpress-theme.git
   cd mcqhome-wordpress-theme
   ```

2. **Install dependencies**

   ```bash
   npm install
   ```

3. **Build assets**

   ```bash
   # Development build with file watching
   npm run dev

   # Production build
   npm run build
   ```

4. **Install in WordPress**
   - Copy the theme folder to `/wp-content/themes/`
   - Activate the theme in WordPress admin

## Development Workflow

### CSS Development

- Source files are in `src/css/main.css`
- Uses Tailwind CSS for styling
- Run `npm run dev` for development with file watching
- Run `npm run build` for production build

### JavaScript Development

- Source files are in `src/js/`
- Uses webpack for bundling
- Alpine.js is included for reactive components
- Run `npm run dev-js` for development
- Run `npm run build-js` for production

### File Structure

```
mcqhome-theme/
├── assets/           # Compiled assets
├── inc/             # PHP includes
├── src/             # Source files
├── functions.php    # Main theme functions
├── style.css        # Theme header
└── *.php           # Template files
```

## Coding Standards

### PHP

- Follow WordPress Coding Standards
- Use proper sanitization and escaping
- Add proper documentation blocks
- Use meaningful variable and function names

### CSS

- Use Tailwind CSS utility classes
- Follow BEM methodology for custom components
- Ensure responsive design
- Test across different browsers

### JavaScript

- Use ES6+ features
- Follow consistent naming conventions
- Add comments for complex logic
- Ensure accessibility compliance

## Commit Guidelines

### Commit Message Format

```
type(scope): description

[optional body]

[optional footer]
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

### Examples

```
feat(dashboard): add user role-specific dashboard widgets
fix(mobile): resolve mobile menu toggle issue
docs(readme): update installation instructions
style(css): improve button hover states
```

## Pull Request Process

1. **Create a feature branch**

   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**

   - Follow coding standards
   - Test your changes
   - Update documentation if needed

3. **Commit your changes**

   ```bash
   git add .
   git commit -m "feat(scope): your descriptive message"
   ```

4. **Push to your fork**

   ```bash
   git push origin feature/your-feature-name
   ```

5. **Create a Pull Request**
   - Provide a clear description
   - Reference any related issues
   - Include screenshots if applicable

## Testing

### Manual Testing

- Test on different screen sizes
- Verify functionality across browsers
- Check accessibility with screen readers
- Test with different WordPress versions

### Code Quality

- Run `npm run build` to ensure no build errors
- Validate HTML markup
- Check for PHP errors and warnings

## Issue Reporting

When reporting issues, please include:

- WordPress version
- PHP version
- Browser and version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable

## Feature Requests

For feature requests:

- Check existing issues first
- Provide detailed use case
- Explain the benefit to users
- Consider implementation complexity

## Code of Conduct

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Maintain professional communication

## Questions?

If you have questions about contributing:

- Check existing documentation
- Search closed issues
- Create a new issue with the "question" label
- Contact the maintainers

Thank you for contributing to MCQHome WordPress Theme!
