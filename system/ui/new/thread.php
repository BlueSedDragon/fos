<?php
$pid = '';
if (key_exists('pid', $_GET)) {
    $pid = (int) $_GET['pid'];
}
?>

<html>
    <head>
        <title>Create a new thread</title>
    </head>
    <body>
        <form action='../../command/exec.php' method='POST'>
            Type: <select name='type'>
                <option value='thread'>Thread</option>
            </select><br/>
            PID: <input type='number' name='pid' required='required' value='<?php echo $pid;?>'/><br/>
            Name: <input type='text' name='name' required='required'/><br/>

            Memory: <textarea name='memory' style='width:100%;height:80%;'></textarea>

            <input type='submit' value='Create'/>
        </form>
    </body>
</html>