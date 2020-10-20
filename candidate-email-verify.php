<?php
include_once "db.php";
include_once "menu.php";
include_once "renderers.php";

global $mysql;

if (!isset($_GET['id'])) {
    html_error_page_candidate('Email Verification', 'Application not found');
    exit;
}

if (!isset($_GET['code'])) {
    html_error_page_candidate('Email Verification', 'Code not found');
    exit;
}

$application = $mysql
    ->where('ja.code', $_GET['id'])
    ->getOne('job_applications ja', 'ja.id, ja.email, ja.name');

if (!$application) {
    html_error_page_candidate('Email Verification', 'Application not found');
    exit;
}

$code = $mysql
    ->where('job_application_id', $application['id'])
    ->where('type', 'Email')
    ->getValue('candidate_verifications', 'data');


if (!$code || empty($_GET['code']) || $_GET['code'] != $code) {
    html_error_page_candidate('Email Verification', "Invalid code");
    exit;
}

$result = $mysql
    ->where('job_application_id', $application['id'])
    ->where('type', 'Email')
    ->update('candidate_verifications', ['status' => 'Verified', 'data' => '']);

if (!$application) {
    html_error_page_candidate('Email Verification', 'Internal Server Error. Please try again later');
    exit;
}

html_error_page_candidate('Email Verification', 'Email verified successfully', 'success', 'Success!', 'check');
exit;