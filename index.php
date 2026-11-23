<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

if (!empty($_SESSION['id_user'])) {
    redirect('presensi/index.php');
}

redirect('login.php');
