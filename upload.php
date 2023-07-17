<?php
ini_set('display_errors','off');    # 關閉錯誤輸出

// 驗證文件大小
if ($_FILES['fileToUpload']['size'] > 5 * 1024 * 1024) {
    die('檔案大小無效，僅允許 5 MB 以下的 .txt 檔案。');
}

// 驗證文件類型
if ($_FILES['fileToUpload']['type'] != 'text/plain') {
    die('文件類型無效，僅允許 .txt 檔案。');
}

// 驗證檔案副檔名
$file_info = pathinfo($_FILES['fileToUpload']['name']);
if ($file_info['extension'] != 'txt') {
    die('文件類型無效，僅允許 .txt 檔案。');
}

$uploaded_file = $_FILES['fileToUpload']['tmp_name'];

// 驗證密碼長度
if (strlen($_POST['password']) < 8) {
    die('密碼長度必須至少為 8 個字符');
}

$password = $_POST['password'];
$method = 'aes-256-cbc';
$key = substr(hash('sha256', $password, true), 0, 32);
$iv = openssl_random_pseudo_bytes(16);

$uploaded_content = file_get_contents($uploaded_file);
//先將內容也base64 一下，防止最後還原內容造成編碼錯誤
$base64_content = base64_encode($uploaded_content);
$encrypted_content = openssl_encrypt($base64_content, $method, $key, OPENSSL_RAW_DATA, $iv);

// 將 IV 和加密內容一起保存到文件中，以防讀不到數據
$combined_content = $iv . $encrypted_content;
$encrypted_file_path = 'encrypted_files/' . bin2hex(random_bytes(16)) . '.txt';
file_put_contents($encrypted_file_path, $combined_content);

echo "文件已成功上傳並加密，你現在可以下載： <a href='$encrypted_file_path'>點我</a>";
?>
