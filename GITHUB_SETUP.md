# How to Add MoveMigo Project to GitHub

## Step-by-Step Guide

### Prerequisites
- GitHub account (create at https://github.com)
- Git installed on your computer
- Your MoveMigo project ready

---

## Method 1: Using Command Line (Recommended)

### Step 1: Create a New Repository on GitHub

1. Go to https://github.com
2. Click the **"+"** icon in the top right
3. Select **"New repository"**
4. Fill in:
   - **Repository name**: `movemigo` (or your preferred name)
   - **Description**: "Rental and relocation platform built with PHP and MySQL"
   - **Visibility**: Choose Public or Private
   - **DO NOT** check "Initialize with README" (we already have files)
5. Click **"Create repository"**

### Step 2: Initialize Git in Your Project

Open **Command Prompt** (Windows) or **Terminal** (Mac/Linux) and navigate to your project:

```bash
cd C:\xampp\htdocs\movemigo
```

### Step 3: Initialize Git Repository

```bash
git init
```

### Step 4: Add All Files

```bash
git add .
```

### Step 5: Create Initial Commit

```bash
git commit -m "Initial commit: MoveMigo rental platform"
```

### Step 6: Connect to GitHub Repository

Replace `YOUR_USERNAME` with your GitHub username:

```bash
git remote add origin https://github.com/YOUR_USERNAME/movemigo.git
```

### Step 7: Push to GitHub

```bash
git branch -M main
git push -u origin main
```

You'll be prompted for your GitHub username and password (or personal access token).

---

## Method 2: Using GitHub Desktop (Easier for Beginners)

### Step 1: Download GitHub Desktop
- Download from: https://desktop.github.com
- Install and sign in with your GitHub account

### Step 2: Create Repository on GitHub
- Follow Step 1 from Method 1 above

### Step 3: Add Repository in GitHub Desktop
1. Open GitHub Desktop
2. Click **"File"** → **"Add Local Repository"**
3. Click **"Choose"** and select: `C:\xampp\htdocs\movemigo`
4. Click **"Add Repository"**

### Step 4: Publish to GitHub
1. In GitHub Desktop, click **"Publish repository"**
2. Select your GitHub account
3. Choose the repository name
4. Click **"Publish Repository"**

---

## Method 3: Using VS Code (If you use VS Code)

### Step 1: Open Project in VS Code
- Open VS Code
- File → Open Folder → Select `C:\xampp\htdocs\movemigo`

### Step 2: Initialize Git
1. Open Terminal in VS Code (Ctrl + `)
2. Run: `git init`
3. Run: `git add .`
4. Run: `git commit -m "Initial commit"`

### Step 3: Connect to GitHub
1. Click the Source Control icon (left sidebar)
2. Click "..." menu → "Remote" → "Add Remote"
3. Enter name: `origin`
4. Enter URL: `https://github.com/YOUR_USERNAME/movemigo.git`

### Step 4: Push
1. Click "..." menu → "Push" → "Push to..."
2. Select `origin`
3. Enter your GitHub credentials

---

## Important: Before Pushing

### Check Your .gitignore File

Make sure sensitive files are NOT uploaded:
- ✅ `config/database.php` - Should be in .gitignore
- ✅ `vendor/` - Should be in .gitignore
- ✅ `*.log` - Should be in .gitignore

### Create config/database.php.example

Create a template file for others:

```php
<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'movemigo_db';
    private $username = 'root';
    private $password = '';
    // ... rest of the file
}
```

---

## After Pushing: Updating Your Repository

### When You Make Changes

```bash
# Navigate to project
cd C:\xampp\htdocs\movemigo

# Check what changed
git status

# Add changed files
git add .

# Commit changes
git commit -m "Description of changes"

# Push to GitHub
git push
```

---

## Common Commands Reference

```bash
# Check status
git status

# Add specific file
git add filename.php

# Add all files
git add .

# Commit changes
git commit -m "Your commit message"

# Push to GitHub
git push

# Pull latest changes
git pull

# View commit history
git log

# Create new branch
git branch new-feature

# Switch branch
git checkout new-feature
```

---

## Troubleshooting

### Error: "remote origin already exists"
```bash
git remote remove origin
git remote add origin https://github.com/YOUR_USERNAME/movemigo.git
```

### Error: "Authentication failed"
- Use Personal Access Token instead of password
- Generate token: GitHub → Settings → Developer settings → Personal access tokens

### Error: "Large files"
- Check file sizes
- Add large files to .gitignore
- Use Git LFS for large files if needed

### Want to remove sensitive data already pushed?
1. Remove from .gitignore (temporarily)
2. Use `git filter-branch` or `git filter-repo`
3. Force push: `git push --force` (⚠️ Be careful!)

---

## Next Steps

1. ✅ Repository is on GitHub
2. Add collaborators (Settings → Collaborators)
3. Create issues for bugs/features
4. Create branches for new features
5. Write commit messages clearly

---

## Security Reminder

⚠️ **NEVER commit:**
- Database passwords
- API keys
- Session tokens
- Personal information
- `.env` files
- `config/database.php` (with real credentials)

✅ **DO commit:**
- `config/database.php.example` (template)
- Code files
- Documentation
- SQL schema files

---

## Need Help?

- GitHub Docs: https://docs.github.com
- Git Documentation: https://git-scm.com/doc
- GitHub Support: https://support.github.com

