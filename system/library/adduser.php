<?php
require_once(dirname(__FILE__) . '/../config/index.php');
require_once(dirname(__FILE__) . '/../library/hash.php');
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/error.php');
require_once(dirname(__FILE__) . '/../library/session.php');

$session = Session::current();

$db = new Database();

function gen_uid() {
    global $db;

    $result = $db->query('SELECT * FROM `users` ORDER BY `uid` DESC LIMIT 1;');

    $data = $result->fetch_all();
    $len = count($data);

    $now = -1;
    if ($len > 0) {
        $row = $data[0];

        $db_now = $row[0];
        if ($db_now > $now) {
            $now = $db_now;
        }
    }

    $next = $now + 1;
    return $next;
}

function add_account($username, $password) {
    global $db;
    global $session;

    assert_eq(is_string($username), true);
    assert_eq(is_string($password), true);

    $query = $db->new_query('SELECT * FROM `users` WHERE `name` = ?;');
    $query->bind_param('s', $username);
    $result = $db->to_query($query);
    $rows = $result->fetch_all();
    if (count($rows) > 0) {
        panic('This account is found!');
        exit;
    }

    $session->write_check();

    $pbkdf2 = new PBKDF2($password, null);

    $uid = gen_uid();
    $name = $username;
    $group = 'undefined';
    $lock = time() * 10000;
    $password_pbkdf2 = $pbkdf2->get_hash();
    $password_pbkdf2_salt = $pbkdf2->get_salt();
    $last_write = 0;

    $query = $db->new_query('INSERT INTO `users` (`uid`, `name`, `group`, `lock`, `password_pbkdf2`, `password_pbkdf2_salt`, `last_write`) VALUES (?, ?, ?, ?, ?, ?, ?);');
    $query->bind_param('dssdssd', $uid, $name, $group, $lock, $password_pbkdf2, $password_pbkdf2_salt, $last_write);
    $db->to_query($query);
}

function add_user($username, $password) {
    global $db;

    add_account($username, $password);

    $query = $db->new_query('UPDATE `users` SET `group` = "user", `lock` = 0 WHERE `name` = ?;');
    $query->bind_param('s', $username);
    $db->to_query($query);
}

function add_root($username, $password) {
    global $db;

    add_account($username, $password);

    $query = $db->new_query('UPDATE `users` SET `group` = "root", `lock` = 0 WHERE `name` = ?;');
    $query->bind_param('s', $username);
    $db->to_query($query);
}

function add_guest($username) {
    global $db;

    add_account($username, '');

    $query = $db->new_query('UPDATE `users` SET `group` = "guest", `lock` = 0 WHERE `name` = ?;');
    $query->bind_param('s', $username);
    $db->to_query($query);
}

//adduser('abc', 'source');
