<?php
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/error.php');

$uid = (int) get('uid');

$db = new Database();

$query = $db->new_query('SELECT * from `users` WHERE `uid` = ? LIMIT 1;');
$query->bind_param('d', $uid);

$result = $db->to_query($query);
$rows = $result->fetch_all();

if (count($rows) <= 0) {
    panic('User is not found!');
    exit;
}

$row = $rows[0];

$name = $row[1];
$group = $row[2];
$lock = $row[3];

$lock_text = null;
if ($lock <= time()) {
    $lock_text = 'block';
} else {
    $lock_text = 'unblock';
}

echo "UID: ${uid} (<a href='../ui/mail/send.php?to_uid=${uid}'>send mail</a>) <br/>\n";
echo "Name: ${name} <br/>\n";
echo "Group: ${group} <br/>\n";
echo "Lock: ${lock} (<a href='../ui/lock.php?uid=${uid}'>${lock_text}</a>) <br/>\n";
