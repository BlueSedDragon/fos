<?php
require_once(dirname(__FILE__) . '/../config/index.php');

require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/html.php');

$db = new Database();

function get_processes() {
    global $db; 

    $result = $db->query('SELECT * FROM `processes` ORDER BY `pid` DESC;');
    $rows = $result->fetch_all();

    $processes = [];
    foreach ($rows as $row) {
        $process = [];

        $pid = $row[0];
        $process['pid'] = HTML::pid_link($pid);
        $process['tid'] = "<a href='../ui/new/thread.php?pid=${pid}'>new</a>";
        $process['uid'] = HTML::uid_link($row[1]);
        $process['name'] = $row[2];
        $process['memory'] = $row[3];
        $process['start'] = time_display($row[4]);

        $end = $row[5];
        if (is_integer($end)) {
            $end = time_display($end);
        } else {
            $end = time_display($end) . " (<a href='./kill.php?signal=KILL&pid=${pid}'>kill</a>)";
        }
        $process['end'] = $end;

        $processes[] = $process;
    }

    return $processes;
}

function get_threads($pid) {
    global $db;

    assert_eq(is_integer($pid), true);
    assert_eq($pid >= 0, true);

    $query = $db->new_query('SELECT * FROM `threads` WHERE `pid` = ? ORDER BY `tid` DESC;');
    $query->bind_param('d', $pid);

    $result = $db->to_query($query);
    $rows = $result->fetch_all();

    $threads = [];
    foreach ($rows as $row) {
        $thread = [];
        $tid = $row[0];
        $pid = $row[1];
        $thread['tid'] = "<a href='./memdump.php?pid=${pid}&tid=${tid}'>${tid}</a>";
        $thread['pid'] = $pid;
        $thread['uid'] = HTML::uid_link($row[2]);
        $thread['name'] = $row[3];
        $thread['start'] = time_display($row[5]);
        $thread['end'] = time_display($row[6]);

        $threads[] = $thread;
    }

    return $threads;

    var_dump($rows);

    panic('');
}

function display($things) {
    echo
"<style type='text/css'>
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
</style>\n\n
";

    if (count($things) <= 0) {
        echo "nothing.\n<br/><a href='?'>Back</a>\n";
        return;
    }

    echo "<table>\n<tr>";

    $keys = array_keys($things[0]);
    foreach ($keys as $key) {
        echo "<th>${key}</th>";
    }
    echo "</tr>\n";

    foreach ($things as $thing) {
        echo "<tr>";
        foreach ($keys as $key) {
            $value = $thing[$key];
            echo "<td>${value}</td>";
        }
        echo "</tr>\n";
    }
    echo "</table>\n<br/>";

    if (key_exists('tid', $things[0])) {
        echo "<a href='?'>Back</a>\n";
    }
}

function main() {
    $things = null;

    if (!key_exists('pid', $_GET)) {
        $things = get_processes();
    } else {
        $pid = $_GET['pid'];
        $things = get_threads((int)$pid);
    }

    display($things);
}

main();
