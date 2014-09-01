<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/8/13
 * Time: 8:03 PM
 */

require_once("../core/AssemblaConnector.php");

$conn = new AssemblaConnector($_POST['key'], $_POST['secret'], $_POST['project']);

$statuses = json_decode($conn->getSpaceStatuses());
$users = json_decode($conn->getSpaceMembers());
$milestones = json_decode($conn->getMilestones(0, 100));

?>

<!DOCTYPE html>
<html>
<head>
    <title>Milestone Based projects - Indicators report (step 2)</title>
    <meta charset="utf-8">
</head>

<body>


<h1>Specify report details</h1>

<form action="report.php" method="post">

    <input type="hidden" name="key" value="<?php echo $_POST['key']; ?>">
    <input type="hidden" name="secret" value="<?php echo $_POST['secret']; ?>">
    <input type="hidden" name="project" value="<?php echo $_POST['project']; ?>">

    Select the milestone:
    <select name="milestone">
        <?php
        foreach ($milestones as $milestone) {
            echo '<option value="' . $milestone->id . '">' . $milestone->title . '</option>';
        }
        ?>
    </select>

    <fieldset>
        <legend>Plan Levels to consider</legend>
        <input type="checkbox"  name="plan[]" value="0">No Plan Level<br>
        <input type="checkbox"  name="plan[]" value="1">Subtask<br>
        <input type="checkbox" checked name="plan[]" value="2">Story<br>
        <input type="checkbox"  name="plan[]" value="3">Epic<br>
    </fieldset>

    <fieldset>
        <legend>Specify the "delivered" statuses</legend>
        <?php
        foreach ($statuses as $status) {
            echo '<input type ="checkbox" name="status[]" value="' . $status->name . '">' . $status->name . '<br>';
        }
        ?>
    </fieldset>

    <fieldset>
        <legend>Exceptions</legend>
        Specify the tickets that should be ignored, if any (separate them with commas). <br>
        <textarea name="exceptions" rows="4" cols="50"></textarea>
    </fieldset>


    <INPUT type="submit" value="Next"> <INPUT type="reset">
</form>

</body>

</html>
