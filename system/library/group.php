<?php
require_once(dirname(__FILE__) . '/../config/index.php');

require_once(dirname(__FILE__) . '/error.php');
require_once(dirname(__FILE__) . '/basic.php');
require_once(dirname(__FILE__) . '/hash.php');

abstract class Permission {
    static function get_id($name) {
        global $PERMISSIONS;

        if (!is_string($name)) {
            panic('$name is not a string!');
            exit;
        }

        $key = strtoupper($name);

        $id = $PERMISSIONS[$key];
        if (!is_integer($id)) {
            panic('this permission is not found!');
            exit;
        }

        return $id;
    }
}

class Group {
    private $name = null; // type: String
    private $permissions = null; // type: Array
    private $frozen = null; // type: Boolean

    function __construct($group_name) {
        assert_eq(is_string($group_name), true);
        $this->name = $group_name;

        $this->permissions = [];
        $this->frozen = false;
    }

    function get_name() {
        return $this->name;
    }

    function add_permission($name) {
        $this->check();

        $id = Permission::get_id($name);
        $this->permissions[$id] = true;

        return $this;
    }

    function del_permission($name) {
        $this->check();

        $id = Permission::get_id($name);
        $this->permissions[$id] = false;

        return $this;
    }

    function have_permission($name) {
        $this->freeze();

        $id = Permission::get_id($name);

        if (!array_key_exists($id, $this->permissions)) {
            return false;
        }

        if ($this->permissions[$id]) {
            return true;
        }
        return false;
    }

    // this method is like Ruby "Array.freeze"
    function freeze() {
        $this->frozen = true;
        return $this;
    }

    function check() {
        if ($this->frozen) {
            panic('cannot to modify a frozen group!');
        }
    }

    static function get($name) {
        global $GROUPS;

        assert_eq(is_string($name), true);
        assert_eq(key_exists($name, $GROUPS), true);

        $obj = $GROUPS[$name];
        return $obj->freeze();
    }
}
