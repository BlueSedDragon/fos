<?php
require_once(dirname(__FILE__) . '/system/config/index.php');
require_once(dirname(__FILE__) . '/system/library/session.php');
require_once(dirname(__FILE__) . '/system/library/group.php');
require_once(dirname(__FILE__) . '/system/library/basic.php');

$current_session = Session::current();

$logined = null;

$current_uid = null;
if (!$current_session) {
    $logined = false;

    $current_uid = 1;
    $current_group = Group::get('guest');
} else {
    $logined = true;

    $current_uid = $current_session->get_uid();
    $current_group = $current_session->get_group();
}

$commands = [];
{
    $commands[] = "<il>\n";
    {
        $filenames = glob('./system/command/*.php');
        foreach ($filenames as $filename) {
            $commands[] = "<li> <a href='${filename}'>${filename}</a> </li>\n";
        }
    }
    $commands[] = "</il>\n";
}
$commands = join('', $commands);
?>

<html>
    <head>
        <title>Welcome to FOS!</title>
    </head>
    <body>
        Welcome to FOS (Forum Operating System).<br/>
        uid: <a href='./system/command/id.php?uid=<?php echo $current_uid;?>'><?php echo $current_uid;?></a><br/>
        group: <?php echo $current_group->get_name();?> <?php if ($current_group->get_name() != 'guest') { echo '(<a href="./system/command/exit.php">Exit</a>)'; } else { echo '(<a href="./system/ui/login.html">Login</a> | <a href="./system/ui/signup.html">Sign up</a>)'; } ?><br/>
        <hr/>
        
        <a href='./system/ui/new/'>Create</a> | <a href='./system/command/ps.php'>Browse</a> | <a href='./system/ui/mail/'>Mail</a>

        <hr/>
        List of commands:<br/>
        <?php echo $commands;?>
    </body>
</html>
