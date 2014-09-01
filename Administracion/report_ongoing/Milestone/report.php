<!DOCTYPE html>
<html>
<head>
    <title>Milestone Based Project - Indicator report</title>
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

require_once('MilestoneAnalyzer.php');
require_once("../core/misc.php");


if( !isset($_POST["plan"])){
    echo "You have selected no plan level at all. Nothing to do!";
    die;
}



if (trim($_POST['exceptions']) != '') {
    $exceptions = explode(',', $_POST['exceptions']);
} else {
    $exceptions = null;
}

$analyzer = new MilestoneAnalyzer(
    $_POST['key'],
    $_POST['secret'],
    $_POST['project'],
    $_POST['status'],
    $exceptions);

$analyzer->AnalyzeMilestone((int)$_POST['milestone'], $_POST["plan"]);

?>

<h1>Sprint based project Metrics</h1>

<h2><?php echo $_POST['project'] ?></h2>
<br>

<h2>Milestone: <?php echo $analyzer->getMilestone()->title; ?></h2><br>

<h3>Due date: <?php echo $analyzer->getMilestone()->due_date; ?></h3>
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
        <th>Deviation</th>
        <th>Error(%)</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getCompletedTickets() as $ticket) {

        echo '<tr';
        echo alterTr($ticket);
        echo '>';

        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';

        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->completed_date . '</td>';
        echo '<td>' . $ticket->total_estimate . '</td>';
        echo '<td>' . formatValue($ticket->total_invested_hours) . '</td>';
        echo '<td>' . formatValue($ticket->workRatio)  . '</td>';
        echo '<td>' . formatValue($ticket->deviation) . '</td> ';
        echo '<td>' . formatValue($ticket->errorPercentage) . '</td>';
        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>

<h4>Total completed tickets:<?php echo count($analyzer->getCompletedTickets()); ?></h4>
<h4>Total time invested in complete tickets: <?php echo $analyzer->getcompleteTicketsInvestedHours(); ?></h4>

<br>

<h3>Incomplete Tickets</h3>

<table border="1">
    <tr>
        <th>#</th>
        <th>Plan Level</th>
        <th>Summary</th>
        <th>Estimated</th>
        <th>Invested</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getIncompleteTickets() as $ticket) {
        echo '</tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->total_estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total incomplete tickets:<?php echo count($analyzer->getIncompleteTickets()); ?></h4>
<h4>Total time invested in incomplete tickets: <?php echo $analyzer->getIncompleteTicketsInvestedHours(); ?></h4>


<br>

<h3>Deferred Tickets</h3>

<table border="1">
    <tr>
        <th>#</th>
        <th>Plan Level</th>
        <th>Summary</th>
        <th>Current Milestone</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getDeferredTickets() as $ticket) {
        echo '</tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->currentMilestone . '</td>';
        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total deferred tickets:<?php echo count($analyzer->getDeferredTickets()); ?></h4>

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
        echo '</tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total excluded tickets:<?php echo count($analyzer->getExcludedTickets()); ?></h4>

<br><br>

<h2>Milestone Indicators</h2><br>

<table>
    <tr>
        <th>Indicator</th>
        <th>Value</th>
    </tr>
    <tr>
        <td style="text-align: left">Commited Tickets</td>
        <td><?php echo $analyzer->getIndicators()->ticketsTotal; ?></td>
    </tr>
    <tr>
        <td style="text-align: left">Delivered Tickets</td>
        <td><?php echo $analyzer->getIndicators()->totalCompleted . ' (' . formatValue($analyzer->getIndicators()->completedPercentage) . ' %)'     ; ?></td>
    </tr>
    <tr>
        <td style="text-align: left">Not Delivered Tickets </td>
        <td><?php echo $analyzer->getIndicators()->totalIncomplete . ' (' . formatValue($analyzer->getIndicators()->incompletePercentage) .' %)'; ?></td>
    </tr>
    <tr>
       <td style="text-align: left">General Work Ratio</td>
       <td><?php echo formatValue($analyzer->getGeneralWorkRatio()); ?>%</td>
    </tr>

</table>


</body>
</html>
