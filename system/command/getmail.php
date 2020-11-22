<?php
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/session.php');
require_once(dirname(__FILE__) . '/../library/basic.php');

$current = Session::current();
$current_uid = $current->get_uid();

echo "My UID: ${current_uid} <br/>\n";
echo "<hr/>\n";

$db = new Database();

$query = $db->new_query('SELECT * FROM `mails` WHERE `to_uid` = ? OR `from_uid` = ? ORDER BY `mid` DESC;');
$query->bind_param('dd', $current_uid, $current_uid);

$result = $db->to_query($query);
$rows = $result->fetch_all();

$mails = [];
foreach ($rows as $row) {
    $mail = [];

    $mail['type'] = 'N/A';

    $mid = $row[0];
    $mail['mid'] = $mid;

    $from_uid = $row[1];

    $mail['from_uid'] = "<a href='./id.php?uid=${from_uid}'>${from_uid}</a>";
    if ($from_uid != $current_uid) {
        $mail['type'] = 'recv';
        $mail['from_uid'] .= " (<a href='../ui/mail/send.php?to_uid=${from_uid}&reply_mid=${mid}'>reply</a>)";
    } else {
        $mail['type'] = 'send';
    }

    $to_uid = $row[2];
    $mail['to_uid'] = "<a href='./id.php?uid=${to_uid}'>${to_uid}</a>";

    $mail['time'] = time_display($row[3]);
    $mail['text'] = $row[4];

    $mails[] = $mail;
}

rows_display($mails);
