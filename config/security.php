<?php
function verify_sign($data, $timestamp, $nonce, $sign) {
    $token = get_session_token(); // 从session获取用户token
    $signStr = $timestamp . $nonce . $token . json_encode($data);
    return $sign === md5($signStr);
}

function decrypt_data($encrypted) {
    $token = get_session_token();
    return openssl_decrypt($encrypted, 'AES-256-CBC', $token);
}

function get_session_token() {
    // 从session获取token,如果没有则生成新的
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
} 