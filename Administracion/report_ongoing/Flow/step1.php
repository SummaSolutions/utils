<?php include '../header.php'; ?>
<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/8/13
 * Time: 7:53 PM
 */

date_default_timezone_set('America/Argentina/Buenos_Aires');

$first = date('Y-m-01');
$lastDay = date('t');
$last = date("Y-m-$lastDay");

?>


<form action="step2.php" method="post">

<!--    <fieldset>-->
<!--        <legend>Specify your Assembla credentials</legend>-->
<!--        Assembla Key: <input type="text" name="key" value="cfbc848c5e2816ac3381" size="60"><br>-->
<!--        Assembla Secret: <input type="text" name="secret" value="8f21a57b9b86dedeafdc22f34d90d5dff93130f4"-->
<!--                                size="60"><br>-->
<!--        Assembla Project: <input type="text" name="project" value="" size="60"><br><br>-->
<!--    </fieldset>-->

    <fieldset>
        <legend>Specify your Assembla credentials</legend>

        <div class="row">
            <div class="form-group col-md-4">
                <label for="key">Assembla Key</label>
                <input type="text" id="key" name="key" class="form-control" value="cfbc848c5e2816ac3381" placeholder="Assembla Key" required="required">
            </div>

            <div class="form-group col-md-4">
                <label for="secret">Assembla Secret</label>
                <input type="text" id="secret" name="secret" class="form-control" value="8f21a57b9b86dedeafdc22f34d90d5dff93130f4" placeholder="Assembla Secret" required="required">
            </div>

            <div class="form-group col-md-4">
                <label for="project">Assembla Project</label>
                <input type="text" id="project" name="project" class="form-control" value="" placeholder="Assembla Project" required="required">
            </div>
        </div>


    </fieldset>



<!--    <fieldset>-->
<!--        <legend>Specify date range</legend>-->
<!--        Fecha desde: <input type="text" name="dateFrom" value="--><?php //echo $first; ?><!--" size="12"><br>-->
<!--        Fecha hasta: <input type="text" name="dateTo" value="--><?php //echo $last; ?><!--" size="12"><br>-->
<!--    </fieldset>-->
    <fieldset>
        <legend>Specify date range</legend>

        <div class="row">
            <div class="form-group col-md-6">
                <label for="dateFrom">From</label>
                <input type="text" id="dateFrom" name="dateFrom" class="form-control" value="<?php echo $first; ?>" required="required">
            </div>

            <div class="form-group col-md-6">
                <label for="dateTo">To</label>
                <input type="text" id="dateTo" name="dateTo" class="form-control" value="<?php echo $last; ?>" required="required">
            </div>
        </div>
    </fieldset>



<!--    <fieldset>-->
<!--        <legend>Exceptions</legend>-->
<!--        Specify the tickets that should be ignored, if any (separate them with commas). <br>-->
<!--        <textarea name="exceptions" rows="4" cols="50"></textarea>-->
<!--    </fieldset>-->

    <fieldset>
        <legend>Exceptions</legend>

        <div class="form-group">
            <label for="exceptions">Specify the tickets that should be ignored, if any (separate them with commas)</label>
            <textarea name="exceptions" id="exceptions" class="form-control" rows="4" cols="50"></textarea>
        </div>
    </fieldset>

<!--    <INPUT type="submit" value="Next"> <INPUT type="reset">-->
    <button type="submit" class="btn btn-lg btn-primary">Next</button>
</form>

<?php include '../footer.php'; ?>

