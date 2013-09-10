<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/8/13
 * Time: 8:03 PM
 */

require_once("../core/assembla.php");

$conn = new AssemblaConnector($_POST['key'], $_POST['secret'], $_POST['project']);

$statuses = json_decode($conn->getSpaceStatuses());
$milestones = json_decode($conn->getMilestones(0,1000));

?>

<!DOCTYPE html>
<html>
<head>
    <title>Milestone Based projects - Indicators report (step 2)</title>
</head>

<body>
<h1>Specify report details</h1>
<form action="report.php" method="post">

    <input type="hidden" name="key" value="<?php echo $_POST['key']; ?>">
    <input type="hidden" name="secret" value="<?php echo $_POST['secret']; ?>">
    <input type="hidden" name="project" value="<?php echo $_POST['project']; ?>">

    <fieldset>
        <legend>Specify the "delivered" statuses</legend>
        <?php
            foreach($statuses as $status){
                echo '<input type ="checkbox" name="status[]" value="' .$status->name . '">' . $status->name . '<br>';
            }
        ?>
    </fieldset>

    <fieldset>
        <legend>Exceptions</legend>
        Specify the tickets that should be ignored, if any (separate them with commas). <br>
        <textarea name="exceptions" rows="4" cols="50"></textarea>
    </fieldset>

    Select the milestone:
    <select name="milestone">
        <?php
            foreach($milestones as $milestone){
                echo '<option value="' . $milestone->id . '">' . $milestone->title . '</option>';
            }
        ?>
    </select>

    <INPUT type="submit" value="Next"> <INPUT type="reset">
</form>

</body>

</html>
