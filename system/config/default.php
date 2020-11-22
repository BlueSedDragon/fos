<?php
// *** PLEASE DO NOT MODIFY THIS FILE! ***
// Please see "./user.php", You can edit it, because it is a user config file.

require_once(dirname(__FILE__) . '/../library/basic.php');

$DEBUG = true;

$VERSION = 2;
$ENCODING = 'utf-8';
$README_FILE = dirname(__FILE__) . '/../../README.wiki';

$COOKIE_TK = 'tk';

$PASSWORD_ROOT = null;

$PBKDF2_ALGO = 'sha3-512';

$PBKDF2_HASH_LEN = 64;
$PBKDF2_ITERS = 10000;

$PBKDF2_SALT_LEN = $PBKDF2_HASH_LEN * 2;
$PBKDF2_SALT_NONE = str_repeat("\x00", $PBKDF2_SALT_LEN);

require_once(dirname(__FILE__) . '/../library/group.php');

$PERMISSIONS = [
    // Authorized to dump the memory of any process.
    'DUMP_ANY_PROCESS' => nonce(),

    // Authorized to dump the memory of any thread.
    'DUMP_ANY_THREAD' => nonce(),

    // Authorized to create a new process.
    'CREATE_PROCESS' => nonce(),

    // Authorized to create a new thread.
    'CREATE_THREAD' => nonce(),

    // Authorized to kill any process.
    'KILL_ANY_PROCESS' => nonce(),

    // Authorized to kill any thread.
    'KILL_ANY_THREAD' => nonce(),

    // Authorized to kill the process of created by myself.
    'KILL_MY_PROCESS' => nonce(),

    // Authorized to kill the thread of created by myself.
    'KILL_MY_THREAD' => nonce(),

    // Authorized to lock any account.
    'LOCK_ANY_ACCOUNT' => nonce(),

    // Authorized to lock the account of myself.
    'LOCK_MY_ACCOUNT' => nonce()
];

// list of user groups.
$GROUPS = [];

// add "root" group for super users.
$GROUPS['root'] = (new Group('root'))
    ->add_permission('DUMP_ANY_PROCESS')
    ->add_permission('CREATE_PROCESS')
    ->add_permission('KILL_ANY_PROCESS')

    ->add_permission('DUMP_ANY_THREAD')
    ->add_permission('CREATE_THREAD')
    ->add_permission('KILL_ANY_THREAD')

    ->add_permission('LOCK_ANY_ACCOUNT')
    ->add_permission('LOCK_MY_ACCOUNT')

    ->freeze();

// add "user" group for general users.
$GROUPS['user'] = (new Group('user'))
    ->add_permission('DUMP_ANY_PROCESS')

    ->add_permission('DUMP_ANY_THREAD')
    ->add_permission('CREATE_THREAD')
    ->add_permission('KILL_MY_THREAD')

    ->add_permission('LOCK_MY_ACCOUNT')

    ->freeze();

// add "guest" group for guest users.
$GROUPS['guest'] = (new Group('guest'))
    ->add_permission('DUMP_ANY_PROCESS')
    ->add_permission('DUMP_ANY_THREAD')
    ->freeze();

/* ================================================= */

if ($DEBUG) {
    // display all errors.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
