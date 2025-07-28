<?php
set_time_limit(900);
const PATH = './';
const WARNING_SIZE = 100000; // bytes
const FOLDERS_SCAN = ['/.', '/.config/', '/.local/share/', '/.local/share/', '/.var/app/', '/snap/'];
const FILES_SCAN = ['/.config/', '/.local/share/', '/.local/share/', '/.var/app/', '/snap/'];
const KDE_MATCH = 'k|rc|pulse|session|gtk|x|autostart|xbel';

function scanFolders($path)
{
    $home = $_SERVER['HOME'];
    $items = [];
    $folders = glob($home . $path . '*', GLOB_ONLYDIR);
    foreach ($folders as $folder) {
        if (!is_dir($folder) or basename($folder) == '.' or basename($folder) == '..') {
            continue;
        }
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder), RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $file) {
            if ($file->isFile()) {
                $items[$folder] = $items[$folder] ?? 0;
                $items[$folder] += $file->getSize();
            }
        }
    }
    return $items;
}

function scanFiles($path)
{
    $home = $_SERVER['HOME'];
    $items = [];
    $files = glob($home . $path . '*');
    foreach ($files as $file) {
        if (is_dir($file) or basename($file) == '.' or basename($file) == '..') {
            continue;
        }
        $items[$file] = filesize($file);
    }
    return $items;
}

function displayItems($items, $path = '', $heading = '')
{
    $filtered_items = [];
    foreach ($items as $item => $size) {
        $parts = pathinfo($item);
        if ($parts['dirname'] == $path) {
            $filtered_items[$item] = $size;
        }
    }
    ksort($filtered_items);
    if (!empty($filtered_items)) {
        echo '<hr><h2>' . $heading . ' ' . $path . '</h2>';
        foreach ($filtered_items as $item => $size) {
            $name = basename($item);
            echo '<input type="checkbox" name="items[]" value="' . $item . '" id="' . $item . '" data-size="' . $size . '"> <label for="' . $item . '">' . $name;
            if ($size > WARNING_SIZE) {
                echo ' <span class="warning">(' . formatSize($size) . ')</span>';
            }
            echo '</label><br>';
        }
    }
}

