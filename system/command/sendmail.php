<?php
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/session.php');

$current = Session::current();
$current->write_check();

$db = new Database();

function gen_mid() {
    global $db;

    $result = $db->query('SELECT * FROM `mails` ORDER BY `mid` DESC LIMIT 1;');
    $rows = $result->fetch_all();

    $now = -1;
    if (count($rows) > 0) {
        $row = $rows[0];
        $now = $row[0];
    }

    $next = $now + 1;
    return $next;
}

function sendmail($from_uid, $to_uid, $text) {
    global $db;

    assert_eq(is_integer($from_uid), true);
    assert_eq($from_uid >= 0, true);

    assert_eq(is_integer($to_uid), true);
    assert_eq($to_uid >= 0, true);

    assert_eq(is_string($text), true);

    $text = htmlentities($text);
    {
        $text = str_replace("\r\n", "<br/>", $text);
        $text = str_replace("\r", "<br/>", $text);
        $text = str_replace("\n", "<br/>", $text);

        $text = str_replace(" ", "&nbsp;", $text);
    }

    $mid = gen_mid();
    $time = time_day();

    $query = $db->new_query('INSERT INTO `mails` (`mid`, `from_uid`, `to_uid`, `time`, `text`) VALUES (?, ?, ?, ?, ?);');
    $query->bind_param('dddds', $mid, $from_uid, $to_uid, $time, $text);
    $db->to_query($query);
}

$from_uid = $current->get_uid();
$to_uid = (int) post('to_uid');
$text = post('text');

sendmail($from_uid, $to_uid, $text);
echo 'OK';
