<?php
/**
 * Created by PhpStorm.
 * User: Aju
 * Date: 03-11-2019
 * Time: 05:12 PM
 */

include_once "../db.php";


$action = $_GET['action'] ?? null;

global $mysql;


if ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($_POST['email']) || empty($_POST['password'])) {
        echo error_response("Email or Password Wrong");
        exit;
    }

    $user = $mysql
        ->where('is_active', true)
        ->where('email', mb_strtolower($email))
        ->where('password', $password)
        ->getOne('users');

    $sql = $mysql->getLastQuery();

    if (!$mysql->count) {
        echo error_response("Email or Password Wrong");
        exit;
    }

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_avatar'] = $user['avatar'];
    $_SESSION['user_type'] = $user['type'];

    echo success_response("Login Successful", $_SESSION);
    exit;
}
elseif ($action == 'candidate-login') {
    if (empty($_POST['code']) || empty($_POST['t_dna_pattern'])) {
        echo error_response("Code or Pattern Wrong");
        exit;
    }

    $pattern = $_POST['t_dna_pattern'] ?? '';
    $code = $_POST['code'] ?? '';

    $application = $mysql
        ->where('ja.code', $code)
        ->getOne('job_applications ja', 'ja.id, ja.code, ja.t_dna_pattern');

    if (!$mysql->count) {
        echo error_response("Application not found");
        exit;
    }

    if (!typingDNAMatch($pattern, $application['t_dna_pattern'])) {
        echo error_response("Unable to authenticate. Please try again later.");
        exit;
    }

    $_SESSION['candidate_logged_in'] = true;
    $_SESSION['application_code'] = $application['code'];
    $_SESSION['application_id'] = $application['id'];

    echo success_response("Login Successful", $_SESSION);
    exit;
}
