<?php include '../header.php'; ?>

<div class="jumbotron">
    <h2><?php echo $analyzer->getSpace(); ?></h2>
    <p>From <?php echo $analyzer->getFrom(); ?> to <?php echo $analyzer->getTo(); ?></p>
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
                <th>Absolute Error (%)</th>
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

        <h4>Total excluded tickets: <?php echo count($analyzer->getExcludedTickets()); ?></h4>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <h1>Period Indicators (considers completed tickets)</h1>
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
                <td>Tickets without estimation</td>
                <td><?php echo $analyzer->getTotalTicketsWithNoEstimation(); ?></td>
            </tr>
            <tr>
                <td>Completed tickets</td>
                <td><?php echo count($analyzer->getCompletedTickets()); ?></td>
            </tr>
            <tr>
                <td>Estimated time</td>
                <td><?php echo formatValue($analyzer->getTotalEstimatedHours()); ?></td>
            </tr>
            <tr>
                <td>Invested hours</td>
                <td><?php echo $analyzer->getTotalInvestedHours(); ?></td>
            </tr>
            <tr>
                <td>Perceived deviation (hours)</td>
                <td><?php echo formatValue($analyzer->getPerceivedDeviation()) ; ?></td>
            </tr>
            <tr>
                <td>Perceived error (%)</td>
                <td><?php echo formatValue($analyzer->getPerceivedError()) ; ?></td>
            </tr>
            <tr>
                <td>Absolute deviation (hours)</td>
                <td><?php echo $analyzer->getTotalDeviation(); ?></td>
            </tr>
            <tr>
                <td>Ponderated error</td>
                <td><?php echo formatValue($analyzer->getTotalPonderatedDeviation(), 2); ?></td>
            </tr>
            <tr>
                <td>General Work Ratio</td>
                <td><?php echo formatValue($analyzer->getGeneralWorkRatio(), 2); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="page-header">
            <h1>Ticket created in the period that are not completed: <?php echo count($analyzer->getPendingTickets()); ?></h1>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>#</th>
                <th>Plan Level</th>
                <th>Summary</th>
                <th>Creation Date</th>
                <th>Estimated</th>
                <th>Invested</th>
                <th>Status</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($analyzer->getPendingTickets() as $ticket) : ?>
                <?php $ticketDate = strtotime($ticket->created_on); ?>
                <?php $ticket->created_on = date('d/m/Y', $ticketDate); ?>

                <tr>
                    <td><?php echo $ticket->number; ?></td>
                    <td><?php echo showPlanLevel($ticket->hierarchy_type); ?></td>
                    <td><?php echo $ticket->summary; ?></td>
                    <td><?php echo $ticket->created_on; ?></td>
                    <td><?php echo $ticket->total_estimate; ?></td>
                    <td><?php echo $ticket->total_invested_hours; ?></td>
                    <td><?php echo $ticket->status; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h4>Total time invested in incomplete tickets: <?php echo $analyzer->getIncompleteTicketsInvestedHours(); ?></h4>
    </div>
</div>

<?php include '../footer.php'; ?>
