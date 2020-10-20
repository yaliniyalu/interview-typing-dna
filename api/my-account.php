<?php
include_once "../db.php";
include_once  __DIR__ . '/class/SimpleImage.php';

tryLogin();

global $mysql;

$action = $_GET['action'] ?? '';

if ($action === 'update') {

    if (empty($_POST['name']) || empty($_POST['email'])) {
        echo error_response("Required Fields Missing (Name, Email)");
        exit;
    }

    $id = $_SESSION['user_id'];

    $data = $mysql
        ->where('email', $_POST['email'])
        ->getOne('users', 'id');

    if (count($data) && $data['id'] !== $id) {
        echo error_response("Email already exists");
        exit;
    }

    $update = [
        'name'  => $_POST['name'],
        'email'      => mb_strtolower($_POST['email'])
    ];

    if (!empty($_FILES['image']) && $_FILES['image']['name']) {
        $update['avatar'] = uploadImage($_FILES['image'], PROFILE_IMAGE_UPLOAD_DIR);

        $image = new SimpleImage();
        $image->load(PROFILE_IMAGE_UPLOAD_DIR . '/' . $update['avatar']);
        $image->resize(150, 150);
        $image->save(PROFILE_IMAGE_UPLOAD_DIR . '/' . $update['avatar'], IMAGETYPE_PNG);
    }

    $result = $mysql
        ->where('id', $id)
        ->update('users', $update);

    $_SESSION['user_name']   = $_POST['name'];
    $_SESSION['user_email']  = $_POST['email'];

    if (isset($update['avatar']))
        $_SESSION['user_avatar'] = $update['avatar'];

    if ($result) {
        echo success_response("Account updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}

elseif ($action === 'update-password') {
    if (empty($_POST['password']) || empty($_POST['new_password']) || empty($_POST['r_new_password'])) {
        echo error_response("Required Fields Missing");
        exit;
    }

    $id = $_SESSION['user']['id'];

    if ($_POST['new_password'] !== $_POST['r_new_password']) {
        echo error_response("New passwords mismatch");
        exit;
    }

    $data = $mysql
        ->where('id', $id)
        ->getOne('users', 'password');

    if (!count($data) || $data['password'] !== $_POST['password']) {
        echo error_response("Invalid Current Password");
        exit;
    }

    $result = $mysql
        ->where('id', $id)
        ->update('users', [
            'password' => $_POST['new_password'],
        ]);

    if ($result) {
        echo success_response("Password updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'get-district') {
    $id = $_SESSION['user']['id'];

    $district = $mysql
         ->where('user_id', $id)
        ->join('districts d', 'dm.district_id = d.id')
        ->get('district_members dm');

    echo success_response("District", $district);
    exit;
}