<?php
require_once(dirname(__FILE__) . '/config/index.php');

require_once(dirname(__FILE__) . '/library/database.php');
require_once(dirname(__FILE__) . '/library/error.php');
require_once(dirname(__FILE__) . '/library/basic.php');
require_once(dirname(__FILE__) . '/library/adduser.php');

// connect to database server.
$db = new Database();
$db->connect();

// the table name of the install lock. using nonce to anti-repeated. THIS NONCE IS SHOULD NOT USED AT OTHER PROGRAM.
$install_lock = '__install_lock__nonce_be7618c3a6608451b501992d525a0148';

// get all tables.
$tables = [];
{
    $result = $db->query('SHOW TABLES;');
    $rows = $result->fetch_all();

    foreach ($rows as $row) {
        $table = $row[0];
        $tables[] = $table;
    }
    unset($rows, $row, $table);

    unset($result);
}

// get the status of install lock.
$install_locked = null;
{
    $install_locked = false;
    foreach ($tables as $table) {
        assert_eq(is_string($table), true);
        if ($table == $install_lock) {
            $install_locked = true;
            break;
        }
    }
}

// check lock status.
if ($install_locked) {
    echo "FOS has been installed. if you want to reinstall, please remove table \"${install_lock}\" from the database.\n";
    exit;
}

// check install environment.
if (count($tables) > 0) {
    echo "Unable to install FOS: This database has some tables, so this installation environment is not acceptable. Please using an empty database!\n";
    exit;
}

/* == INSTALL START == */
/*
uid is user id.

pid is process id.
tid is thread id.
lid is line id.
*/

echo "FOS is installing... <br/>\n";

// create table "processes".
$db->query('CREATE TABLE `processes` (
    `pid` BIGINT NOT NULL, -- the pid of this process.
    `uid` BIGINT NOT NULL, -- the uid of this process.

    `name` TINYTEXT NOT NULL, -- the name of this process.
    `memory` LONGTEXT NOT NULL, -- the memory of this process.

    `start` BIGINT NOT NULL, -- the start time (posix timestamp) of this process.
    `end` BIGINT -- the end time (posix timestamp) of this process. if this process is not ended, set it to NULL.
);');
echo "successfully to create table `processes`. <br/>\n";

// create table "threads".
$db->query('CREATE TABLE `threads` (
    `tid` BIGINT NOT NULL, -- the tid of this thread.
    `pid` BIGINT NOT NULL, -- the pid of this thread.
    `uid` BIGINT NOT NULL, -- the uid of this thread.

    `name` TINYTEXT NOT NULL, -- the name of this thread.
    `memory` LONGTEXT NOT NULL, -- the memory of this thread.

    `start` BIGINT NOT NULL, -- the start time (posix timestamp) of this thread.
    `end` BIGINT -- the end time (posix timestamp) of this thread. if this thread is not ended, set it to NULL.
);');
echo "successfully to create table `threads`. <br/>\n";

// create table "lines".
$db->query('CREATE TABLE `lines` (
    `lid` BIGINT NOT NULL, -- the lid of this line.
    `tid` BIGINT NOT NULL, -- the tid of this line.
    `pid` BIGINT NOT NULL, -- the pid of this line.
    `uid` BIGINT NOT NULL, -- the uid of this line.

    `memory` LONGTEXT NOT NULL, -- the memory of this line.

    `time` BIGINT NOT NULL -- the add time (posix timestamp) of this line.
);');
echo "successfuly to create table `lines`. <br/>\n";

// create table "users".
$db->query("CREATE TABLE `users` (
    `uid` BIGINT NOT NULL, -- the uid of this user.
    `name` TEXT NOT NULL, -- the name of this user.
    `group` TEXT NOT NULL, -- the group of this user.

    `lock` BIGINT NOT NULL, -- if this user is locked, set to the expire time (posix timestamp). if this user is not locked, set to 0.

    `password_pbkdf2` BINARY(${PBKDF2_HASH_LEN}) NOT NULL, -- the pbkdf2 hash of user password.
    `password_pbkdf2_salt` BINARY(${PBKDF2_SALT_LEN}) NOT NULL, -- the hash salt of user password.

    `last_write` BIGINT NOT NULL -- the last write database time (posix timestamp) of this user.
);");
echo "successfully to create table `users`. <br/>\n";

// create user "root".
add_root('root', $PASSWORD_ROOT);
// create user "guest".
add_guest('guest');

// create table "sessions".
$db->query('CREATE TABLE `sessions` (
    `tk` TEXT NOT NULL, -- the token of this session.
    `uid` BIGINT NOT NULL, -- the uid of this session.
    `expire` BIGINT NOT NULL -- the expire time (posix timestamp) of this session.
);');
echo "successfully to create table `sessions`. <br/>\n";

// create table "mails".
$db->query('CREATE TABLE `mails` (
    `mid` BIGINT NOT NULL, -- the mid of this mail.

    `from_uid` BIGINT NOT NULL, -- the sender uid of this mail.
    `to_uid` BIGINT NOT NULL, -- the receiver uid of this mail.

    `time` BIGINT NOT NULL, -- the send time (posix timestamp) of this mail.
    `text` LONGTEXT NOT NULL -- the text of this mail.
);');
echo "successfully to create table `mails`. <br/>\n";

/* == INSTALL END == */

// print done information.
echo "FOS is installed. <br/>\n";

// create the install lock.
$db->query("CREATE TABLE `${install_lock}` (
    `this_is_the_install_lock_of_FOS` INT
);");
