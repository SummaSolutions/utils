<!DOCTYPE html>
<html>
<head>
    <title>Flow Based Project - Indicator report</title>
    <meta charset="utf-8">
</head>

<body>


<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/9/13
 * Time: 10:37 AM
 */

require_once("FLowAnalyzer.php");

if (trim($_POST['exceptions']) != '') {
    $exceptions = explode(',', $_POST['exceptions']);
} else {
    $exceptions = null;
}

$analyzer = new FLowAnalyzer($_POST['key'], $_POST['secret'], $_POST['project'], $exceptions);

$users = null;
if( !isset($_POST['skipUserValidation']))
{
    $users =  $_POST['users'];
}

$analyzer->analyzePeriod($_POST['dateFrom'], $_POST['dateTo'], $users);

?>

<h1>Ongoing project Metrics</h1>
<h2><?php echo $_POST['project'] ?></h2>
<br>

<h2>range: <?php echo $_POST['dateFrom']; ?> to <?php echo $_POST['dateTo']; ?></h2>
<br>
<br>
<h3>Completed Tickets</h3>

<table border="1">
    <tr>
        <th>Ticket number</th>
        <th>Summary</th>
        <th>Completed date</th>
        <th>Estimated (hours)</th>
        <th>Invested (hours)</th>
        <th>Work Ratio</th>
        <th>Deviation (hours)</th>
        <th>Error Percentage</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getCompletedTickets() as $ticket) {

        echo '<tr';
        if( $ticket->isEpic){
            echo ' bgcolor = "#BEE6CD" title="This is an Epic" ';
        }
        echo '>';

        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->completed_date . '</td>';
        echo '<td>' . $ticket->estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td>' . number_format($ticket->workRatio, 2) . '%</td>';
        echo '<td>' . number_format($ticket->deviation, 2) . '</td>';
        echo '<td>' . number_format($ticket->errorPercentage, 2) . '%</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>

<br>

<h3>Excluded Tickets</h3>

<table border="1">
    <tr>
        <th>Ticket number</th>
        <th>Summary</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getExcludedTickets() as $ticket) {
        echo '<tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total excluded tickets:<?php echo count($analyzer->getExcludedTickets()); ?></h4>

<br><br>
<h2>Period Indicators</h2>
<br>
<h3>Total delivered tickets: <?php echo count($analyzer->getCompletedTickets()); ?></h3>
<br>
<h3>Average deviation (hours): <?php echo number_format($analyzer->getAvgDeviation(), 2); ?></h3>
<br>
<h3>Average Error Percentage: <?php echo number_format($analyzer->getAvgErrorPercentage(), 2); ?>%</h3>
<br>
<h3>General Work Ratio: <?php echo number_format($analyzer->getGeneralWorkRatio(), 2); ?>%</h3>


</body>

</html>