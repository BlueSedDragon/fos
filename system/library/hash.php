<?php
require_once(dirname(__FILE__) . '/../config/index.php');
require_once(dirname(__FILE__) . '/basic.php');

class PBKDF2 {
    private $hash = null;
    private $algo = null;
    private $salt = null;
    private $iters = null;
    
    function __construct($password, $salt) {
        global $PBKDF2_ALGO;
        global $PBKDF2_ITERS;

        global $PBKDF2_SALT_LEN;
        global $PBKDF2_HASH_LEN;

        assert_eq(is_string($password), true);

        if (!$salt) {
            $salt = PBKDF2::new_salt();
        }

        assert_eq(is_string($salt), true);
        assert_eq(strlen($salt), $PBKDF2_SALT_LEN);

        $algo = $PBKDF2_ALGO;
        $iters = $PBKDF2_ITERS;

        $hash = hash_pbkdf2($algo, $password, $salt, $iters, $PBKDF2_HASH_LEN, true);

        $this->hash = $hash;
        $this->algo = $algo;
        $this->salt = $salt;
        $this->iters = $iters;
    }

    static function new_salt() {
        global $PBKDF2_SALT_LEN;

        $salt = random_bytes($PBKDF2_SALT_LEN);
        return $salt;
    }

    /* == Getter == */
    function get_hash() {
        return $this->hash;
    }
    function get_algo() {
        return $this->algo;
    }
    function get_salt() {
        return $this->salt;
    }
    function get_iters() {
        return $this->iters;
    }
}