function formatSize($size)
{
    $mod = 1024;
    $units = explode(' ', 'B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    return round($size, 2) . ' ' . $units[$i];
}

$items = [];
$source = php_sapi_name();
if ($source == 'cli') {
    if (isset($argv[1]) and $argv[1] == 'scan') {
        echo "== STARTING SCAN ==\n";
        $total = count(FOLDERS_SCAN);
        foreach (FOLDERS_SCAN as $key => $folder) {
            $items = array_merge($items, scanFolders($folder));
            $percentage = round(($key + 1) / $total * 100);
            echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m\r";
            flush();
        }
        $total = count(FILES_SCAN);
        foreach (FILES_SCAN as $key => $folder) {
            $items = array_merge($items, scanFiles($folder));
            $percentage = round(($key + 1) / $total * 100);
            echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m\r";
            flush();
        }
        echo "\e[1;37m100%\e[0m\n";
        $config = ['home' => $_SERVER['HOME'], 'items' => $items];
        file_put_contents(PATH . 'migrant1.config', serialize($config));
        echo "== SCAN FINISHED ==\n";
        echo "\e[1;37mOpen migrant.php in your browser now to configure files and folders to migrate.\e[0m\n";
    } elseif (!isset($argv[1]) or (isset($argv[1]) and $argv[1] == 'help')) {
        echo "Usage: php migrant.php [COMMAND]\n\n";
        echo "  scan  \tScan user home directory\n";
        echo "  backup\tStart backup process\n";
        echo "  dryrun\tSimulate backup process (dry run)\n";
        echo "  help  \tShow this help\n\n";
        if (!file_exists(PATH . 'migrant1.config')) {
            echo "Home dir structure unknown: Use \e[0;30;103mscan\e[0m command to scan user home directory.\n";
        }
        if (file_exists(PATH . 'migrant2.config')) {
            echo "Configuration found: Use \e[0;30;103mbackup\e[0m to start backup process.\n";
        }
        echo "To run as different user: sudo -u [user] php migrant.php\n";
    } elseif (!file_exists(PATH . 'migrant2.config') and isset($argv[1]) and $argv[1] == 'backup') {
        echo "Configuration not found: \e[1;37mOpen migrant.php in your browser now to configure files and folders to migrate.\e[0m\n";
    } elseif (file_exists(PATH . 'migrant2.config') and file_exists(PATH . 'migrant1.config') and isset($argv[1]) and $argv[1] == 'backup') {
        echo "== STARTING BACKUP ==\n";
        $config = unserialize(file_get_contents(PATH . 'migrant1.config'));
        $home_directory = $config['home'];
        $config = unserialize(file_get_contents(PATH . 'migrant2.config'));
        $zip = new ZipArchive();
        $result = $zip->open(PATH . 'migrant.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result === true) {
            $config = unserialize(file_get_contents(PATH . 'migrant2.config'));
            if (!empty($config)) {
                $total = count($config);
                echo "\e[1;37mBacking up " . $total . " items\e[0m:\n";
                foreach ($config as $key => $item) {
                    $percentage = round(($key + 1) / $total * 100);
                    echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m " . $item . "\n";
                    flush();
                    if (is_dir($item)) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($item), RecursiveIteratorIterator::LEAVES_ONLY);
                        foreach ($files as $name => $file) {
                            if (!$file->isDir()) {
                                $file_path = $file->getRealPath();
                                if ($file_path) {
                                    $relative_path = str_ireplace($home_directory . '/', '', $file_path);
                                    $zip->addFile($file_path, $relative_path);
                                }
                            }
                        }
                    } else {
                        $relative_path = str_ireplace($home_directory . '/', '', $item);
                        $zip->addFile($item, $relative_path);
                    }
                }
            }
            $zip->close();
            echo "== BACKUP FINISHED ==\n";
            echo "\e[1;37mYou can now take the migrant.zip file and unzip it on target machine.\e[0m\n";
            echo <<< EOT
        _
   ____/ \____
  / \e[1;37mCONGRATS!\e[0m \
  |  YOU ARE  |
  |   NOW A   |
  | \e[0;30;103m   KDE   \e[0m |
  \ \e[0;30;103m MIGRANT \e[0m /
   ====   ====
       \_/
EOT;
            echo "\n";
        } else {
            echo "\e[0;30;103mError creating migrant.zip file.\e[0m Check folder permissions and enable write access.\n";
        }
    } elseif (file_exists(PATH . 'migrant2.config') and isset($argv[1]) and $argv[1] == 'dryrun') {
        echo "\e[0;30;103m== Dry run only ==\e[0m\n";
        $config = unserialize(file_get_contents(PATH . 'migrant2.config'));
        if (!empty($config)) {
            $total = count($config);
            $sleeptime = 3 / $total * 1000000;
            echo "\e[1;37mBacking up " . $total . " items\e[0m:\n";
            foreach ($config as $key => $item) {
                $percentage = round(($key + 1) / $total * 100);
                echo "\e[1;37m" . str_pad($percentage, 3, ' ', STR_PAD_LEFT) . "%\e[0m " . $item . "\n";
                usleep($sleeptime);
            }
        }
        echo "\e[0;30;103m== Dry run only ==\e[0m\n";
    } else {
        echo "Unrecognized command. Use \e[0;30;103mhelp\e[0m to get usage info.\n";
    }
} else {
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1" /><title>KDE Migrant 🧳</title>';
    echo '<style>
        * { margin: 0; padding: 0; }
        body { font-size: 18px; font-family: system-ui, sans-serif; padding: 1em; }
        div { padding: 1em; font-weight: bold; background: #FFF; }
        h2 { margin-top:0.5em; margin-bottom:0.2em; }
        code { background: #EEE; }
        fieldset { padding: 0.5em auto; border: 0; }
        fieldset label { margin-right: 0.5em}
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
    </style>';
    echo "<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        document.querySelectorAll('#options input[type=checkbox]').forEach(function(parent_el) {
            parent_el.addEventListener('click', (event)=> {
                let total=0;
                document.querySelectorAll('#items input[type=checkbox]').forEach(function(el) {
                    parent_ids=[parent_el.id];
                    if (parent_el.id.indexOf('|')) {
                        parent_ids=parent_el.id.split('|');
                    }
                    for (i=0; i<parent_ids.length; i++) {
                        parent_id=parent_ids[i];
                        if (parent_id.length==1 && el.id.toLowerCase().indexOf('/'+parent_id)!=-1) {
                            el.checked = parent_el.checked;
                            break;
                        } else if (parent_id.length>1 && el.id.toLowerCase().indexOf(parent_id)!=-1) {
                            el.checked = parent_el.checked;
                            break;
                        }
                    }
                });
                countSize();
            });
        });
        document.querySelectorAll('#items input[type=checkbox]').forEach(function(el) {
            el.addEventListener('click', (event)=> {
                countSize();
            });
        });
    });
    ";
    // copied from: https://stackoverflow.com/questions/15900485/correct-way-to-convert-size-in-bytes-to-kb-mb-gb-in-javascript
    echo '
    function countSize() {
        var total=0;
        document.querySelectorAll("#items input[type=checkbox]").forEach(function(el) {
            if (el.checked==true) {
                total=total+el.dataset.size*1;
            }
        });
        document.querySelectorAll("input[type=submit]").forEach(function(el) {
            var add="";
            if (total>0) {
                add=" ("+formatSize(total)+")";
            }
            el.value="Create backup configuration"+add;
        });
    }
    function formatSize(bytes, decimals = 2) {
        if (!+bytes) return "0 Bytes";
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math . pow(k, i)) . toFixed(dm))} ${sizes[i]}`;
    }
    </script>';
    echo '</head><body><h1>KDE Migrant 🧳</h1>';
    if ($_SERVER['REQUEST_METHOD'] == 'GET' and !file_exists(PATH . 'migrant1.config')) {
        echo '<div class="error"><p>You need to run</p><p><code>php migrant.php scan</code></p><p>in command line first to gather information about  user home directory.</p></div>';
    } elseif ($_SERVER['REQUEST_METHOD'] == 'GET' and file_exists(PATH . 'migrant1.config')) {
        echo '<fieldset id="options">
        <input type="checkbox" name="' . KDE_MATCH . '" id="' . KDE_MATCH . '"><label for="' . KDE_MATCH . '"> KDE</label>
        <input type="checkbox" name="plasma" id="plasma"><label for="plasma"> Plasma</label>
        <input type="checkbox" name="var/app" id="var/app"><label for="var/app"> Flatpaks</label>
        <input type="checkbox" name="snap" id="snap"><label for="snap"> Snaps</label>
        </fieldset>';
        echo '<h2>Step 1: Configure backup</h2>';
        $config = unserialize(file_get_contents(PATH . 'migrant1.config'));
        $home = $config['home'];
        $items = $config['items'];
        echo '<form id="items" method="post" action="migrant.php">';
        echo '<input type="hidden" name="step" value="2">';
        echo '<input type="submit" value="Create backup configuration">';
        displayItems($items, $home . '/.config');
        displayItems($items, $home . '/.local/share');
        displayItems($items, $home . '/.var/app', 'Flatpak:');
        displayItems($items, $home . '/snap', 'Snapcraft:');
        displayItems($items, $home);
        echo '<input type="submit" value="Create backup configuration">';
        echo '</form>';
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' and $_POST['step'] == 2) {
        $config = $_POST['items'];
        if (!empty($config)) {
            file_put_contents(PATH . 'migrant2.config', serialize($config));
            echo '<div class="info"><p>Backup settings have been saved. You can now run:</p><p><code>php migrant.php backup</code></p><p>in command line to start backup.</p></div>';
        } else {
            echo '<div class="warning"><p>No backup settings have been selected. Use Back button to select some.</p></div>';
        }
    }
    echo '</body></html>';
}
