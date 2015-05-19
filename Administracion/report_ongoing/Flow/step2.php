<?php include '../header.php'; ?>
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

<form action="report.php" method="post">

    <input type="hidden" name="key" value="<?php echo $_POST['key']; ?>">
    <input type="hidden" name="secret" value="<?php echo $_POST['secret']; ?>">
    <input type="hidden" name="project" value="<?php echo $_POST['project']; ?>">
    <input type="hidden" name="dateFrom" value="<?php echo $_POST['dateFrom']; ?>">
    <input type="hidden" name="dateTo" value="<?php echo $_POST['dateTo']; ?>">
    <input type="hidden" name="exceptions" value="<?php echo $_POST['exceptions']; ?>">

<!--    <input type="checkbox" name="skipUserValidation" value="" checked>Skip Users validation<br><br>-->
<!---->
<!--    <fieldset>-->
<!--        <legend>Team Members</legend>-->
<!--        --><?php
//        foreach ($users as $user)
//        {
//            echo '<input type ="checkbox" name="users[]" value="' . $user->id . '">' . $user->name . '<br>';
//        }
//        ?>
<!--    </fieldset>-->
<!---->
<!--    <fieldset>-->
<!--        <legend>Plan Levels to consider</legend>-->
<!--        <input type="checkbox"  name="plan[]" value="0">No Plan Level<br>-->
<!--        <input type="checkbox"  name="plan[]" value="1">Subtask<br>-->
<!--        <input type="checkbox" checked name="plan[]" value="2">Story<br>-->
<!--        <input type="checkbox"  name="plan[]" value="3">Epic<br>-->
<!--    </fieldset>-->
<!---->
<!--    <fieldset>-->
<!--        <legend>Tags</legend>-->
<!--        <input type="checkbox"  name="filterByTag" value="1">Filter by Tags<br>-->
<!--        Tags to use: <input type="text" name="tags">-->
<!--    </fieldset>-->


    <div class="row">
        <fieldset class="col-md-4">
            <legend>Team members to consider</legend>

            <div class="form-group">
                <div class="checkbox">
                    <label><input type="checkbox" name="skipUserValidation" value="">Skip Users validation</label>
                </div>
            </div>

            <div class="form-group">
                <?php foreach ($users as $user) : ?>
                    <div class="checkbox">
                        <label><input type="checkbox" name="users[]" value="<?php echo $user->id; ?>"><?php echo $user->name; ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <fieldset class="col-md-4">
            <legend>Plan Levels to consider</legend>

            <div class="form-group">
                <div class="checkbox">
                    <label><input type="checkbox" name="plan[]" value="0">No Plan Level</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" name="plan[]" value="1">Subtask</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" name="plan[]" value="2" checked>Story</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" name="plan[]" value="3">Epic</label>
                </div>
            </div>
        </fieldset>

        <fieldset class="col-md-4">
            <legend>Tags</legend>

            <div class="checkbox">
                <label><input type="checkbox" name="filterByTag" value="1">Filter by Tags</label>
            </div>

            <div class="form-group">
                <label for="tags">Tags to use</label>
                <input type="text" id="tags" name="tags" class="form-control" value="" placeholder="Tags">
            </div>
        </fieldset>
    </div>

<!--    <INPUT type="submit" value="Next">-->
    <button type="submit" class="btn btn-lg btn-primary">Next</button>
</form>
<?php include '../footer.php'; ?>
