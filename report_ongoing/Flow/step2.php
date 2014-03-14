<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/8/13
 * Time: 8:03 PM
 */

require_once("../core/AssemblaConnector.php");

$conn = new AssemblaConnector($_POST['key'], $_POST['secret'], $_POST['project']);
$users = json_decode($conn->getSpaceMembers());

?>

<!DOCTYPE html>
<html>
<head>
    <title>Flow Based projects - Indicators report - Step 2)</title>
    <meta charset="utf-8">
</head>

<body>

<h1>Specify the team members to consider</h1>

<form action="report.php" method="post">

    <input type="hidden" name="key" value="<?php echo $_POST['key']; ?>">
    <input type="hidden" name="secret" value="<?php echo $_POST['secret']; ?>">
    <input type="hidden" name="project" value="<?php echo $_POST['project']; ?>">
    <input type="hidden" name="dateFrom" value="<?php echo $_POST['dateFrom']; ?>">
    <input type="hidden" name="dateTo" value="<?php echo $_POST['dateTo']; ?>">
    <input type="hidden" name="exceptions" value="<?php echo $_POST['exceptions']; ?>">

    <input type="checkbox" name="skipUserValidation" value="" checked>Skip Users validation<br><br>

    <fieldset>
        <legend>Team Members</legend>
        <?php
        foreach ($users as $user)
        {
            echo '<input type ="checkbox" name="users[]" value="' . $user->id . '" checked>' . $user->name . '<br>';
        }
        ?>
    </fieldset>

    <INPUT type="submit" value="Next"> <INPUT type="reset">
</form>

</body>

</html>
