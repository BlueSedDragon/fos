<?php
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/session.php');

$session = Session::current();
if (!$session) {
    echo 'please <a href="../ui/login.html">login</a> first.';
    exit;
}

$session->write_check();
$db = new Database();

$group = $session->get_group();

function gen_pid() {
    global $db;

    $result = $db->query('SELECT * FROM `processes` ORDER BY `pid` DESC LIMIT 1;');

    $data = $result->fetch_all();
    $len = count($data);

    $now = -1;
    if ($len > 0) {
        $row = $data[0];
        $now = $row[0];
    }

    $next = $now + 1;
    return $next;
}

function gen_tid($pid) {
    global $db;

    assert_eq(is_integer($pid), true);
    assert_eq($pid >= 0, true);

    $query = $db->new_query('SELECT * FROM `threads` WHERE `pid` = ? ORDER BY `tid` DESC LIMIT 1;');
    $query->bind_param('d', $pid);

    $result = $db->to_query($query);

    $data = $result->fetch_all();
    $len = count($data);

    $now = -1;
    if ($len > 0) {
        $row = $data[0];
        $now = $row[0];
    }

    $next = $now + 1;
    return $next;
}

function gen_lid($pid, $tid) {
    global $db;

    assert_eq(is_integer($pid), true);
    assert_eq($pid >= 0, true);

    assert_eq(is_integer($tid), true);
    assert_eq($tid >= 0, true);

    $query = $db->new_query('SELECT * FROM `lines` WHERE `pid` = ? AND `tid` = ? ORDER BY `lid` DESC LIMIT 1;');
    $query->bind_param('dd', $pid, $tid);

    $result = $db->to_query($query);

    $data = $result->fetch_all();
    $len = count($data);

    $now = -1;
    if ($len > 0) {
        $row = $data[0];
        $now = $row[0];
    }

    $next = $now + 1;
    return $next;
}

$type = post('type');

$memory = htmlentities(post('memory'));

$start = time_day();
$end = null;

switch ($type) {
    case 'process':
        if (!$group->have_permission('CREATE_PROCESS')) {
            echo 'Cannot to create a new process: Permission denied.';
            exit;
        }

        $name = htmlentities(post('name'));

        $pid = gen_pid();
        $uid = $session->get_uid();

        $query = $db->new_query('INSERT INTO `processes` (`pid`, `uid`, `name`, `memory`, `start`, `end`) VALUES (?, ?, ?, ?, ?, ?);');
        $query->bind_param('ddssdd', $pid, $uid, $name, $memory, $start, $end);

        $result = $db->to_query($query);
        echo "success to create a new process. <br/>\n";
        echo "pid: ${pid} <br/>\n";
        echo "uid: ${uid} <br/>\n";
        break;

    case 'thread':
        if (!$group->have_permission('CREATE_THREAD')) {
            echo 'Cannot to create a new thread: Permission denied.';
            exit;
        }

        $name = htmlentities(post('name'));

        $pid = (int) post('pid');
        $tid = gen_tid($pid);
        $uid = $session->get_uid();

        $query = $db->new_query('INSERT INTO `threads` (`tid`, `pid`, `uid`, `name`, `memory`, `start`, `end`) VALUES (?, ?, ?, ?, ?, ?, ?);');
        $query->bind_param('dddssdd', $tid, $pid, $uid, $name, $memory, $start, $end);

        $result = $db->to_query($query);
        echo "success to create a new thread. <br/>\n";
        echo "tid: ${tid} <br/>\n";
        echo "uid: ${uid} <br/>\n";
        echo "<hr/><a href='./ps.php?pid=${pid}'>Back</a>";
        break;
    
    case 'line':
        $pid = (int) post('pid');
        $tid = (int) post('tid');
        $lid = gen_lid($pid, $tid);
        $uid = $session->get_uid();
        $time = $start;

        {
            $memory = str_replace("\r\n", "<br/>", $memory);
            $memory = str_replace("\r", "<br/>", $memory);
            $memory = str_replace("\n", "<br/>", $memory);

            $memory = str_replace(' ', '&nbsp;', $memory);
        }

        $query = $db->new_query('INSERT INTO `lines` (`lid`, `tid`, `pid`, `uid`, `memory`, `time`) VALUES (?, ?, ?, ?, ?, ?);');
        $query->bind_param('ddddsd', $lid, $tid, $pid, $uid, $memory, $time);

        $db->to_query($query);
        echo "success to create a new line. <br/>\n";
        echo "lid: ${lid} <br/>\n";
        echo "uid: ${uid} <br/>\n";
        echo "<hr/><a href='./memdump.php?pid=${pid}&tid=${tid}'>Back</a>";
        break;

    default:
        echo 'invalid $type.';
        exit;
}

echo "<hr/>\n";
echo "<a href='./ps.php'>ps</a>\n";
