<?php

include_once "../db.php";

$action = $_GET['action'] ?? '';
global $mysql;

if (!isset($_GET['id'])) {
    echo error_response('invalid request');
    exit;
}

tryApplicationLogin();

if ($action === 'add-answer') {
    $fields = ['question_id', 'answer', 't_dna_pattern'];
    validateRequiredFields($fields, $_POST);

    $mysql
        ->where('id', $_POST['question_id'])
        ->where('job_application_id', $_SESSION['application_id'])
        ->where('interview_level_id', $_SESSION['interview_id'])
        ->get('interview_questions', 1);

    if (!$mysql->count) {
        echo error_response("Invalid request");
        exit;
    }

    $pattern = $mysql
        ->where('ja.id', $_SESSION['application_id'])
        ->getValue('job_applications ja', 'ja.t_dna_pattern');

    $response = requestTypingDNAMatch($_POST['t_dna_pattern'], $pattern, 1);

    $result = $mysql
        ->where('id', $_POST['question_id'])
        ->update('interview_questions', [
            'answer' => $_POST['answer'],
            't_dna_match' => ($response['result'] ?? null) == 1,
            't_dna_confidence' => $response['confidence'] ?? null
        ]);

    if ($result) {
        echo success_response("Answer Added");
        exit;
    }

    echo error_response("Something went wrong");
    exit;
}

