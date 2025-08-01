# 🚀 Final Setup Commands for bsobro

## Step 1: Create GitHub Repository

1. Go to [GitHub](https://github.com) and create a new repository:
   - **Repository name**: `mcqhome-wordpress-theme`
   - **Description**: `A comprehensive WordPress theme for MCQ-based educational platform`
   - **Public** repository
   - **Don't** initialize with README, .gitignore, or license (we have them)

## Step 2: Push to GitHub

Run these commands in your terminal:

```bash
# Add GitHub as remote origin
git remote add origin https://github.com/bsobro/mcqhome-wordpress-theme.git

# Push to GitHub
git branch -M main
git push -u origin main
```

## Step 3: Create First Release

1. Go to your repository: https://github.com/bsobro/mcqhome-wordpress-theme
2. Click **"Releases"** → **"Create a new release"**
3. **Tag version**: `v1.0.0`
4. **Release title**: `MCQHome WordPress Theme v1.0.0 - Initial Release`
5. **Description**: Copy from the release template in `release.md`
6. **Publish release**

## 🎯 Your Repository URLs

- **Main Repository**: https://github.com/bsobro/mcqhome-wordpress-theme
- **Releases**: https://github.com/bsobro/mcqhome-wordpress-theme/releases
- **Issues**: https://github.com/bsobro/mcqhome-wordpress-theme/issues
- **Wiki**: https://github.com/bsobro/mcqhome-wordpress-theme/wiki

## 📦 Installation Commands for Users

### Quick Install (Download ZIP)

```bash
# Download and extract
wget https://github.com/bsobro/mcqhome-wordpress-theme/archive/refs/tags/v1.0.0.zip
unzip v1.0.0.zip -d /path/to/wordpress/wp-content/themes/
mv mcqhome-wordpress-theme-1.0.0 mcqhome-theme
```

### Developer Install (Git Clone)

```bash
cd /path/to/wordpress/wp-content/themes/
git clone https://github.com/bsobro/mcqhome-wordpress-theme.git mcqhome-theme
cd mcqhome-theme
npm install && npm run build
```

## 🔄 Future Updates

When you make changes:

```bash
git add .
git commit -m "feat: describe your changes"
git push origin main
# Then create a new release on GitHub
```

## ✅ Ready to Go!

Your theme is now:

- ✅ Version controlled with Git
- ✅ Ready for GitHub
- ✅ Installable by users worldwide
- ✅ Professional documentation
- ✅ Easy update workflow

**Next**: Create the GitHub repository and run the push commands above! 🚀
