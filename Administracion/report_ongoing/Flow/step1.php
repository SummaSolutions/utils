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

<!DOCTYPE html>
<html>
<head>
    <title>Milestone Based projects - Indicators report (step 1)</title>
    <meta charset="utf-8">
</head>

<body>
<form action="step2.php" method="post">

    <fieldset>
        <legend>Specify your Assembla credentials</legend>
        Assembla Key: <input type="text" name="key" value="cfbc848c5e2816ac3381" size="60"><br>
        Assembla Secret: <input type="text" name="secret" value="8f21a57b9b86dedeafdc22f34d90d5dff93130f4"
                                size="60"><br>
        Assembla Project: <input type="text" name="project" value="" size="60"><br><br>
    </fieldset>

    <fieldset>
        <legend>Specify date range</legend>
        Fecha desde: <input type="text" name="dateFrom" value="<?php echo $first; ?>" size="12"><br>
        Fecha hasta: <input type="text" name="dateTo" value="<?php echo $last; ?>" size="12"><br>
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

