<?php
require_once(dirname(__FILE__) . '/../config/index.php');
require_once(dirname(__FILE__) . '/error.php');
require_once(dirname(__FILE__) . '/basic.php');

// database client is mysqli.
class Database {
    private $link = null;
    private $driver = null;

    private $results = null;

    function __construct() {
        $this->connect();

        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_STRICT;
        $this->driver = $driver;

        $this->results = [];
    }
    function __destruct() {
        $this->close();
    }

    // connect to database server.
    function connect() {
        global $DATABASE_IP;
        global $DATABASE_PORT;

        global $DATABASE_USER;
        global $DATABASE_PASS;
    
        global $DATABASE_NAME;
    
        if (!$this->link) {
            $new_link = new mysqli($DATABASE_IP, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME, $DATABASE_PORT);
            if ($new_link->connect_errno) {
                $errno = $new_link->connect_errno;
                $error = $new_link->connect_error;
                panic("Cannot connect to the database server! Error[Errno ${errno}]: ${error}");
                exit;
            }

            $this->link = $new_link;
        }
    }

    // create a new database query (not to execute it).
    function new_query($sql) {
        assert_eq(is_string($sql), true);

        $this->connect();

        $query = $this->link->prepare($sql);
        if (!$query) {
            $errno = $this->link->errno;
            $error = $this->link->error;
            panic("This SQL is have some syntax errors! Error[Errno ${errno}]: ${error}");
        }

        return $query;

        // at later, if you needed to insert some parem to $sql, please using $query->bind_param method.
        // and, use $this->to_query($query) to get the query result.
    }

    // execute a database query and return result.
    function to_query($query) {
        $this->connect();

        $ok = $query->execute();
        if (!$ok) {
            $errno = $query->errno;
            $error = $query->error;
            panic("failed to query the database! Error[Errno ${errno}]: ${error}");
            exit;
        }

        $result = $query->get_result();
        if (!$result) {
            $errno = $query->errno;
            if ($errno) {
                $error = $query->error;

                panic("Cannot to get query result! Error[Errno ${errno}]: ${error}");
                exit;
            }

            return null;
        }

        $this->results[] = $result;
        return $result;
    }

    // create a new query and execute it.
    function query($sql) {
        assert_eq(is_string($sql), true);

        $query = $this->new_query($sql);
        $results = $this->to_query($query);
        return $results;
    }

    // free all results.
    function free() {
        foreach ($this->results as $result) {
            $result->free();
        }

        // clear all results.
        $this->results = [];
    }

    // close database connection.
    function close() {
        $this->free();

        if ($this->link) {
            $ok = $this->link->close();
            if ($ok) $this->link = null;
            return $ok;
        }

        return true;
    }

    // to reconnect database connection.
    function reconnect() {
        while (!($this->close())) {
            // sleep 100 milliseconds.
            usleep(100 * 1000);
        }

        $this->connect();
    }
}
