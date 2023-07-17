<?php
ini_set('display_errors','off');    # 關閉錯誤輸出

// 驗證文件大小
if ($_FILES['fileToDecrypt']['size'] > 5 * 1024 * 1024) {
    die('檔案大小無效，僅允許 5 MB 以下的 .txt 檔案。');
}

// 驗證文件類型
if ($_FILES['fileToDecrypt']['type'] != 'text/plain') {
    die('文件類型無效，僅允許 .txt 檔案。');
}

// 驗證檔案副檔名
$file_info = pathinfo($_FILES['fileToDecrypt']['name']);
if ($file_info['extension'] != 'txt') {
    die('文件類型無效，僅允許 .txt 檔案。');
}

if (!isset($_POST['password_to_decrypt'])) {
    die('解密文件需要密碼，請你檢查');
}

$encrypted_file = $_FILES['fileToDecrypt']['tmp_name'];
$password_to_decrypt = $_POST['password_to_decrypt'];
$key = substr(hash('sha256', $password_to_decrypt, true), 0, 32);

$method = 'aes-256-cbc';

$encrypted_file_content = file_get_contents($encrypted_file);

// 從文件中分離出 IV 和加密內容
$iv = substr($encrypted_file_content, 0, 16);
$encrypted_content = substr($encrypted_file_content, 16);

//將內容base64 解密
$decrypted_base64_content = openssl_decrypt($encrypted_content, $method, $key, OPENSSL_RAW_DATA, $iv);

if ($decrypted_base64_content === false) {
    die('無法解密文件，請確保您的文檔沒損壞以及密碼正確.');
}

$decrypted_content = base64_decode($decrypted_base64_content);

// 將解密後的內容寫入臨時文件
$temp_file_path = 'temp/' . bin2hex(random_bytes(16)) . '.txt';
file_put_contents($temp_file_path, $decrypted_content);

// 設置 HTTP headers 觸發下載
header('Content-Description: File Transfer');
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . basename($temp_file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($temp_file_path));
flush(); // Flush system output buffer

readfile($temp_file_path);

// 刪除臨時文件
unlink($temp_file_path);

// 刪除原始加密文件
unlink($encrypted_file);
exit;
?>
