<!DOCTYPE html>
<html>
<head>
    <title>Milestone Based Project - Indicator report</title>
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
        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->completed_date . '</td>';
        echo '<td>' . $ticket->total_estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td>' . $ticket->workRatio . '%</td>';
        echo '<td>' . $ticket->deviation . '</td>';
        echo '<td>' . $ticket->errorPercentage . '%</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total completed tickets:<?php echo count($analyzer->getCompletedTickets()); ?></h4>

<br>

<h3>Incomplete Tickets</h3>

<table border="1">
    <tr>
        <th>Ticket number</th>
        <th>Summary</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getIncompleteTickets() as $ticket) {
        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total incomplete tickets:<?php echo count($analyzer->getIncompleteTickets()); ?></h4>

<br>

<h3>Deferred Tickets</h3>

<table border="1">
    <tr>
        <th>Ticket number</th>
        <th>Summary</th>
        <th>Current Milestone</th>
        <th>Status</th>
    </tr>
    <?php
    foreach ($analyzer->getDeferredTickets() as $ticket) {
        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->currentMilestone . '</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total deferred tickets:<?php echo count($analyzer->getDeferredTickets()); ?></h4>

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
        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>
<h4>Total excluded tickets:<?php echo count($analyzer->getExcludedTickets()); ?></h4>

<br><br>

<h2>Milestone Indicators</h2><br>

<h3>Average deviation (hours): <?php echo number_format($analyzer->getAvgDeviation(), 2); ?></h3>
<br>
<h3>Average Error Percentage: <?php echo number_format($analyzer->getAvgErrorPercentage(), 2); ?>%</h3>
<br>
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
