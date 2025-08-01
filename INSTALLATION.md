# MCQHome WordPress Theme Installation Guide

This guide will help you install and set up the MCQHome WordPress theme.

## Quick Installation

### Method 1: Direct Download (Recommended)

1. **Download the theme**

   - Download the latest release from GitHub
   - Extract the ZIP file

2. **Upload to WordPress**

   ```
   /wp-content/themes/mcqhome-theme/
   ```

3. **Activate the theme**
   - Go to WordPress Admin → Appearance → Themes
   - Find "MCQHome Theme" and click "Activate"

### Method 2: Git Clone (For Developers)

1. **Clone the repository**

   ```bash
   cd /path/to/wordpress/wp-content/themes/
   git clone https://github.com/bsobro/mcqhome-wordpress-theme.git mcqhome-theme
   ```

2. **Install dependencies**

   ```bash
   cd mcqhome-theme
   npm install
   ```

3. **Build assets**

   ```bash
   npm run build
   ```

4. **Activate in WordPress**
   - Go to WordPress Admin → Appearance → Themes
   - Activate "MCQHome Theme"

## Post-Installation Setup

### Automatic Setup

Upon activation, the theme will automatically:

- ✅ Create default pages (Dashboard, Browse MCQs, Institutions, Teachers)
- ✅ Set up widget areas
- ✅ Configure navigation menus
- ✅ Apply default theme options

### Manual Configuration

1. **Set up menus**

   - Go to Appearance → Menus
   - Create a "Primary Menu" and assign to "Primary Menu" location
   - Create a "Footer Menu" and assign to "Footer Menu" location

2. **Configure widgets**

   - Go to Appearance → Widgets
   - Add widgets to "Sidebar" and "Footer Widget Area"

3. **Customize theme**

   - Go to Appearance → Customize
   - Configure colors, fonts, and layout options

4. **Set up homepage**
   - Go to Settings → Reading
   - Choose "A static page" and select your homepage

## Requirements

### Server Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher

### Development Requirements (Optional)

- **Node.js**: 16.0 or higher
- **npm**: 7.0 or higher

## Verification

After installation, verify everything is working:

1. **Frontend Check**

   - Visit your website
   - Check responsive design on mobile/tablet
   - Verify navigation menu works
   - Test mobile menu toggle

2. **Admin Check**

   - Check Appearance → Customize works
   - Verify default pages were created
   - Test widget areas

3. **Performance Check**
   - Check page load speed
   - Verify CSS and JS files load correctly

## Troubleshooting

### Common Issues

**Theme not appearing in admin**

- Check file permissions (755 for directories, 644 for files)
- Verify `style.css` has proper WordPress theme header

**CSS not loading**

- Run `npm run build` to compile Tailwind CSS
- Check if `assets/css/main.css` exists
- Clear any caching plugins

**JavaScript not working**

- Check browser console for errors
- Verify `assets/js/main.js` exists
- Ensure jQuery is loaded

**Default pages not created**

- Deactivate and reactivate the theme
- Check WordPress user permissions
- Verify database write permissions

### Getting Help

If you encounter issues:

1. Check the [troubleshooting section](#troubleshooting)
2. Search existing [GitHub issues](https://github.com/bsobro/mcqhome-wordpress-theme/issues)
3. Create a new issue with:
   - WordPress version
   - PHP version
   - Error messages
   - Steps to reproduce

## Next Steps

After successful installation:

1. **Content Setup**

   - Add your logo (Appearance → Customize → Site Identity)
   - Create your content pages
   - Set up your navigation menus

2. **Customization**

   - Configure theme colors and fonts
   - Add your branding
   - Set up social media links

3. **Development** (if applicable)
   - Set up development environment
   - Start building MCQ functionality
   - Follow the [contributing guide](CONTRIBUTING.md)

## Updates

### Automatic Updates (Future)

The theme will support automatic updates through WordPress admin.

### Manual Updates

1. Download the latest version
2. Replace theme files (backup first!)
3. Run `npm run build` if using development version
4. Clear any caches

## Support

For installation support:

- 📧 Email: support@mcqhome.com
- 📖 Documentation: [GitHub Wiki](https://github.com/bsobro/mcqhome-wordpress-theme/wiki)
- 🐛 Issues: [GitHub Issues](https://github.com/bsobro/mcqhome-wordpress-theme/issues)
