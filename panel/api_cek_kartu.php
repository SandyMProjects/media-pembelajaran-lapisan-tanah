<?php
$file = '../temp_rfid.txt';

if(file_exists($file)){
    $isi = trim(file_get_contents($file));
    
    if($isi != ""){
        echo json_encode(["status" => "success", "rfid" => $isi]);
        exit;
    }
}
echo json_encode(["status" => "empty"]);
?>
