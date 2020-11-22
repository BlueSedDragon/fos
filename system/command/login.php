<?php
require_once(dirname(__FILE__) . '/../config/index.php');
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/hash.php');
require_once(dirname(__FILE__) . '/../library/session.php');
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/http.php');

$db = new Database();

function login($username, $password) {
    global $db;

    assert_eq(is_string($username), true);
    assert_eq(is_string($password), true);

    $query = $db->new_query('SELECT * FROM `users` WHERE `name` = ? LIMIT 1;');
    $query->bind_param('s', $username);

    $result = $db->to_query($query);
    $rows = $result->fetch_all();
    if (count($rows) <= 0) {
        // user not found.
        panic('user is not found!');
        exit;
    }

    $row = $rows[0];

    $verify = [];
    $verify['uid'] = $row[0];
    $verify['name'] = $row[1];
    $verify['group'] = $row[2];
    $verify['lock'] = $row[3];
    $verify['password_pbkdf2'] = $row[4];
    $verify['password_pbkdf2_salt'] = $row[5];

    // user locked.
    if ($verify['lock'] > time()) {
        panic('user is locked!');
        exit;
    }

    $password_pbkdf2 = new PBKDF2($password, $verify['password_pbkdf2_salt']);
    $try = $password_pbkdf2->get_hash();

    if (
        ($try == $verify['password_pbkdf2']) ||
        ($verify['group'] == 'guest')
    ) {
        // password correct or account is a guest.

        $session = new Session($verify['uid']);
        $session->get_tk();
        return $session;
    }

    // password incorrect.
    return null;
}

function main() {
    global $db;
    global $COOKIE_TK;

    $username = post('username');
    $password = post('password');

    $session = login($username, $password);
    if (!$session) {
        // anti the exhaustive attack.
        {
            $lock = time() + 10;
            $name = $username;

            $query = $db->new_query('UPDATE `users` SET `lock` = ? WHERE `name` = ?;');
            $query->bind_param('ds', $lock, $name);
            $db->to_query($query);
        }

        echo 'Login failed: password is incorrect! this user will be automatic locked, and automatic unlocked at ten seconds later.';
        exit;
    }

    $tk = $session->get_tk();
    setcookie($COOKIE_TK, $tk, time() + 86400, '/');
    redirect('/');
}

main();
