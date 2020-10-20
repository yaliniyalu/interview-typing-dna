<?php

/**
 * Created by PhpStorm.
 * User: Aju
 * Date: 03-11-2019
 * Time: 05:12 PM
 */

include_once "../db.php";
include_once __DIR__ . '/class/SimpleImage.php';

tryLogin();

if ($_SESSION['user_type'] !== 'Admin') {
    echo error_response("Only admin can access");
    exit;
}

global $mysql;

$action = $_GET['action'] ?? '';


if ($action === 'get') {
    if (!empty($_GET['id'])) {
        $mysql->where('id', $_GET['id']);
    }

    $users = $mysql
        ->where('type', 'User', '!=')
        ->where('is_active', true)
        ->orderBy('id', 'desc')
        ->get('users');

    echo success_response("Users", $users);
    exit;
} else if ($action === 'delete') {
    if (empty($_POST['id'])) {
        echo error_response("Id required");
        exit;
    }

    if ($_POST['id'] == $_SESSION['user_id']) {
        echo error_response("You cannot delete your account.");
        exit;
    }

    $result = $mysql
        ->where('id', $_POST['id'])
        ->update('users', ['is_active' => false, 'email' => null]);

    if ($result) {
        echo success_response("Account deleted");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
} else if ($action === 'update') {
    if (empty($_POST['id'])) {
        echo error_response("Id required");
        exit;
    }

    if ($_POST['id'] == $_SESSION['user_id']) {
        echo error_response("Edit your account from my account page.");
        exit;
    }

    if (empty($_POST['name']) || empty($_POST['type'])) {
        echo error_response("Required Fields Missing (Name, Type)");
        exit;
    }

    $update = [
        'name'  => $_POST['name'],
        'type' => $_POST['type']
    ];

    if (!empty($_FILES['image']) && $_FILES['image']['name']) {
        $update['avatar'] = uploadImage($_FILES['image'], PROFILE_IMAGE_UPLOAD_DIR);

        $image = new SimpleImage();
        $image->load(PROFILE_IMAGE_UPLOAD_DIR . '/' . $update['avatar']);
        $image->resize(150, 150);
        $image->save(PROFILE_IMAGE_UPLOAD_DIR . '/' . $update['avatar'], IMAGETYPE_PNG);
    }

    $result = $mysql
        ->where('id', $_POST['id'])
        ->update('users', $update);

    if ($result) {
        echo success_response("Account updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
} else if ($action === 'add') {
    if (empty($_POST['name']) || empty($_POST['type']) || empty($_POST['email']) || empty($_POST['password'])) {
        echo error_response("Required Fields Missing (Name, Type, Email, Password)");
        exit;
    }

    $data = [
        'name'  => $_POST['name'],
        'type'       => $_POST['type'],
        'email'      => mb_strtolower($_POST['email']),
        'password'   => $_POST['password'],
    ];

    if (!empty($_FILES['image']) && $_FILES['image']['name']) {
        $data['avatar'] = uploadImage($_FILES['image'], PROFILE_IMAGE_UPLOAD_DIR);

        $image = new SimpleImage();
        $image->load(PROFILE_IMAGE_UPLOAD_DIR . '/' . $data['avatar']);
        $image->resize(150, 150);
        $image->save(PROFILE_IMAGE_UPLOAD_DIR . '/' . $data['avatar'], IMAGETYPE_PNG);
    }

    $result = $mysql
        ->insert('users', $data);

    if ($result) {
        echo success_response("Account Added", ['id' => $mysql->getInsertId()]);
        exit;
    }

    if ($mysql->getLastErrno() === 1062) {
        echo error_response("Email already registered");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}