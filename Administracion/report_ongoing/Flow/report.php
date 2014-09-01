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

if( !isset($_POST["plan"])){
    echo "You have selected no plan level at all. Nothing to do!";
    die;
}
else{
    $analyzer->analyzePeriod($_POST['dateFrom'], $_POST['dateTo'], $_POST["plan"], $users);
}

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
        <th>Plan Level</th>
        <th>Summary</th>
        <th>Finished</th>
        <th>Estimated</th>
        <th>Invested</th>
        <th>Work Ratio</th>
        <th>Deviation (hours)</th>
        <th>Absolute error (%)</th>
        <th>Ticket Ponderation</th>
        <th>Ponderated error</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getCompletedTickets() as $ticket) {

        echo '<tr';
        echo alterTr($ticket);
        echo '>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
        echo '<td style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->completed_date . '</td>';
        echo '<td>' . $ticket->total_estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td>' . formatValue($ticket->workRatio) . '</td>';
        echo '<td>' . formatValue($ticket->deviation) . '</td>';
        echo '<td>' . formatValue($ticket->errorPercentage) . '</td>';
        echo '<td>' . formatValue($ticket->ponderation) . '</td>';
        echo '<td>' . formatValue($ticket->ponderated_deviation) . '</td>';
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
        echo '<td></td>';
        echo '<td>' . formatValue($analyzer->getTotalEstimatedHours()) . '</td>';
        echo '<td>' . formatValue($analyzer->getTotalInvestedHours()) . '</td>';
        echo '<td>' . formatValue($analyzer->getGeneralWorkRatio()) . '</td>';
        echo '<td>' . formatValue($analyzer->getTotalDeviation()). '</td>';
        echo '<td></td>';
        echo '<td>' . formatValue($analyzer->getTotalPonderation()) . '</td>';
        echo '<td>' . formatValue($analyzer->getTotalPonderatedDeviation()) . '</td>';
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
        <th>Plan Level</th>
        <th>Summary</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getExcludedTickets() as $ticket) {
        echo '<tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total excluded tickets:<?php echo count($analyzer->getExcludedTickets()); ?></h4>

<h2>Period Indicators (considers completed tickets)</h2>
<br>

<table>

    <tr>
        <th>Indicator</th>
        <th>value</th>
    </tr>


    <tr>
        <td style="text-align: left">Tickets without estimation</td>
        <td><?php echo $analyzer->getTotalTicketsWithNoEstimation(); ?></td>
    </tr>


    <tr>
        <td style="text-align: left">Completed tickets</td>
        <td><?php echo count($analyzer->getCompletedTickets()); ?></td>
    </tr>

    <tr>
        <td style="text-align: left">Estimated time</td>
        <td><?php echo formatValue($analyzer->getTotalEstimatedHours()); ?></td>
    </tr>

    <tr>
        <td style="text-align: left">Invested hours</td>
        <td><?php echo $analyzer->getTotalInvestedHours(); ?></td>
    </tr>


    <tr>
        <td style="text-align: left">Perceived deviation (hours)</td>
        <td><?php echo formatValue($analyzer->getPerceivedDeviation()) ; ?></td>
    </tr>

    <tr>
        <td style="text-align: left">Perceived error (%)</td>
        <td><?php echo formatValue($analyzer->getPerceivedError()) ; ?></td>
    </tr>

    <tr>
        <td style="text-align: left">Absolute deviation (hours)</td>
        <td><?php echo $analyzer->getTotalDeviation(); ?></td>
    </tr>

    <tr>
        <td style="text-align: left">Ponderated error</td>
        <td><?php echo formatValue($analyzer->getTotalPonderatedDeviation(), 2); ?></td>
    </tr>

    <tr>
        <td style="text-align: left">General Work Ratio</td>
        <td><?php echo formatValue($analyzer->getGeneralWorkRatio(), 2); ?></td>
    </tr>


</table>



<h3>Ticket created in the period that are not completed: <?php echo count($analyzer->getPendingTickets()); ?></h3>

<table border="1">
    <tr>
        <th>#</th>
        <th>Plan Level</th>
        <th>Summary</th>
        <th>Creation Date</th>
        <th>Estimated</th>
        <th>Invested</th>
        <th>Status</th>
    </tr>
    <?php

    foreach ($analyzer->getPendingTickets() as $ticket) {

        echo '<tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
        echo '<td style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->created_on . '</td>';
        echo '<td>' . $ticket->total_estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>

</table>

<h4>Total time invested in incomplete tickets: <?php echo $analyzer->getIncompleteTicketsInvestedHours(); ?></h4>

</body>

</html>