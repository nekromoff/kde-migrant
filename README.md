# KDE Migrant 🧳

KDE Migrant allows you to migrate your existing KDE configuration to a new computer. Good when changing computers or cloning one user configuration for other users.

A single file browser and command-line script that allows you to backup your full or partial KDE configuration including apps, dotfiles and any customizations. It works for KDE Plasma widgets as well.

It creates a ZIP file that you can transfer to a different computer to unzip it.

## 1. CLI: Gather user home directory structure
Run from command line:

```php migrant.php scan```

or run `scan` as a different user:

```sudo -u [user] php migrant.php scan```

## 2. Browser: Configure which settings to back up
2. Select configuration folders and files to backup (or select whole categories such as KDE, Plasma, Flatpaks, Snaps)
3. Confirm to create backup configuration

## 3. CLI: Run backup process

Dry run to simulate backup based on existing configuration:

```php migrant.php dryrun```

Run backup based on existing configuration:

```php migrant.php backup```

or run `backup` as a different user:

```sudo -u [user] php migrant.php backup```

**migrant.zip file will be created.**

## 4. Transfer migrant.zip to target computer

And extract it there.

**Please note that KDE Migrant 🧳 creates a ZIP file with full paths (e.g. /home/user/*). When cloning single user's config files for multiple users, extract and change paths as needed.**

## Screenshots

## Please ⭐ star 🌟 this repo, if you like it and use it.
