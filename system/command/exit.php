<?php
require_once(dirname(__FILE__) . '/../library/session.php');
require_once(dirname(__FILE__) . '/../library/database.php');

$current = Session::current();

if ($current->get_uid() != 1) {
    $db = new Database();
    $query = $db->new_query('UPDATE `sessions` SET `expire` = 0 WHERE `tk` = ?;');

    $tk = $current->get_tk();
    $query->bind_param('s', $tk);

    $db->to_query($query);

    echo 'OK';
} else {
    echo 'You are not logined.';
}
