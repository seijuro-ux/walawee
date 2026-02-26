<?php
$target_url = "https://suarabmi.id/404.jpg"; // Ganti dengan URL yang Anda gunakan

echo "Panjang URL: " . strlen($target_url) . "\n";
echo "Isi URL: '" . $target_url . "'\n";

for ($i = 0; $i < strlen($target_url); $i++) {
    $char = $target_url[$i];
    $ord = ord($char);
    echo "Posisi $i: '$char' (ASCII: $ord)\n";
}

// Sekarang coba encode
$BRPl = array_merge(
    range('a','z'),
    range('A','Z'),
    range('0','9'),
    ['.',':','/','_','-','?','=']
);

$WGZP = [];
foreach (str_split($target_url) as $char) {
    $idx = array_search($char, $BRPl);
    if ($idx === false) {
        echo "⚠️ Karakter tidak dikenali: '$char' (ASCII: " . ord($char) . ")\n";
        die("STOP: Fix the URL first!");
    }
    $WGZP[] = $idx;
}

echo "\n✅ Output benar:\n";
print_r($WGZP);
?>