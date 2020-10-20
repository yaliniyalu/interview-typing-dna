<?php
/**
 * Created by PhpStorm.
 * User: Aju
 * Date: 03-11-2019
 * Time: 05:14 PM
 */


function secure($value) {
    global $mysql;

    return $mysql->escape($value);
}


function e($e) {
    echo htmlspecialchars($e);
}

function _e($e) {
    return htmlspecialchars($e);
}

function error_response($message, $data = []) {
    return json_encode(['success' => false, 'message' => $message, 'data' => $data]);
}

function success_response($message, $data = []) {
    return json_encode(['success' => true, 'message' => $message, 'data' => $data]);
}

function redirect($link) {
    header("location: $link");
    exit;
}

function tryLogin() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        return true;
    }

    header("location: login.php");
    exit;
}

function tryApplicationLogin() {
    if (isset($_SESSION['application_code']) && $_SESSION['application_code'] == $_GET['id']) {
        return true;
    }

    header("location: candidate-login.php?id={$_GET['id']}");
    exit;
}

function allowAdminPanelUsers() {
    if (in_array($_SESSION['user_type'], ['Admin', 'Employee'])) {
        return;
    }

    echo error_response("Only admin can access");
    exit;
}

function validateRequiredFields($required_fields, $arr) {
    foreach ($required_fields as $required_field) {
        if (empty($arr[$required_field])) {
            echo error_response("Required Fields Missing ({$required_field})");
            exit;
        }
    }
}

function get_context_value($name, $default = null) {
    global $context;
    if ($context && isset($context[$name])) {
        return $context[$name];
    }
    return $default;
}

function e_ctx($name) {
    e(get_context_value($name, ''));
}

function _e_ctx($name) {
    return get_context_value($name, '');
}

function get_profile_image($image) {
    if ($image) {
        return 'uploads/images/' . $image;
    }

    return 'assets/images/avatar.png';
}

function get_application_profile_image($image) {
    if ($image) {
        return 'uploads/applications/' . $image;
    }

    return 'assets/images/avatar.png';
}

function uploadImage($file, $dir) {
    $file_name   = $file['name'];
    $file_size   = $file['size'];
    $file_tmp    = $file['tmp_name'];
    $file_type   = $file['type'];

    if ($file_size > 5242880) {
        throw new Exception("File size must be 5 mb");
    }

    $file_secure  = array('jpg', 'jpeg', 'png', 'gif');
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (in_array($extension, $file_secure) === false) {
        throw new Exception("Accepted file types are " . implode(',', $file_secure));
    }

    $new_name = md5($file_name . time() . rand()) . '.' . $extension;
    $file_target = $dir . '/' . $new_name;

    if (move_uploaded_file($file_tmp, $file_target)) {
        return $new_name;
    }

    throw new Exception("Unable to upload image");
}

function typingDNAMatch($newPattern, $oldPattern, $quality = 2) {
    $response = requestTypingDNAMatch($newPattern, $oldPattern, $quality);
    return ($response['result'] ?? null) == 1;
}

function requestTypingDNAMatch($newPattern, $oldPattern, $quality = 2) {
    $base_url = 'https://api.typingdna.com/match';

    $ch = curl_init($base_url);
    $data = ['tp1' => $newPattern, 'tp2' => $oldPattern, 'quality' => $quality];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_USERPWD, TYPING_DNA_API_KEY . ":" . TYPING_DNA_API_SECRET);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data) . "\n");

    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);
    return $response;
}

set_exception_handler (function (Throwable $ex) {
    echo error_response($ex->getMessage(), []);
});