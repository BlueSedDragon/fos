<?php
require_once(dirname(__FILE__) . '/default.php');
require_once(dirname(__FILE__) . '/user.php');

if ($CONFIGURED === false) {
    header("Content-Type: text/plain; charset=${ENCODING}");
    echo file_get_contents($README_FILE);
    exit;
}

if (!$PASSWORD_ROOT) {
    echo 'please to set root password on "./user.php".';
    exit;
}
