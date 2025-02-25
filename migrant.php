<?php
const PATH = './';
$source = php_sapi_name();
if ($source == 'cli') {
    if (!file_exists(PATH . 'migrant.config')) {
        echo "\e[0;30;103mRun migrant.php in browser\e[0m first to configure backup options\n";
    } elseif (!isset($argv[1]) or (isset($argv[1]) and $argv[1] == 'help')) {
        echo "Usage: php migrant.php [COMMAND]\n\n";
        echo "  backup\t\tStart backup process\n";
        echo "  dryrun\t\tSimulate backup process (dry run)\n";
        echo "  help  \t\tShow this help\n\n";
        echo "Provide \e[0;30;103mbackup\e[0m command to start backup\n";
        echo "To run as different user: sudo -u [user] php migrant.php\n";
    } elseif (isset($argv[1]) and $argv[1] == 'backup') {
        echo "\e[1;37m== STARTING BACKUP ==\e[0m\n";
        $config = unserialize(file_get_contents(PATH . 'migrant.config'));
        $zip = new ZipArchive();
        $result = $zip->open(PATH . 'migrant.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result === true) {
            if (isset($config['folders']) and !empty($config['folders'])) {
                $total = count($config['folders']);
                echo "\e[1;37mBacking up " . $total . " folders\e[0m:\n";
                foreach ($config['folders'] as $key => $folder) {
                    $percentage = round(($key + 1) / $total * 100);
                    echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m " . $folder . "\n";
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder), RecursiveIteratorIterator::LEAVES_ONLY);
                    foreach ($files as $name => $file) {
                        if (!$file->isDir()) {
                            $file_path = $file->getRealPath();
                            if ($file_path) {
                                $zip->addFile($file_path);
                            }
                        }
                    }
                }
            }
            if (isset($config['files']) and !empty($config['files'])) {
                $total = count($config['files']);
                echo "\e[1;37mBacking up " . $total . " files\e[0m:\n";
                foreach ($config['files'] as $key => $file) {
                    $percentage = round(($key + 1) / $total * 100);
                    echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m " . $file . "\n";
                    $zip->addFile($file);
                }
            }
        }
        $zip->close();
        echo "\e[1;37m== BACKUP FINISHED ==\e[0m\n";
        echo "You can now take the migrant.zip file and unzip it on target machine.\n";
    } elseif (isset($argv[1]) and $argv[1] == 'dryrun') {
        echo "\e[0;30;103m== Dry run only ==\e[0m\n";
        $config = unserialize(file_get_contents(PATH . 'migrant.config'));
        if (isset($config['folders']) and !empty($config['folders'])) {
            $total = count($config['folders']);
            $sleeptime = 3 / $total * 1000000;
            echo "\e[1;37mBacking up " . $total . " folders\e[0m:\n";
            foreach ($config['folders'] as $key => $folder) {
                $percentage = round(($key + 1) / $total * 100);
                echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m " . $folder . "\n";
                usleep($sleeptime);
            }
        }
        if (isset($config['files']) and !empty($config['files'])) {
            $total = count($config['files']);
            $sleeptime = 3 / $total * 1000000;
            echo "\e[1;37mBacking up " . $total . " files\e[0m:\n";
            foreach ($config['files'] as $key => $file) {
                $percentage = round(($key + 1) / $total * 100);
                echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m " . $file . "\n";
                usleep($sleeptime);
            }
        }
        echo "\e[0;30;103m== Dry run only ==\e[0m\n";
    } else {
        echo "\e[0;30;103m== Unrecognized command ==\e[0m\n";
    }
} else {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1" /><title>KDE Migrant 🧳</title><style>
        * { margin: 0; padding: 0; }
        body { font-size: 16px; font-family: system-ui, sans-serif; padding: 1em; }
        div { padding: 1em; font-weight: bold; background: #FFF; }
        h2 { margin-top:0.5em; margin-bottom:0.2em; }
        code { background: #EEE; }
        input[type=submit] { margin-top: 1em; width:100%; height:2em; font-size: 120%; background: #000; color: #fff; border-width: 1px; cursor: pointer; }
        input[type=submit]:hover { background: #fff; color: #000; font-weight: bold; }
        label { cursor:pointer; }
        button { padding: 0 0.5em; font-size: 120%; background: #fff; color: #000; border-width: 1px; cursor: pointer; }
        button:hover { background: #000; color: #fff; font-weight: bold; }
        code { user-select: all; }
        @media (max-width: 800px) { main {flex-direction: column;} section { width: calc(100vw - 4em - 2px); } }
        .info { background: #AAFFFF; }
        .warning { background: #FFD6DA; }
        .error { background: #FFAFB0; }
    </style></head><body><h1>KDE Migrant 🧳</h1>';
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        echo '<h2>Step 1: Select user</h2>';
        echo '<form method="post" action="migrant.php">';
        echo '<label for="user">Username:</label> <input type="text" name="user">';
        echo '<input type="hidden" name="step" value="2">';
        echo '<input type="submit" value="Continue to backup options">';
        echo '</form>';
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST' and $_POST['step'] == 2) {
        echo '<h2>Step 2: Configure backup</h2>';
        $user = filter_var($_POST['user'], FILTER_SANITIZE_STRING);
        if (!file_exists('/home/' . $user)) {
            echo '<div class="error"><p>User ' . $user . ' not found.</p><p><a href="migrant.php">Go back and choose different user</a></p></div>';
        } else {
            echo '<form method="post" action="migrant.php">';
            echo '<input type="hidden" name="step" value="3">';
            echo '<input type="submit" value="Create backup configuration">';
            $folders = glob('/home/' . $user . '/.*');
            if (!empty($folders)) {
                echo '<hr><h2>/home/' . $user . '/</h2>';
                foreach ($folders as $folder) {
                    $name = basename($folder);
                    if (is_dir($folder) and $folder and $name != '.' and $name != '..') {
                        echo '<input type="checkbox" name="folders[]" value="' . $folder . '" id="' . $folder . '"> <label for="' . $folder . '">' . $name . '</label><br>';
                    }
                }
            }
            $items = glob('/home/' . $user . '/.config/*');
            if (!empty($items)) {
                echo '<hr><h2>/home/' . $user . '/.config/</h2>';
                foreach ($items as $item) {
                    $name = basename($item);
                    if (is_dir($item)) {
                        echo '<input type="checkbox" name="folders[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    } else {
                        echo '<input type="checkbox" name="files[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    }
                }
            }
            $items = glob('/home/' . $user . '/.local/share/*');
            if (!empty($items)) {
                echo '<hr><h2>/home/' . $user . '/.local/share/</h2>';
                foreach ($items as $item) {
                    $name = basename($item);
                    if (is_dir($item)) {
                        echo '<input type="checkbox" name="folders[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    } else {
                        echo '<input type="checkbox" name="files[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    }
                }
            }
            $items = glob('/home/' . $user . '/.var/app/*');
            if (!empty($items)) {
                echo '<hr><h2>Flatpak: /home/' . $user . '/.var/app/</h2>';
                foreach ($items as $item) {
                    $name = basename($item);
                    if (is_dir($item)) {
                        echo '<input type="checkbox" name="folders[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    } else {
                        echo '<input type="checkbox" name="files[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    }
                }
            }
            $items = glob('/home/' . $user . '/snap/*');
            if (!empty($items)) {
                echo '<hr><h2>Snap: /home/' . $user . '/snap/</h2>';
                foreach ($items as $item) {
                    $name = basename($item);
                    if (is_dir($item)) {
                        echo '<input type="checkbox" name="folders[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    } else {
                        echo '<input type="checkbox" name="files[]" value="' . $item . '" id="' . $item . '"> <label for="' . $item . '">' . $name . '</label><br>';
                    }
                }
            }
            echo '<input type="submit" value="Create backup configuration">';
            echo '</form>';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' and $_POST['step'] == 3) {
        $config = [];
        if (isset($_POST['folders'])) {
            $config['folders'] = $_POST['folders'];
        }
        if (isset($_POST['files'])) {
            $config['files'] = $_POST['files'];
        }
        if (!empty($config)) {
            file_put_contents(PATH . 'migrant.config', serialize($config));
            echo '<div class="info"><p>Backup settings have been saved. You can now run:</p><p><code>php migrant.php backup</code></p><p>in command line to start backup.</p></div>';
        } else {
            echo '<div class="warning"><p>No backup settings have been selected. Use Back button to select some.</p></div>';
        }
    }
    echo '</body></html>';
}
