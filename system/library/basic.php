<?php
require_once(dirname(__FILE__) . '/error.php');

function hex($input) {
    assert_eq(is_string($input), true);

    $input_len = strlen($input);

    $output = [];
    for ($i = 0; $i < $input_len; ++$i) {
        $it = $input[$i];
        $byte = ord($it);

        $hex = sprintf('%02x', $byte);
        $output[] = $hex;
    }

    $output = join('', $output);
    return $output;
}

$NOW = 0;
function nonce() {
    global $NOW;

    $NOW += 1;
    return $NOW;
}

// this function is like Rust "assert_eq!"
function assert_eq($a, $b) {
    if ($a !== $b) {
        panic('Assert failed!');
        exit;
    }
}

function time_display($timestamp) {
    $none = 'N/A';

    if (!is_integer($timestamp)) {
        return $none;
    }
    if ($timestamp < 0) {
        return $none;
    }

    return date('Y-m-d', $timestamp) . ' (UTC)';
}

function time_day() {
    $now = time();

    $today = $now % 86400;
    $now -= $today;

    return $now;
}

function get($name) {
    if (!key_exists($name, $_GET)) {
        panic("missing get argument $${name}.");
        exit;
    }

    $value = $_GET[$name];
    if (strlen($value) <= 0) {
        panic("empty get argument $${name}.");
        exit;
    }

    return $value;
}

function post($name) {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        panic('Please use POST method.');
        exit;
    }

    if (!key_exists($name, $_POST)) {
        panic("missing post argument $${name}.");
        exit;
    }

    $value = $_POST[$name];
    if (strlen($value) <= 0) {
        panic("empty post argument $${name}.");
        exit;
    }

    return $value;
}

function rows_display($rows) {
    echo
"<style type='text/css'>
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #dddddd; text-align: left; padding: 8px; }
</style>\n\n
";

    if (count($rows) <= 0) {
        echo "nothing.\n";
        return;
    }

    echo "<table>\n<tr>";

    $keys = array_keys($rows[0]);
    foreach ($keys as $key) {
        echo "<th>${key}</th>";
    }
    echo "</tr>\n";

    foreach ($rows as $row) {
        echo "<tr>";
        foreach ($keys as $key) {
            $value = $row[$key];
            echo "<td>${value}</td>";
        }
        echo "</tr>\n";
    }
    echo "</table>\n<br/>";
}
