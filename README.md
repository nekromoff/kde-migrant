# KDE Migrant 🧳

KDE Migrant allows you to migrate your existing KDE configuration to a new computer. Good when changing computers or cloning one user configuration for other users.

A single file browser and command-line script that allows you to backup your full or partial KDE configuration including apps, dotfiles and any customizations. It works for KDE Plasma widgets as well.

It create a ZIP file that you can transfer to a different computer to unzip it.

## 1. Backup configuration in browser
1. Select user to backup
2. Select configuration folders and files to backup (i.e. KDE programs' rc-files)
3. Confirm to create backup configuration

## 2. Command-line backup process
Run from command line:

```php migrant.php```

to see info and help.

Dry run to simulate backup based on existing configuration:

```php migrant.php dryrun```

Run backup based on existing configuration:

```php migrant.php backup```

To run `backup` as a different user use:

```sudo -u [user] php migrant.php backup```

**migrant.zip file will be created.**

## 3. Transfer migrant.zip to target computer

And extract it there.

**Please note that KDE Migrant 🧳 creates a ZIP file with full paths (e.g. /home/user/*). When cloning single user's config files for multiple users, extract and change paths as needed.**

## Please ⭐ star 🌟 this repo, if you like it and use it.
