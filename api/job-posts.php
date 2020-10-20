<?php
include_once "../db.php";

tryLogin();

allowAdminPanelUsers();

$action = $_GET['action'] ?? '';
global $mysql;

if ($action === 'get') {
    if (!empty($_GET['id'])) {
        $mysql->where('id', $_GET['id']);
    }

    if (empty($_GET['deleted']) || $_GET['deleted'] == 0) {
        $mysql->where('is_active', true);
    }

    $posts = $mysql
        ->orderBy('id', 'desc')
        ->get('job_posts');

    echo success_response("Job Posts", $posts);
    exit;
} else if ($action === 'add') {
    $fields = ['title', 'skills', 'experience', 'salary', 'details'];

    validateRequiredFields(['title'], $_POST);

    $insert = array_filter($_POST, fn($v, $k) => in_array($k, $fields), ARRAY_FILTER_USE_BOTH);

    $insert['created_by'] = $_SESSION['user_id'];

    $result = $mysql->insert('job_posts', $insert);

    if ($result) {
        echo success_response("Job Post Added", ['id' => $result]);
        exit;
    }

    echo error_response("Something went wrong");
    exit;
} else if ($action === 'update') {

    $fields = ['title', 'skills', 'experience', 'salary', 'details'];

    validateRequiredFields(['id', 'title'], $_POST);

    $id = $_POST['id'];
    $update = array_filter($_POST, fn($v, $k) => in_array($k, $fields), ARRAY_FILTER_USE_BOTH);

    $result = $mysql
        ->where('id', $id)
        ->update('job_posts', $update);

    if ($result) {
        echo success_response("Job Post Added", ['id' => $id]);
        exit;
    }

    echo error_response("Something went wrong");
    exit;
} else if ($action === 'delete') {

    validateRequiredFields(['id'], $_POST);

    $id = $_POST['id'];

    $result = $mysql
        ->where('id', $id)
        ->update('job_posts', ['is_active' => false]);

    if ($result) {
        echo success_response("Post deleted");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
