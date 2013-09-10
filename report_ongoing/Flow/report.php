<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/9/13
 * Time: 10:37 AM
 */

require_once("FLowAnalyzer.php");


$analyzer = new FLowAnalyzer($_POST['key'], $_POST['secret'], $_POST['project']);
$analyzer->analyzePeriod($_POST['dateFrom'],$_POST['dateTo']);

?>

<h1>Ongoing project Metrics</h1>
<h2><?php echo $_POST['project'] ?></h2>
<br>

<h2>range: <?php echo $_POST['dateFrom'];?> to <?php echo $_POST['dateFrom']; ?></h2>
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
        <th>Deviation (hours)</th>
        <th>Error Percentage</th>
        <th>Final Status</th>
    </tr>
    <?php
    foreach($analyzer->getTickets() as $ticket){
        echo '<td>' . $ticket->number . '</td>';
        echo '<td>' . $ticket->summary . '</td>';
        echo '<td>' . $ticket->completed_date . '</td>';
        echo '<td>' . $ticket->total_estimate . '</td>';
        echo '<td>' . $ticket->total_invested_hours . '</td>';
        echo '<td>' . $ticket->deviation . '</td>';
        echo '<td>' . $ticket->errorPercentage . '%</td>';
        echo '<td>' . $ticket->status . '</td>';
        echo '</tr>';
    }
    ?>
</table>

<br><br>
<h2>Period Indicators</h2>
<br>
<h3>Total delivered tickets: <?php echo count($analyzer->getTickets());?></h3>
<br>
<h3>Average deviation (hours): <?php echo number_format($analyzer->getAvgDeviation(), 2);?></h3>
<br>
<h3>Average Error Percentage: <?php echo number_format($analyzer->getAvgErrorPercentage(),2);?>%</h3>

</body>

</html>