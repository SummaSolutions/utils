<!DOCTYPE html>
<html>
<head>
    <title>Flow Based Project - Indicator report</title>
    <meta charset="utf-8">

    <style>

        table,td
        {
            border:1px solid black;
            text-align: right;
        }
        table {
            border-collapse: collapse;
        }
        tfoot,th
        {
            text-align: left;
            font-weight: bold;
            background-color: #666666;
            color: #dddddd;
            font-size: 100%;
        }
    </style>

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
require_once("../core/misc.php");

if (trim($_POST['exceptions']) != '') {
    $exceptions = explode(',', $_POST['exceptions']);
} else {
    $exceptions = null;
}

$analyzer = new FLowAnalyzer($_POST['key'], $_POST['secret'], $_POST['project'], $exceptions);

$users = null;
if( !isset($_POST['skipUserValidation']))
{
    if(isset($_POST['users']) ){
        $users =  $_POST['users'];
    }
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

<table>
    <tr>
        <th>#</th>
        <th>Summary</th>
        <th>Finished</th>
        <th>Estimated</th>
        <th>Invested</th>
        <th>Work Ratio</th>
        <th>Deviation</th>
        <th>Ticket Ponderation</th>
        <th>Ponderated Deviation</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getCompletedTickets() as $ticket) {

        echo '<tr';
        echo alterTr($ticket);
        echo '>';

        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->completed_date . '</td>';
        echo '<td>' . $ticket->estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td>' . number_format($ticket->workRatio, 2) . '%</td>';
        echo '<td>' . number_format($ticket->deviation, 2) . '</td>';
        echo '<td>' . number_format($ticket->ponderation, 2) . '</td>';
        echo '<td>' . number_format($ticket->ponderated_deviation, 2) . '</td>';
        echo '<td style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>

    <tfoot>
    <tr style="font:bold">
        <?php

        echo '<td>Totals</td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td>' . number_format($analyzer->getTotalEstimatedHours(),2) . '</td>';
        echo '<td>' . number_format($analyzer->getTotalInvestedHours(),2) . '</td>';
        echo '<td>' . number_format($analyzer->getGeneralWorkRatio(), 2) . '%</td>';
        echo '<td>' . number_format($analyzer->getTotalDeviation(), 2). '</td>';
        echo '<td>' . number_format($analyzer->getTotalPonderation(), 2) . '</td>';
        echo '<td>' . number_format($analyzer->getTotalPonderatedDeviation(), 2) . '</td>';
        echo '<td></td>';

        ?>

    </tr>
    </tfoot>

</table>

<br>

<h3>Excluded Tickets</h3>

<table border="1">
    <tr>
        <th>#</th>
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
<h3>Total invested hours (in the delivered tickets): <?php echo $analyzer->getTotalInvestedHours(); ?></h3>
<br>
<h3>Total deviation (hours): <?php echo $analyzer->getTotalDeviation(); ?></h3>
<br>
<h3>Ponderated deviation: <?php echo number_format($analyzer->getTotalPonderatedDeviation(), 2); ?></h3>
<br>
<h3>General Work Ratio: <?php echo number_format($analyzer->getGeneralWorkRatio(), 2); ?>%</h3>
<br>
<br>

<h3>Ticket created in the period that are not completed: <?php echo count($analyzer->getPendingTickets()); ?></h3>

<table border="1">
    <tr>
        <th>#</th>
        <th>Summary</th>
        <th>Creation Date</th>
        <th>Estimated</th>
        <th>Invested</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getPendingTickets() as $ticket) {

        echo '<tr';
        echo alterTr($ticket);
        echo '>';

        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->created_on . '</td>';
        echo '<td>' . $ticket->estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>



</table>

</body>

</html>