<?php
require_once(dirname(__FILE__) . '/../config/index.php');
require_once(dirname(__FILE__) . '/basic.php');
require_once(dirname(__FILE__) . '/database.php');
require_once(dirname(__FILE__) . '/group.php');
require_once(dirname(__FILE__) . '/error.php');

// each session will timeout at ten minutes later.
$SessionTimeout = 600;

// each user is allowed one write per ten seconds only.
$WriteLimit = 10;

class Session {
    private $tk = null;

    private $uid = null;
    private $expire = null;

    private $group = null;

    function __construct($uid) {
        global $SessionTimeout;

        $this->tk = null;

        assert_eq(is_integer($uid), true);
        assert_eq($uid >= 0, true);
        $this->uid = $uid;

        $this->expire = time() + $SessionTimeout;
    }

    // must to call this function at each write operation.
    function write_check() {
        global $WriteLimit;

        $group = $this->get_group();
        $group_name = $group->get_name();

        // root group is unlimited.
        if ($group_name == 'root') {
            return;
        }

        /*
        // guest group is not allowed.
        if ($group_name == 'guest') {
            panic('"guest" group is not allowed to write the database.');
            exit;
        }
        */

        // maybe is user group or other.

        $db = new Database();
        $query = $db->new_query('SELECT * FROM `users` WHERE `uid` = ? LIMIT 1;');

        $uid = $this->get_uid();
        $query->bind_param('d', $uid);

        $result = $db->to_query($query);
        $rows = $result->fetch_all();

        if (count($rows) <= 0) {
            panic('user is not found!');
            exit;
        }

        $row = $rows[0];

        $last_write = $row[6];

        $now = time();
        if (($last_write + $WriteLimit) > $now) {
            panic('this group has a write frequency limit: write once every ten seconds, please retry at ten seconds later.');
            exit;
        }

        $query = $db->new_query('UPDATE `users` SET `last_write` = ? WHERE `uid` = ?;');
        $query->bind_param('dd', $now, $uid);
        $db->to_query($query);

        return;
    }

    function get_group() {
        if (!$this->group) {
            $db = new Database();
            $query = $db->new_query('SELECT * FROM `users` WHERE `uid` = ? LIMIT 1;');

            $uid = $this->get_uid();
            $query->bind_param('d', $uid);

            $result = $db->to_query($query);
            $rows = $result->fetch_all();

            if (count($rows) <= 0) {
                panic('Cannot using uid to find a user!');
                exit;
            }

            $row = $rows[0];

            $group = $row[2];

            $group_obj = Group::get($group);
            $this->group = $group_obj;
        }

        return $this->group;
    }

    function get_tk() {
        if (!$this->tk) {
            $uid = $this->uid;
            $expire = $this->expire;

            $db = new Database();
            $db->connect();

            $new_tk = Session::new_tk();

            $query = $db->new_query('UPDATE `sessions` SET `uid` = ?, `expire` = ? WHERE `tk` = ?;');
            $query->bind_param('dds', $uid, $expire, $new_tk);
            $db->to_query($query);

            $this->tk = $new_tk;
        }

        return $this->tk;
    }
    function get_uid() {
        return $this->uid;
    }
    function get_expire() {
        return $this->expire;
    }

    // generate a new token.
    static function new_tk() {
        $db = new Database();

        // anti-repeated.
        $tk = null;
        while (1) {
            $tk = Session::_new_tk();

            $db->reconnect();

            $results = $db->query('SELECT * FROM `sessions` WHERE `tk` = "$0";', [ $tk ]);
            $rows = $results->fetch_all();

            $results_len = count($rows);
            if ($results_len == 0) {
                // $tk is not repeated.
                break;
            }

            // sleep 100 milliseconds.
            usleep(100 * 1000);
        }

        $db->reconnect();

        $query = $db->new_query('INSERT INTO `sessions` (`tk`, `uid`, `expire`) VALUES (?, -1, -1);');
        $query->bind_param('s', $tk);
        $db->to_query($query);

        $db->close();

        return $tk;
    }

    // please do not use this function, because the $tk repeated is possible.
    static function _new_tk() {
        $tks = [];
        $tks[] = 'tk';
        $tks[] = (string) time();
        $tks[] = (string) hex(random_bytes(32));

        $tk = join('_', $tks);
        return $tk;
    }

    static function current() {
        $session = Session::_current();
        if (!$session) {
            $session = new Session(1);
            $session->tk = 'tk_guest';
            $session->expire = time() * 10000;
        }

        return $session;
    }

    // try get a current session from cookie. if not found, return null.
    static function _current() {
        global $COOKIE_TK;
        global $SessionTimeout;

        // $tk not found.
        if (!key_exists($COOKIE_TK, $_COOKIE)) return null;

        $tk = $_COOKIE[$COOKIE_TK];

        // $tk is empty.
        if (strlen($tk) <= 0) return null;
        
        $db = new Database();

        // get a row.
        $row = null;
        {
            $query = $db->new_query('SELECT * FROM `sessions` WHERE `tk` = ? LIMIT 1;');
            $query->bind_param('s', $tk);

            $result = $db->to_query($query);
            $rows = $result->fetch_all();

            // $tk is not found.
            if (count($rows) <= 0) return null;

            $row = $rows[0];
        }

        $tk = $row[0];
        $uid = $row[1];
        $expire = $row[2];

        // this session is expired.
        if (time() >= $expire) return null;

        // keep expire time.
        {
            $query = $db->new_query('UPDATE `sessions` SET `expire` = ? WHERE `tk` = ? AND `uid` = ?;');

            $new_expire = time() + $SessionTimeout;
            $query->bind_param('dsd', $new_expire, $tk, $uid);

            $db->to_query($query);
        }

        $session = new Session($uid);
        $session->tk = $tk;
        $session->expire = $expire;
        return $session;
    }
}
