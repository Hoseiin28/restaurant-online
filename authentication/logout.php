<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

session_unset();
session_destroy();

setSuccessMessage('با موفقیت خارج شدید.');
redirect(BASE_URL . 'index.php');
?>