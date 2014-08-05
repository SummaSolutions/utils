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

$analyzer->AnalyzeMilestone((int)$_POST['milestone']);

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
        if( $ticket->isEpic){
            echo ' bgcolor = "#2CFFA5" title="This is an Epic" ';
        }
        echo '>';

        echo '<td>' . $ticket->number . '</td>';
        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->completed_date . '</td>';
        echo '<td>' . $ticket->estimate . '</td>';
        echo '<td>' . number_format($ticket->total_invested_hours, 2) . '</td>';
        echo '<td>' . number_format($ticket->workRatio, 2)  . '%</td>';
        echo '<td>' . number_format($ticket->deviation, 2) . '</td> ';
        echo '<td>' . number_format($ticket->errorPercentage, 2) . '%</td>';
        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total completed tickets:<?php echo count($analyzer->getCompletedTickets()); ?></h4>

<br>

<h3>Incomplete Tickets</h3>

<table border="1">
    <tr>
        <th>#</th>
        <th>Summary</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getIncompleteTickets() as $ticket) {
        echo '</tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total incomplete tickets:<?php echo count($analyzer->getIncompleteTickets()); ?></h4>

<br>

<h3>Deferred Tickets</h3>

<table border="1">
    <tr>
        <th>#</th>
        <th>Summary</th>
        <th>Current Milestone</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getDeferredTickets() as $ticket) {
        echo '</tr>';
        echo '<td>' . $ticket->number . '</td>';
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
        <th>Summary</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getExcludedTickets() as $ticket) {
        echo '</tr>';
        echo '<td>' . $ticket->number . '</td>';
        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total excluded tickets:<?php echo count($analyzer->getExcludedTickets()); ?></h4>

<br><br>

<h2>Milestone Indicators</h2><br>

<h3>General Work Ratio: <?php echo number_format($analyzer->getGeneralWorkRatio(), 2); ?>%</h3>
<br>

<h3>Total committed tickets: <?php echo $analyzer->getIndicators()->ticketsTotal; ?></h3><br>
<h3>Total delivered tickets: <?php echo $analyzer->getIndicators()->totalCompleted; ?></h3>
<h3>( <?php echo number_format($analyzer->getIndicators()->completedPercentage, 2); ?> %)</h3>
<br>
<h3>Total not delivered tickets: <?php echo $analyzer->getIndicators()->totalIncomplete; ?></h3>
<h3>( <?php echo number_format($analyzer->getIndicators()->incompletePercentage, 2); ?> %)</h3>
</body>
</html>
