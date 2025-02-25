# KDE Migrant 🧳

KDE Migrant allows you to migrate your existing KDE configuration to a new computer. Good when changing computers or cloning one user configuration for other users.

A single file browser and command-line script that allows you to backup your full or partial KDE configuration including apps, dotfiles and any customizations. It works for KDE Plasma widgets as well.

It creates a ZIP file that you can transfer to a different computer to unzip it.

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

## Screenshots
![1](https://github.com/user-attachments/assets/f592ff9d-1e83-4233-a888-b8b7092dd736)

![2](https://github.com/user-attachments/assets/9ba5f703-2640-44e4-a2e3-757cc27b8cae)

![3](https://github.com/user-attachments/assets/7322b49c-1e9c-4d5c-9ac7-3217e55b7f5b)

![4](https://github.com/user-attachments/assets/7a2e23d6-7dbb-4981-9a52-4ada4855ee5b)

![5](https://github.com/user-attachments/assets/3f2b0d57-5e52-411d-95e3-4ecfeca9e736)

![6](https://github.com/user-attachments/assets/d0991f50-d11b-4f02-bb9d-6119f4672ced)

![7](https://github.com/user-attachments/assets/5a329ead-4656-49f3-92b3-ed2a24f9f5b4)

## Please ⭐ star 🌟 this repo, if you like it and use it.
