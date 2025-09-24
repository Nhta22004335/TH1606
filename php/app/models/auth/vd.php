<?php
    // $command = "/opt/venv/bin/python ../../helpers/xuly_matkhau.py " . escapeshellarg('123456'); 
    // $result = shell_exec($command);

    // echo $result;
?>

<?php

$image_path = '/var/www/html/app/models/auth/ht.png';
$script_path = '/var/www/html/app/helpers/vd.py';

// Redirect stderr để loại bỏ warning YOLO (nếu có)
$cmd = escapeshellcmd("/opt/venv/bin/python $script_path $image_path 2>/dev/null");
$output = shell_exec($cmd);

// Chuyển JSON sang mảng PHP
$result = json_decode($output, true);

// Nếu có lỗi parse JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    $result = ["error" => json_last_error_msg(), "raw_output" => $output];
}

echo "<pre>";
print_r($result);
echo "</pre>";


?>

