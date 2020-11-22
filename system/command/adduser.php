<?php
require_once(dirname(__FILE__) . '/../library/adduser.php');
require_once(dirname(__FILE__) . '/../library/basic.php');

$username = post('username');
$password = post('password');

add_user($username, $password);
echo 'OK';
