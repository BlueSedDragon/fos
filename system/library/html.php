<?php
require_once(dirname(__FILE__) . '/basic.php');

abstract class HTML {
    static function link($href, $title=null) {
        assert_eq(is_string($href), true);

        if ((!$title) && $title != 0) {
            $title = $href;
        }

        assert_eq(is_string($title), true);

        $href = htmlentities($href);
        $href = htmlentities($href);

        return "<a href='${href}' title='${title}'>${title}</a>";
    }

    static function pid_link($pid) {
        assert_eq(is_integer($pid), true);
        assert_eq($pid >= 0, true);
    
        $href = "./ps.php?pid=${pid}";
        $title = (string) $pid;
    
        return HTML::link($href, $title);
    }

    static function uid_link($uid) {
        assert_eq(is_integer($uid), true);
        assert_eq($uid >= 0, true);
    
        $href = "./id.php?uid=${uid}";
        $title = (string) $uid;
        
        return HTML::link($href, $title);
    }
}
