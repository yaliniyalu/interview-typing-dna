<?php
include_once "../db.php";
include_once __DIR__ . '/class/SimpleImage.php';

$action = $_GET['action'] ?? '';
global $mysql;

if ($action !== 'add') {
    tryLogin();
    allowAdminPanelUsers();
}

if ($action === 'get') {
    if (!empty($_GET['id'])) {
        $mysql->where('id', $_GET['id']);
    }

    if (empty($_GET['deleted']) || $_GET['deleted'] == 0) {
        $mysql->where('is_active', true);
    }

    $applications = $mysql
        ->orderBy('id', 'desc')
        ->get('job_applications');

    echo success_response("Job Applications", $applications);
    exit;
}
elseif ($action === 'add') {
    $required = ['name', 'dob', 'gender', 'email', 'mobile', 'job_post_id', 't_dna_pattern'];
    $optional = ['address', 'skills', 'experience', 'comments', 'details'];
    $fields = array_merge($required, $optional);

    validateRequiredFields($required, $_POST);

    $insert = array_filter($_POST, fn($v, $k) => in_array($k, $fields), ARRAY_FILTER_USE_BOTH);

    if (!empty($_FILES['image']) && $_FILES['image']['name']) {
        $insert['avatar'] = uploadImage($_FILES['image'], APPLICATION_PROFILE_UPLOAD_DIR);

        $image = new SimpleImage();
        $image->load(APPLICATION_PROFILE_UPLOAD_DIR . '/' . $insert['avatar']);
        $image->resize(150, 150);
        $image->save(APPLICATION_PROFILE_UPLOAD_DIR . '/' . $insert['avatar'], IMAGETYPE_PNG);
    }

    $insert['code'] = md5(rand() . time());

    $result = $mysql->insert('job_applications', $insert);

    $url =  URL_WEB_ROOT . '/candidate-status.php?id=' . $insert['code'];

    include_once __DIR__ . "/../mail.php";

    $body    = "Hello {$insert['name']},<br> Your application has been submitted. Goto this link to check status.<br> <a href='" . $url . "'>Click Here</a>";
    $altBody = "Hello {$insert['name']}, Your application has been submitted. Goto this link to check status. " . $url;

    try {
        sendMail(['name' => $insert['name'], 'email' => $insert['email']], "Interview Application Status", $body, $altBody);
    } catch (\PHPMailer\PHPMailer\Exception $e) {
    }

    if ($result) {
        if (($_GET['from'] ?? null) == 'candidate') {
            $_SESSION['candidate_logged_in'] = true;
            $_SESSION['application_code'] = $insert['code'];
            $_SESSION['application_id'] = $result;
        }

        echo success_response("Job Application Added", ['id' => $result, 'redirect_url' => $url]);
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'update') {
    $required = ['name', 'dob', 'gender', 'email', 'mobile', 'job_post_id'];
    $optional = ['address', 'skills', 'experience', 'comments', 'details'];
    $fields = array_merge($required, $optional);

    validateRequiredFields(array_merge($required, ['id']), $_POST);

    $id = $_POST['id'];
    $insert = array_filter($_POST, fn($v, $k) => in_array($k, $fields), ARRAY_FILTER_USE_BOTH);

    if (!empty($_FILES['image']) && $_FILES['image']['name']) {
        $insert['avatar'] = uploadImage($_FILES['image'], APPLICATION_PROFILE_UPLOAD_DIR);

        $image = new SimpleImage();
        $image->load(APPLICATION_PROFILE_UPLOAD_DIR . '/' . $insert['avatar']);
        $image->resize(150, 150);
        $image->save(APPLICATION_PROFILE_UPLOAD_DIR . '/' . $insert['avatar'], IMAGETYPE_PNG);
    }

    $insert['status'] = 'Pending';

    $result = $mysql
        ->where('id', $id)
        ->update('job_applications', $insert);

    if ($result) {
        echo success_response("Job Application Added", ['id' => $result]);
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'delete') {
    validateRequiredFields(['id'], $_POST);

    $id = $_POST['id'];

    $result = $mysql
        ->where('id', $id)
        ->update('job_applications', ['is_active' => false]);

    if ($result) {
        echo success_response("Application deleted");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'update-status') {

    validateRequiredFields(['status', 'id'], $_POST);

    $id = $_POST['id'];

    $update = ['status' => $_POST['status']];

    $mysql
        ->where('job_application_id', $id)
        ->getValue('interview_details', '1', 1);

    $mysql->startTransaction();
    if (!$mysql->count) {
        $update['current_level'] = 1;

        $mysql->insert('interview_details', ['job_application_id' => $id, 'interview_level_id' => 1]);
    }

    $result = $mysql
        ->where('id', $id)
        ->update('job_applications', $update);

    $mysql->commit();

    if ($result) {
        echo success_response("Application Status Updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif ($action === 'update-interview-details') {

    validateRequiredFields(['id', 'level_id'], $_POST);

    $id = $_POST['id'];
    $level_id = $_POST['level_id'];

    $update = [
        'job_application_id' => $id,
        'interview_level_id' => $level_id
    ];

    if (!empty($_POST['scheduled_date'])) {
        $update['scheduled_date'] = $_POST['scheduled_date'];

        $d = date_format(date_create_from_format('Y-m-d', $update['scheduled_date']), 'U');

        if($d > time()) {
            $update['attended_on'] = null;
            $update['interviewed_by'] = null;
            $update['score'] = 0;
            $update['status'] = 'Pending';
        }
    }

    if (!empty($_POST['assigned_to'])) {
        $update['assigned_to'] = $_POST['assigned_to'];
    }

    if (!empty($_POST['interviewed_by'])) {
        $update['interviewed_by'] = $_POST['interviewed_by'];
    }

    if (!empty($_POST['attended_on'])) {
        $update['attended_on'] = $_POST['attended_on'];
    }

    if (!empty($_POST['status'])) {
        $update['status'] = $_POST['status'];
    }

    if (!empty($_POST['remarks'])) {
        $update['remarks'] = $_POST['remarks'];
    }

    $old_score = null;
    if (isset($_POST['score'])) {
        $update['score'] = $_POST['score'];
    }

    $mysql->startTransaction();

    $result = $mysql
        ->onDuplicate(array_keys($update))
        ->insert('interview_details', $update);

    $ja_update = [];

    if (isset($update['status'])) {
        if ($update['status'] == 'Promoted') {
            if ($level_id == 5) {
                $update_level = null;
            }
            else {
                $update_level = $level_id + 1;
                $mysql->insert('interview_details', ['job_application_id' => $id, 'interview_level_id' => $update_level]);
            }

            $ja_update['current_level'] = $update_level;
        }
        else if ($update['status'] == 'Rejected') {
            $ja_update['current_level'] = null;
            $ja_update['status'] = 'Rejected';
        }
    }

    if (isset($update['score'])) {
        $ja_update['total_scores'] = $mysql
            ->where('job_application_id', $id)
            ->getValue('interview_details', 'sum(score)', 1);
    }

    if (count($ja_update)) {
        $result = $mysql
            ->where('id', $id)
            ->update('job_applications', $ja_update);
    }

    $mysql->commit();

    if ($result) {
        echo success_response("Interview details Updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif($action == 'verify-details') {
    $required = ['job_application_id', 'type', 'status'];
    $fields = [...$required, 'data'];
    validateRequiredFields($required, $_POST);

    $id = $_POST['job_application_id'];
    $insert = array_filter($_POST, fn($v, $k) => in_array($k, $fields), ARRAY_FILTER_USE_BOTH);

    if ($_POST['status'] != 'Pending') {
        $insert['verified_on'] = time();
        $insert['verified_by'] = $_SESSION['user_id'];
    }

    if ($insert['type'] == 'Email' && $insert['status'] == 'Verified') {
        $code = $mysql
            ->where('job_application_id', $_POST['job_application_id'])
            ->where('type', 'Email')
            ->getValue('candidate_verifications', 'data');

        if (!$code || empty($_POST['data']) || $_POST['data'] != $code) {
            echo error_response("Invalid code");
            exit;
        }

        $insert['data'] = '';
    }

    if ($insert['type'] == 'Typing' && $insert['status'] == 'Verified') {
        $pattern = $mysql
            ->where('id', $_POST['job_application_id'])
            ->getValue('job_applications', 't_dna_pattern');

        if (!typingDNAMatch($_POST['data'], $pattern, 2)) {
            echo error_response("Typing pattern doesn't match");
            exit;
        }

        $insert['data'] = '';
    }

    $result = $mysql
        ->onDuplicate(array_keys($insert))
        ->insert('candidate_verifications', $insert);

    if ($result) {
        echo success_response("Verification details Updated");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}
elseif($action == 'email-send-code') {
    $required = ['id'];
    validateRequiredFields($required, $_POST);

    $application = $mysql
        ->where('id', $_POST['id'])
        ->getOne('job_applications', 'email, name, code');

    $code = mt_rand(123456, 987654);
    $url =  URL_WEB_ROOT . '/candidate-email-verify.php?id=' . $application['code'] . '&code=' . $code;

    include_once __DIR__ . "/../mail.php";

    $body    = "Hello {$application['name']},<br> Your email verification code is {$code}. Open this link to verify automatically.<br> <a href='" . $url . "'>Click Here</a>";
    $altBody = "Hello {$application['name']}, Your email verification code is {$code}. Open this link to verify automatically. {$url}";

    $result = $mysql
                ->onDuplicate(['data'])
                ->insert('candidate_verifications', ['job_application_id' => $_POST['id'], 'type' => 'Email', 'data' => $code]);

    try {
        sendMail(['name' => $application['name'], 'email' => $application['email']], "Interview Email Verification", $body, $altBody);
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        echo error_response("Unable to sent mail");
        exit;
    }

    echo success_response("Verification email sent", ['email' => $application['email']]);
    exit;

}
elseif ($action === 'search') {
    foreach ($_GET['order'] ?? [] as $item) {
        $column = $_GET['columns'][(int) $item['column']];

        if ($column['data'] === '_action')
            continue;

        $mysql->orderBy($column['data'], $item['dir']);
    }

    $fields = $search_fields = [ 'ja.id', 'ja.name', 'ja.gender', 'ja.status', 'ja.total_scores', 'post.title', 'level.name' ];

    if (!empty($_GET['filter']['post'])) {
        $mysql->where('post.id', $_GET['filter']['post']);

        $search_fields = array_filter($search_fields, fn($v) => $v !== "post.title");
    }

    if (!empty($_GET['filter']['level'])) {
        $mysql->where('level.id', $_GET['filter']['level']);

        $search_fields = array_filter($search_fields, fn($v) => $v !== "level.name");
    }

    $status = null;
    if (!empty($_GET['filter']['status'])) {
        $mysql->where('status', $status = $_GET['filter']['status']);

        $search_fields = array_filter($search_fields, fn($v) => $v !== "status");
    }

    if ($status != 'Rejected') {
        $mysql->where('status', 'Rejected', '!=');
    }

    if ($_GET['search']['value']) {
        $mysql->where('( 0');

        foreach ($search_fields as $field) {
            $mysql->orWhere($field, $_GET['search']['value'] . '%', 'LIKE');
        }

        foreach ($search_fields as $field) {
            $mysql->orWhere($field, '% ' . $_GET['search']['value'] . '%', 'LIKE');
        }

        foreach ($search_fields as $field) {
            $mysql->orWhere($field, '%' . $_GET['search']['value'] . '%', 'LIKE');
        }

        $mysql->orWhere('0 )');
    }

    $select_fields = array_map(function ($v) {
        return $v . ' as `' . str_replace('.', '_', $v) . '`';
    }, $fields);

    $applications = $mysql
        ->withTotalCount()
        ->orderBy('ja.id', 'desc')
        ->join('interview_levels level', 'level.id = current_level', 'LEFT')
        ->join('job_posts post', 'post.id = job_post_id', 'LEFT')
        ->get('job_applications ja', [$_GET['start'] ?? 0, $_GET['length'] ?? 10], $select_fields);

    $data = [
        'draw' => $_GET['draw'],
        'recordsTotal' => $mysql->totalCount,
        'recordsFiltered' => $mysql->totalCount,
        'data' => $applications
    ];

    echo json_encode($data);
}
