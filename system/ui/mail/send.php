<?php
require_once(dirname(__FILE__) . '/../../library/session.php');

$current = Session::current();
$from_uid = (string) $current->get_uid();

$to_uid = '';
if (key_exists('to_uid', $_GET)) {
    $to_uid = (int) $_GET['to_uid'];
}

$reply_mid = null;
if (key_exists('reply_mid', $_GET)) {
    $reply_mid = (int) $_GET['reply_mid'];
}

?>

<html>
    <head>
        <title>To send a mail</title>
    </head>

    <body>
        <form action='../../command/sendmail.php' method='POST'>
            Sender UID: <?php echo $from_uid;?><br/>
            Recipient UID: <input type='number' name='to_uid' value='<?php echo $to_uid;?>' required='required'/><br/>
            Text: <br/>
            <textarea name='text' style='width:100%;height:80%;' required='required'><?php if (is_integer($reply_mid)) { echo "Reply mid-${reply_mid}:\n\n"; } ?></textarea>
            <input type='submit' value='Send'/>
        </form>
    </body>
</html>
