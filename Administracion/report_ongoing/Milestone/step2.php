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

$statuses = json_decode($conn->getSpaceStatuses());
$users = json_decode($conn->getSpaceMembers());
$milestones = json_decode($conn->getMilestones(0, 100));

?>

    <form action="report.php" method="post" role="form">
        <input type="hidden" name="key" value="<?php echo $_POST['key']; ?>">
        <input type="hidden" name="secret" value="<?php echo $_POST['secret']; ?>">
        <input type="hidden" name="project" value="<?php echo $_POST['project']; ?>">

        <div class="row">
            <fieldset class="col-md-4">
                <legend>Milestone</legend>

                <div class="form-group">
                    <select class="form-control" id="milestone" name="milestone">
                        <?php foreach ($milestones as $milestone) : ?>
                            <option value="<?php echo $milestone->id; ?>"><?php echo $milestone->title; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

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
                <legend>Specify the "delivered" statuses</legend>

                <div class="form-group">
                    <?php foreach ($statuses as $status) : ?>
                        <div class="checkbox">
                            <label><input type="checkbox" name="status[]" value="<?php echo $status->name; ?>"><?php echo $status->name; ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <fieldset class="col-md-4">
                <legend>Exceptions</legend>

                <div class="form-group">
                    <label for="exceptions">Specify the tickets that should be ignored, if any (separate them with commas)</label>
                    <textarea name="exceptions" id="exceptions" class="form-control" rows="4" cols="50"></textarea>
                </div>
            </fieldset>
        </div>

        <button type="submit" class="btn btn-lg btn-primary">Next</button>
    </form>

<?php include '../footer.php'; ?>