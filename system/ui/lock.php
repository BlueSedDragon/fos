<?php
require_once(dirname(__FILE__) . '/../library/basic.php');

$uid = (int) get('uid');
?>

<html>
    <head>
        <title>Lock a account</title>
    </head>

    <body>
        Tips 1: if you want to unlock this account, please set the expire timestamp to 0. <br/>
        Tips 2: The timestamp is POSIX timestamp. <br/>
        <hr/>
        <form action='../command/kill.php?signal=LOCK' method='POST'>
            UID: <input type='number' name='uid' value='<?php echo $uid;?>' required='required'/><br/>
            Expire Timestamp: <input type='number' name='timestamp' required='required' value='<?php echo time();?>'/><br/>
            <input type='submit' value='Lock'/>
        </form>
    </body>
</html>
