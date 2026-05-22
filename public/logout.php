<?php
require_once __DIR__ . '/../includes/auth.php';
logoutUser();
header('Location: ' . PUBLIC_URL . 'login.php');
exit;
