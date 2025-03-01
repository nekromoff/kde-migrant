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
1. Open `migrant.php` in your browser (e.g.: http://localhost/kde-migrant/migrant.php)
2. Select configuration folders and files to back up (or select whole categories such as KDE, Plasma, Flatpaks, Snaps)
3. Confirm to create backup configuration

Note: Files or folders larger than 100 KB will have filesize information on red background shown next to them.

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
### CLI: Running scan to gather home directory structure (for correct access permissions):
![0](https://github.com/user-attachments/assets/e48a9221-897d-4375-a280-d1f5ccdc73ec)

### Browser: Select folders and files to back up / migrate:
![1](https://github.com/user-attachments/assets/8de4a473-3fde-469e-b3b0-c273f207a876)

![2](https://github.com/user-attachments/assets/190b6b3e-7f3b-4db3-b281-282e026f7768)

### Browser: Configuration created
![3](https://github.com/user-attachments/assets/7fe2035b-b234-4d48-a24c-8d075ceab8df)

### CLI: Usage and help
![help](https://github.com/user-attachments/assets/324be6f1-e4fb-4d4b-ab9b-178a51948ac4)





## Please ⭐ star 🌟 this repo, if you like it and use it.
