<?php

$pid = '';
if (key_exists('pid', $_GET)) {
    $pid = (int) $_GET['pid'];
}

$tid = '';
if (key_exists('tid', $_GET)) {
    $tid = (int) $_GET['tid'];
}

?>

<html>
    <head>
        <title>Create a new line</title>
    </head>

    <body>
        <form action='../../command/exec.php' method='POST'>
            Type: <select name='type'>
                <option value='line'>Line</option>
            </select><br/>

            PID: <input type='number' name='pid' value='<?php echo $pid;?>' required='required'/><br/>
            TID: <input type='number' name='tid' value='<?php echo $tid;?>' required='required'/><br/>

            <hr/>

            Memory: <textarea name='memory' style='width:100%;height:80%;'></textarea>

            <input type='submit' value='Create'/>
        </form>
    </body>
</html>
