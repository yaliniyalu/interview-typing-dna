<?php
/**
 * Created by PhpStorm.
 * User: Aju
 * Date: 03-11-2019
 * Time: 06:20 PM
 */

include_once __DIR__ . '/config.php';

session_name(SESSION_NAME);
session_start();

unset($_SESSION);

header('Location: login.php');