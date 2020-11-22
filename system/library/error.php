<?php
require_once(dirname(__FILE__) . '/../config/index.php');

// this function is like Rust "panic!"
function panic($message) {
    global $ENCODING;

    if (!is_string($message)) {
        panic('$message is not a string!');
        exit;
    }

    // anti XSS.
    $message = htmlentities($message);

    // display error and exit.
    echo "<br/>\n";
    echo "== Panic! == <br/>\n";
    echo "=== Backtrace === <br/>\n";
    echo "<pre>\n";
    debug_print_backtrace();
    echo "</pre>\n";
    echo "=== Message === <br/>\n";
    exit("${message}");
}
