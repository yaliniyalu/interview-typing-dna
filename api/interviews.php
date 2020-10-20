<?php
include_once "../db.php";
include_once __DIR__ . '/class/SimpleImage.php';

tryLogin();
allowAdminPanelUsers();

$action = $_GET['action'] ?? '';
global $mysql;

if ($action === 'add-question') {
    $fields = ['job_application_id', 'interview_level_id', 'question'];
    validateRequiredFields($fields, $_POST);

    $insert = array_filter($_POST, fn($v, $k) => in_array($k, $fields), ARRAY_FILTER_USE_BOTH);
    $result = $mysql->insert('interview_questions', $insert);

    if ($result) {
        echo success_response("Question Added", ['id' => $result]);
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'add-answer') {
    $fields = ['job_application_id', 'interview_level_id', 'answer'];
    validateRequiredFields($fields, $_POST);

    $result = $mysql
        ->where('job_application_id', $_POST['job_application_id'])
        ->where('interview_level_id', $_POST['interview_level_id'])
        ->update('interview_questions', ['answer' => $_POST['answer']]);

    if ($result) {
        echo success_response("Answer Added", ['id' => $result]);
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'finish-interview') {
    validateRequiredFields(['id', 'interview_id'], $_POST);

    $id = $_POST['id'];

    $update = [
        'attended_on' => date('Y-m-d H:i:s', time()),
        'interviewed_by' => $_SESSION['user_id']
    ];

    $result = $mysql
        ->where('job_application_id', $id)
        ->where('interview_level_id', $_POST['interview_id'])
        ->update('interview_details', $update);

    if ($result) {
        echo success_response("Interview Updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'save-candidate-photo') {
    validateRequiredFields(['id', 'interview_id'], $_POST);

    $id = $_POST['id'];

    if (empty($_FILES['image']) || !$_FILES['image']['name']) {
        echo error_response("Image is required");
        exit;
    }

    $_FILES['image']['name'] .= '.jpeg';
    $image_name = uploadImage($_FILES['image'], INTERVIEW_PROFILE_UPLOAD_DIR);

    $image = new SimpleImage();
    $image->load(INTERVIEW_PROFILE_UPLOAD_DIR . '/' . $image_name);
    $image->resize(150, 150);
    $image->save(INTERVIEW_PROFILE_UPLOAD_DIR . '/' . $image_name, IMAGETYPE_PNG);

    $result = $mysql
        ->where('job_application_id', $id)
        ->where('interview_level_id', $_POST['interview_id'])
        ->update('interview_details', ['attended_by_photo' => $image_name]);

    if ($result) {
        echo success_response("Interview Updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}