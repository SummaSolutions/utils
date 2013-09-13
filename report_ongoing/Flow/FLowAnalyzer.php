<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/9/13
 * Time: 10:20 AM
 */

require_once('../core/TicketsAnalyzer.php');

class FLowAnalyzer extends TicketsAnalyzer
{
    /**
     * Analyze the specified period.
     * @param $from
     * @param $to
     */
    function analyzePeriod($from, $to)
    {
        $this->filterTickets($from, $to);
        $this->calculateTicketResults();
    }

    /**
     * Filter the tickets that were completed in the period.
     *
     * @param $from
     * @param $to
     * @return array
     */
    private function filterTickets($from, $to)
    {

        // Fetch all tickets for the space.
        $tickets = $this->_dataCollector->getAllTickets();

        foreach ($tickets as $ticket) {

            if (!is_null($ticket->completed_date)) {

                $this->processTicket($from, $to, $ticket);
            }
        }
    }

    /**
     * Verify if this particular ticket needs to be included in the report.
     * @param $from
     * @param $to
     * @param $ticket
     */
    private function processTicket($from, $to, $ticket)
    {
        $ticketDate = strtotime($ticket->completed_date);

        $fromDate = strtotime($from);
        $toDate = strtotime($to);

        if (($ticketDate >= $fromDate && $ticketDate <= $toDate) &&
            !$this->ticketIsInExceptionList($ticket)
        ) {
            $this->_completedTickets[] = $ticket;
        }
    }


} 