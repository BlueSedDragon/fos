<?php
require_once(dirname(__FILE__) . '/../library/basic.php');
require_once(dirname(__FILE__) . '/../library/database.php');
require_once(dirname(__FILE__) . '/../library/session.php');

$current = Session::current();
$current_uid = $current->get_uid();
if ($current_uid == 1) {
    echo 'please <a href="../ui/login.html">login</a> first.';
    exit;
}
$current->write_check();

$group = $current->get_group();

$db = new Database();

$signal = get('signal');
switch ($signal) {
    case 'KILL':
        $end = time_day();

        $pid = (int) get('pid');

        $tid = null;
        if (key_exists('tid', $_GET)) {
            $tid = (int) $_GET['tid'];
        }

        $query = null;
        if (is_integer($tid)) {
            $ended = null;
            $self = null;
            {
                $q = $db->new_query('SELECT * FROM `threads` WHERE `pid` = ? AND `tid` = ? LIMIT 1;');
                $q->bind_param('dd', $pid, $tid);
                $r = $db->to_query($q);

                $rows = $r->fetch_all();

                if (count($rows) <= 0) {
                    echo 'this thread is not found.';
                    exit;
                }

                $row = $rows[0];

                $t_uid = $row[2];
                if ($t_uid == $current_uid) {
                    $self = true;
                } else {
                    $self = false;
                }

                $t_end = $row[6];
                if (is_integer($t_end)) {
                    $ended = true;
                } else {
                    $ended = false;
                }
            }

            if ($ended) {
                echo 'this thread is ended.';
                exit;
            }

            $Cannot = 'Cannot to kill this thread: Permission denied.';

            if (!$group->have_permission('KILL_ANY_THREAD')) {
                if ($self) {
                    if (!$group->have_permission('KILL_MY_THREAD')) {
                        echo $Cannot;
                        exit;
                    }
                } else {
                    echo $Cannot;
                    exit;
                }
            }

            $query = $db->new_query('UPDATE `threads` SET `end` = ? WHERE `pid` = ? AND `tid` = ?;');
            $query->bind_param('ddd', $end, $pid, $tid);
        } else {
            $ended = null;
            $self = null;
            {
                $q = $db->new_query('SELECT * FROM `processes` WHERE `pid` = ? LIMIT 1;');
                $q->bind_param('d', $pid);
                $r = $db->to_query($q);

                $rows = $r->fetch_all();

                if (count($rows) <= 0) {
                    echo 'this process is not found.';
                    exit;
                }

                $row = $rows[0];

                $p_uid = $row[1];
                if ($p_uid == $current_uid) {
                    $self = true;
                } else {
                    $self = false;
                }

                $p_end = $row[5];
                if (is_integer($p_end)) {
                    $ended = true;
                } else {
                    $ended = false;
                }
            }

            if ($ended) {
                echo 'this process is ended.';
                exit;
            }

            $Cannot = 'Cannot to kill this process: Permission denied.';
            if (!$group->have_permission('KILL_ANY_PROCESS')) {
                if ($self) {
                    if (!$group->have_permission('KILL_MY_PROCESS')) {
                        echo $Cannot;
                        exit;
                    }
                } else {
                    echo $Cannot;
                    exit;
                }
            }

            $query = $db->new_query('UPDATE `processes` SET `end` = ? WHERE `pid` = ?;');
            $query->bind_param('dd', $end, $pid);
        }

        $db->to_query($query);
        echo 'OK';
        break;
    
    case 'LOCK':
        $uid = (int) post('uid');

        $self = null;
        if ($uid == $current_uid) {
            $self = true;
        } else {
            $self = false;
        }

        {
            $q = $db->new_query('SELECT * FROM `users` WHERE `uid` = ? LIMIT 1;');
            $q->bind_param('d', $uid);

            $r = $db->to_query($q);

            $rows = $r->fetch_all();
            if (count($rows) <= 0) {
                echo 'this account is not found.';
                exit;
            }
        }

        $Cannot = 'Cannot to lock this account: Permission denied.';
        if (!$group->have_permission('LOCK_ANY_ACCOUNT')) {
            if ($self) {
                if (!$group->have_permission('LOCK_MY_ACCOUNT')) {
                    echo $Cannot;
                    exit;
                }
            } else {
                echo $Cannot;
                exit;
            }
        }

        // lock time: posix timestamp.
        $timestamp = (int) post('timestamp');
        assert_eq(is_integer($timestamp), true);
        assert_eq($timestamp >= 0, true);

        {
            $query = $db->new_query('UPDATE `users` SET `lock` = ? WHERE `uid` = ?;');

            $lock = $timestamp;
            $query->bind_param('dd', $lock, $uid);

            $db->to_query($query);
        }

        {
            $query = $db->new_query('UPDATE `sessions` SET `expire` = 0 WHERE `uid` = ?;');
            $query->bind_param('d', $uid);
            $db->to_query($query);
        }

        echo 'OK';
        break;

    case 'PING':
        echo 'deving...';
        exit;

        $uid = get('uid');
        $message = post('message');
        break;
}
