

<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/8/13
 * Time: 7:53 PM
 */
?>

<!DOCTYPE html>
<html>
<head>
    <title>Milestone Based projects - Indicators report (step 1)</title>
</head>

<body>
<form action="report.php" method="post">

    <fieldset>
        <legend>Specify your Assembla credentials</legend>
        Assembla Key: <input type="text" name="key" value="cfbc848c5e2816ac3381" size="60"><br>
        Assembla Secret: <input type="text" name="secret" value="8f21a57b9b86dedeafdc22f34d90d5dff93130f4" size="60"><br>
        Assembla Project: <input type="text" name="project" value="virtual-piggy" size="60"><br><br>
    </fieldset>

    <fieldset>
        <legend>Specify date range</legend>
        Fecha desde: <input type="text" name="dateFrom" value="2013-01-17" size="12"><br>
        Fecha hasta: <input type="text" name="dateTo" value="2013-01-21" size="12"><br>
    </fieldset>

    <INPUT type="submit" value="Next"> <INPUT type="reset">
</form>

</body>

</html>

