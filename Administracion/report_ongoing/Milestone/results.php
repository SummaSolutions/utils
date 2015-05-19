<?php include '../header.php'; ?>

<div class="jumbotron">
    <h2><?php echo $analyzer->getSpace(); ?></h2>
    <p>Milestone: <?php echo $analyzer->getMilestone()->title; ?></p>
    <p>Due date: <?php echo $analyzer->getMilestone()->due_date; ?></p>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <h1>Completed Tickets</h1>
        </div>

        <table class="table">
            <thead>
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
            </thead>

            <tbody>
            <?php foreach ($analyzer->getCompletedTickets() as $ticket) : ?>
                <tr <?php echo alterTr($ticket); ?>>
                    <td><?php echo $ticket->number; ?></td>
                    <td><?php echo showPlanLevel($ticket->hierarchy_type); ?></td>
                    <td><?php echo $ticket->summary; ?></td>
                    <td><?php echo $ticket->completed_date; ?></td>
                    <td><?php echo $ticket->total_estimate; ?></td>
                    <td><?php echo $ticket->total_invested_hours; ?></td>
                    <td><?php echo formatValue($ticket->workRatio); ?></td>
                    <td><?php echo formatValue($ticket->deviation); ?></td>
                    <td><?php echo formatValue($ticket->errorPercentage); ?></td>
                    <td><?php echo formatValue($ticket->ponderation); ?></td>
                    <td><?php echo formatValue($ticket->ponderated_deviation); ?></td>
                    <td><?php echo $ticket->status; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>

            <tfoot>
            <tr>
                <td colspan="4">Totals</td>
                <td><?php echo formatValue($analyzer->getTotalEstimatedHours()); ?></td>
                <td><?php echo formatValue($analyzer->getTotalInvestedHours()); ?></td>
                <td><?php echo formatValue($analyzer->getGeneralWorkRatio()); ?></td>
                <td><?php echo formatValue($analyzer->getTotalDeviation()); ?></td>
                <td></td>
                <td><?php echo formatValue($analyzer->getTotalPonderation()); ?></td>
                <td><?php echo formatValue($analyzer->getTotalPonderatedDeviation()); ?></td>
                <td></td>
            </tr>
            </tfoot>
        </table>

        <h4>Total completed tickets: <?php echo count($analyzer->getCompletedTickets()); ?></h4>
        <h4>Total estimated time in completed tickets: <?php echo $analyzer->getTotalEstimatedHours(); ?></h4>
        <h4>Total time invested in completed tickets: <?php echo $analyzer->getcompleteTicketsInvestedHours(); ?></h4>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <h1>Incomplete Tickets</h1>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Plan Level</th>
                <th>Summary</th>
                <th>Estimated</th>
                <th>Invested</th>
                <th>Status</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($analyzer->getIncompleteTickets() as $ticket) : ?>
                <tr>
                    <td><?php echo $ticket->number; ?></td>
                    <td><?php echo showPlanLevel($ticket->hierarchy_type); ?></td>
                    <td><?php echo $ticket->summary; ?></td>
                    <td><?php echo $ticket->total_estimate; ?></td>
                    <td><?php echo $ticket->total_invested_hours; ?></td>
                    <td><?php echo $ticket->status; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Total incomplete tickets:<?php echo count($analyzer->getIncompleteTickets()); ?></h4>
        <h4>Total time invested in incomplete tickets: <?php echo $analyzer->getIncompleteTicketsInvestedHours(); ?></h4>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <h1>Deferred Tickets</h1>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Plan Level</th>
                <th>Summary</th>
                <th>Current Milestone</th>
                <th>Status</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($analyzer->getDeferredTickets() as $ticket) : ?>
                <tr>
                    <td><?php echo $ticket->number; ?></td>
                    <td><?php echo showPlanLevel($ticket->hierarchy_type); ?></td>
                    <td><?php echo $ticket->summary; ?></td>
                    <td><?php echo $ticket->currentMilestone; ?></td>
                    <td><?php echo $ticket->status; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Total deferred tickets: <?php echo count($analyzer->getDeferredTickets()); ?></h4>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <h1>Excluded Tickets</h1>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Plan Level</th>
                <th>Summary</th>
                <th>Status</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($analyzer->getExcludedTickets() as $ticket) : ?>
                <tr>
                    <td><?php echo $ticket->number; ?></td>
                    <td><?php echo showPlanLevel($ticket->hierarchy_type); ?></td>
                    <td><?php echo $ticket->summary; ?></td>
                    <td><?php echo $ticket->status; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Total excluded tickets: <?php echo count($analyzer->getExcludedTickets()); ?></h4>    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <h1>Milestone Indicators</h1>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>Indicator</th>
                <th>Value</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td>Commited Tickets</td>
                <td><?php echo $analyzer->getIndicators()->ticketsTotal; ?></td>
            </tr>
            <tr>
                <td>Delivered Tickets</td>
                <td><?php echo $analyzer->getIndicators()->totalCompleted . ' (' . formatValue($analyzer->getIndicators()->completedPercentage) . ' %)'     ; ?></td>
            </tr>
            <tr>
                <td>Not Delivered Tickets </td>
                <td><?php echo $analyzer->getIndicators()->totalIncomplete . ' (' . formatValue($analyzer->getIndicators()->incompletePercentage) .' %)'; ?></td>
            </tr>
            <tr>
                <td>General Work Ratio</td>
                <td><?php echo formatValue($analyzer->getGeneralWorkRatio()); ?>%</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>

