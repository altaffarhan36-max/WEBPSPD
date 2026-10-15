<?php
declare(strict_types=1);

require_once __DIR__ . '/auth/session.php';

header('Location: ' . (isset($_SESSION['user_id']) ? 'modules.php' : 'login.php'));
exit;
