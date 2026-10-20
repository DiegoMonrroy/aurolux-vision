<?php
require __DIR__ . '/_private/auth.php';
auth_logout();
header('Location: login.php');
exit;
