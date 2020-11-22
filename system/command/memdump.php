<?php
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/error.php');
require_once(dirname(__FILE__) . '/../library/html.php');

$pid = get('pid');
$pid = (int)$pid;

$tid = get('tid');
$tid = (int)$tid;

$db = new Database();

$query = $db->new_query('SELECT * from `threads` WHERE `pid` = ? AND `tid` = ? LIMIT 1;');
$query->bind_param('dd', $pid, $tid);

$result = $db->to_query($query);
$rows = $result->fetch_all();

if (count($rows) <= 0) {
    panic('Thread not found!');
    exit;
}

$row = $rows[0];

$tid = $row[0];
$pid = $row[1];
$uid = $row[2];
$name = $row[3];
$memory = $row[4];
$start = time_display($row[5]);

$ended = null;

$end = $row[6];
{
    if (is_integer($end)) {
        $ended = true;
    } else {
        $ended = false;
    }
}
$end = time_display($end);

$lines = [];
{
    $query = $db->new_query('SELECT * FROM `lines` WHERE `pid` = ? AND `tid` = ? ORDER BY `lid` DESC;');
    $query->bind_param('dd', $pid, $tid);

    $result = $db->to_query($query);
    $rows = $result->fetch_all();

    foreach ($rows as $row) {
        $line = [];

        $line['lid'] = $row[0];
        $line['tid'] = $row[1];
        $line['pid'] = $row[2];
        $line['uid'] = HTML::uid_link($row[3]);
        $line['memory'] = $row[4];
        $line['time'] = time_display($row[5]);

        $lines[] = $line;
    }
}

$lines_display = '';
{
    $lines_display .= "
<style type='text/css'>
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
</style>\n";

    $lines_display .= "<table>\n";

    $lines_display .= "<tr> <th>lid</th> <th>uid</th> <th>time</th> <th>memory</th> </tr>\n";
    foreach ($lines as $line) {
        $l_lid = $line['lid'];
        $l_uid = $line['uid'];
        $l_memory = $line['memory'];
        $l_time = $line['time'];

        $lines_display .= "<tr> <td>${l_lid}</td> <td>${l_uid}</td> <td>${l_time}</td> <td>${l_memory}</td> </tr>\n";
    }

    $lines_display .= '</table>';
}

$t_kill = '';
if (!$ended) {
    $t_kill = "(<a href='./kill.php?signal=KILL&pid=${pid}&tid=${tid}'>kill</a>)";
}

?>

<html>
    <head>
        <title>Memory dump</title>
    </head>
    <body>
        TID: <?php echo $tid;?> <?php echo $t_kill;?> <br/>
        PID: <?php echo HTML::pid_link($pid);?> <br/>
        UID: <?php echo HTML::uid_link($uid);?> <br/>

        <br/>
        Name: <?php echo $name;?> <br/>
        <br/>

        Start: <?php echo $start;?> <br/>
        End: <?php echo $end;?> <br/>
        <br/>
        Memory: <textarea style='width:100%;height:50%;' readonly='readonly'><?php echo $memory;?></textarea><br/>

        <hr/>
        Lines (<a href='../ui/new/line.php?pid=<?php echo $pid;?>&tid=<?php echo $tid;?>'>new</a>):
        <div>
            <?php echo $lines_display;?>


        </div>
    </body>
</html>
