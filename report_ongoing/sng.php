<!DOCTYPE html>
<html>
<head>
    <title>Ongoing Project report</title>
</head>

<body>

<?php

require_once('core/DataCollector.php');
require_once('core/misc.php');

$from = '01/01/2013';
$to = '01/31/2013';
$project = 'stockngo';

$handler = new DataCollector('cfbc848c5e2816ac3381', '8f21a57b9b86dedeafdc22f34d90d5dff93130f4', $project);

// Define statuses to consider
$responded = array('Accepted', 'Test', 'Invalid', 'Fixed');
$started = array('Accepted', 'Test', 'Invalid', 'Fixed');
$completed = array('Invalid', 'Fixed');


// Initialize extraction engine
$handler->initialize($responded, $started, $completed);

// Get the tickets within the range
$tickets = $handler->getTicketsCreatedInRange($from, $to);

?>

<h1>Ongoing Project Metrics </h1>
<h2><?php echo $project ?></h2>
<br>
<h2>Period from <?php echo $from . ' to ' . $to;?></h2>

<table border="1">
    <tr>
        <th>Ticket number</th>
        <th>Summary</th>
        <th>Created on</th>
        <th>Responded on</th>
        <th>Response Time</th>
        <th>Started on</th>
        <th>Start Time</th>
        <th>Completed on</th>
        <th>Complete Time</th>
        <th>Total Dragging time</th>
    </tr>

    <?php
    foreach($tickets as $ticket){

        echo '<tr ' . alterTr($ticket) . '>';

        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . format_date($ticket->created) . '</td>';
        echo '<td>' . format_date($ticket->responded) . '</td>';
        echo '<td>' . $ticket->RespondedTime . '</td>';
        echo '<td>' . format_date($ticket->started) . '</td>';
        echo '<td>' . $ticket->startedTime . '</td>';
        echo '<td>' . format_date($ticket->completed) . '</td>';
        echo '<td>' . $ticket->completedTime . '</td>';
        echo '<td>' . $ticket->draggingTime . '</td>';

        echo '</tr>';
    }
    ?>

</table>

</body>

</html>
?>