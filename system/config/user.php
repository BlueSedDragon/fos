<?php
// if you configure done, please set it to true.
$CONFIGURED = true;

// the name of your website.
$WEBSITE_NAME = 'website';

/* Tips: recommended use mariadb-server (apt package name) as the database server. */

// ip address of the database server.
$DATABASE_IP = '127.0.0.1';

// tcp port of the database server.
$DATABASE_PORT = 3306;

// database name.
$DATABASE_NAME = 'database1';

// the login username of the database server.
$DATABASE_USER = 'username';

// the login password of the database server.
$DATABASE_PASS = 'password';

/* Tips: you can add some new user groups. */
// for example: $GROUPS['group name'] = (new Group())->add_permission('one permission')->add_permission('two permission')->freeze();

/* the password of account root. */
$PASSWORD_ROOT = 'rootpass';
