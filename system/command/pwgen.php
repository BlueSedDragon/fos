<?php
require_once(dirname(__FILE__) . '/../library/basic.php');

$chars = str_split('123456789QWERTYUPASDFGHJKLZXCVBNMqwertyuipasdfghjkzxcvbnm');

function new_password($len) {
    global $chars;

    assert_eq(is_integer($len), true);
    assert_eq($len >= 0, true);

    $password = '';
    for ($i = 0; $i < $len; ++$i) {
        $char = null;
        do {
            $char = random_bytes(1);
        } while (is_bool(array_search($char, $chars)));
        
        $password .= $char;
    }

    return $password;
}

header('Content-Type: text/plain');
echo new_password(random_int(12, 20));
