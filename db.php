<?php
/**
 * Created by PhpStorm.
 * User: Aju
 * Date: 03-11-2019
 * Time: 05:25 PM
 */

require_once __DIR__ . '/config.php';
require_once "MysqliDb.php";

session_name(SESSION_NAME);
session_start();

$mysql = new MysqliDb(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);

$timezone        = $_SESSION['timezone'] ?? null;
$timezone_offset = $_SESSION['timezone_offset'] ?? 0;

if (isset($_GET['timezone'])) {
    $timezone = $_GET['timezone'] ?? 'UTC';

    try {
        $timezone_offset = (new DateTime('now', new DateTimeZone($timezone)))->format('P');
    } catch (Exception $e) {
        echo "Invalid Timezone";
        exit;
    }
    $_SESSION['timezone'] = $timezone;
    $_SESSION['timezone_offset'] = $timezone_offset;
}

date_default_timezone_set($timezone);
$mysql->rawQuery("set time_zone = '" . $timezone_offset . "';");

include_once "functions.php";