<!--<html>-->
<!--<head>-->
<!--    <style>-->
<!---->
<!--        table,td-->
<!--        {-->
<!--            border:1px solid black;-->
<!--            text-align: right;-->
<!--        }-->
<!--        table {-->
<!--            border-collapse: collapse;-->
<!--        }-->
<!--        tfoot,th-->
<!--        {-->
<!--            text-align: left;-->
<!--            font-weight: bold;-->
<!--            background-color: #666666;-->
<!--            color: #dddddd;-->
<!--            font-size: 100%;-->
<!--        }-->
<!--    </style>-->
<!--</head>-->
<!--<body>-->
<!---->
<!---->
<!---->
<!--<h1>Sprint based project Metrics</h1>-->
<!---->
<!--<h2>--><?php //echo $analyzer->getSpace() ?><!--</h2>-->
<!--<br>-->
<!---->
<!--<h2>Milestone: --><?php //echo $analyzer->getMilestone()->title; ?><!--</h2><br>-->
<!---->
<!--<h3>Due date: --><?php //echo $analyzer->getMilestone()->due_date; ?><!--</h3>-->
<!--<br>-->
<!--<br>-->
<!---->
<!--<h3>Completed Tickets</h3>-->
<!---->
<!--<table>-->
<!--    <tr>-->
<!--        <th>#</th>-->
<!--        <th>Plan Level</th>-->
<!--        <th>Summary</th>-->
<!--        <th>Finished</th>-->
<!--        <th>Estimated</th>-->
<!--        <th>Invested</th>-->
<!--        <th>Work Ratio</th>-->
<!--        <th>Deviation (hours)</th>-->
<!--        <th>Absolute error (%)</th>-->
<!--        <th>Ticket Ponderation</th>-->
<!--        <th>Ponderated error</th>-->
<!--        <th>Status</th>-->
<!--    </tr>-->
<!--    --><?php
//    foreach ($analyzer->getCompletedTickets() as $ticket) {
//
//        echo '<tr';
//        echo alterTr($ticket);
//        echo '>';
//        echo '<td>' . $ticket->number . '</td>';
//        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
//        echo '<td style="text-align: left">' . $ticket->summary . '</td>';
//        echo '<td>' . $ticket->completed_date . '</td>';
//        echo '<td>' . $ticket->total_estimate . '</td>';
//        echo '<td>' . $ticket->total_invested_hours . '</td>';
//        echo '<td>' . formatValue($ticket->workRatio) . '</td>';
//        echo '<td>' . formatValue($ticket->deviation) . '</td>';
//        echo '<td>' . formatValue($ticket->errorPercentage) . '</td>';
//        echo '<td>' . formatValue($ticket->ponderation) . '</td>';
//        echo '<td>' . formatValue($ticket->ponderated_deviation) . '</td>';
//        echo '<td style="text-align: left">' . $ticket->status . '</td>';
//        echo '</tr>';
//    }
//    ?>
<!---->
<!--    <tfoot>-->
<!--    <tr style="font:bold">-->
<!--        --><?php
//
//        echo '<td>Totals</td>';
//        echo '<td></td>';
//        echo '<td></td>';
//        echo '<td></td>';
//        echo '<td>' . formatValue($analyzer->getTotalEstimatedHours()) . '</td>';
//        echo '<td>' . formatValue($analyzer->getTotalInvestedHours()) . '</td>';
//        echo '<td>' . formatValue($analyzer->getGeneralWorkRatio()) . '</td>';
//        echo '<td>' . formatValue($analyzer->getTotalDeviation()). '</td>';
//        echo '<td></td>';
//        echo '<td>' . formatValue($analyzer->getTotalPonderation()) . '</td>';
//        echo '<td>' . formatValue($analyzer->getTotalPonderatedDeviation()) . '</td>';
//        echo '<td></td>';
//
//        ?>
<!---->
<!--    </tr>-->
<!--    </tfoot>-->
<!---->
<!--</table>-->
<!---->
<!---->
<!--<h4>Total completed tickets:--><?php //echo count($analyzer->getCompletedTickets()); ?><!--</h4>-->
<!--<h4>Total estimated time in completed tickets: --><?php //echo $analyzer->getTotalEstimatedHours(); ?><!--</h4>-->
<!--<h4>Total time invested in completed tickets: --><?php //echo $analyzer->getcompleteTicketsInvestedHours(); ?><!--</h4>-->
<!---->
<!--<br>-->
<!---->
<!--<h3>Incomplete Tickets</h3>-->
<!---->
<!--<table border="1">-->
<!--    <tr>-->
<!--        <th>#</th>-->
<!--        <th>Plan Level</th>-->
<!--        <th>Summary</th>-->
<!--        <th>Estimated</th>-->
<!--        <th>Invested</th>-->
<!--        <th>Status</th>-->
<!--    </tr>-->
<!--    --><?php
//    foreach ($analyzer->getIncompleteTickets() as $ticket) {
//        echo '</tr>';
//        echo '<td>' . $ticket->number . '</td>';
//        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
//        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
//        echo '<td>' . $ticket->total_estimate . '</td>';
//        echo '<td>' . $ticket->total_invested_hours . '</td>';
//        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
//        echo '</tr>';
//    }
//    ?>
<!--</table>-->
<!--<h4>Total incomplete tickets:--><?php //echo count($analyzer->getIncompleteTickets()); ?><!--</h4>-->
<!--<h4>Total time invested in incomplete tickets: --><?php //echo $analyzer->getIncompleteTicketsInvestedHours(); ?><!--</h4>-->
<!---->
<!---->
<!--<br>-->
<!---->
<!--<h3>Deferred Tickets</h3>-->
<!---->
<!--<table border="1">-->
<!--    <tr>-->
<!--        <th>#</th>-->
<!--        <th>Plan Level</th>-->
<!--        <th>Summary</th>-->
<!--        <th>Current Milestone</th>-->
<!--        <th>Status</th>-->
<!--    </tr>-->
<!--    --><?php
//    foreach ($analyzer->getDeferredTickets() as $ticket) {
//        echo '</tr>';
//        echo '<td>' . $ticket->number . '</td>';
//        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
//        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
//        echo '<td>' . $ticket->currentMilestone . '</td>';
//        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
//        echo '</tr>';
//    }
//    ?>
<!--</table>-->
<!--<h4>Total deferred tickets:--><?php //echo count($analyzer->getDeferredTickets()); ?><!--</h4>-->
<!---->
<!--<br>-->
<!---->
<!--<h3>Excluded Tickets</h3>-->
<!---->
<!--<table border="1">-->
<!--    <tr>-->
<!--        <th>#</th>-->
<!--        <th>Plan Level</th>-->
<!--        <th>Summary</th>-->
<!--        <th>Status</th>-->
<!--    </tr>-->
<!--    --><?php
//    foreach ($analyzer->getExcludedTickets() as $ticket) {
//        echo '</tr>';
//        echo '<td>' . $ticket->number . '</td>';
//        echo '<td style="text-align: left">' . showPlanLevel($ticket->hierarchy_type) . '</td>';
//        echo '<td  style="text-align: left">' . $ticket->summary . '</td>';
//        echo '<td  style="text-align: left">' . $ticket->status . '</td>';
//        echo '</tr>';
//    }
//    ?>
<!--</table>-->
<!--<h4>Total excluded tickets:--><?php //echo count($analyzer->getExcludedTickets()); ?><!--</h4>-->
<!---->
<!--<br><br>-->
<!---->
<!--<h2>Milestone Indicators</h2><br>-->
<!---->
<!--<table>-->
<!--    <tr>-->
<!--        <th>Indicator</th>-->
<!--        <th>Value</th>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <td style="text-align: left">Commited Tickets</td>-->
<!--        <td>--><?php //echo $analyzer->getIndicators()->ticketsTotal; ?><!--</td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <td style="text-align: left">Delivered Tickets</td>-->
<!--        <td>--><?php //echo $analyzer->getIndicators()->totalCompleted . ' (' . formatValue($analyzer->getIndicators()->completedPercentage) . ' %)'     ; ?><!--</td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <td style="text-align: left">Not Delivered Tickets </td>-->
<!--        <td>--><?php //echo $analyzer->getIndicators()->totalIncomplete . ' (' . formatValue($analyzer->getIndicators()->incompletePercentage) .' %)'; ?><!--</td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <td style="text-align: left">General Work Ratio</td>-->
<!--        <td>--><?php //echo formatValue($analyzer->getGeneralWorkRatio()); ?><!--%</td>-->
<!--    </tr>-->
<!---->
<!--</table>-->
<!---->
<!---->
<!--</body>-->
<!--</html>-->