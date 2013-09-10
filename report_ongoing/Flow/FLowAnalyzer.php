<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/9/13
 * Time: 10:20 AM
 */

require_once('../core/DataCollector.php');

class FLowAnalyzer
{

    private $_tickets;
    private $_avgDeviation;
    private $_avgErrorPercentage;
    private $_generalWorkRatio;

    public function getTickets()
    {
        return $this->_tickets;
    }

    public function getAvgDeviation()
    {
        return $this->_avgDeviation;
    }

    public function getAvgErrorPercentage()
    {
        return $this->_avgErrorPercentage;
    }

    public function getGeneralWorkRatio(){
        return $this->_generalWorkRatio;
    }

    function __construct($key, $secret, $space)
    {
        $this->dataCollector = new DataCollector($key, $secret, $space);

        $this->_tickets = array();
    }

    /**
     * Analyze the specified period.
     * @param $from
     * @param $to
     */
    function analyzePeriod($from, $to)
    {

        $this->filterTickets($from, $to);
        $this->calculateResults();
    }

    /**
     * Calculate the indicators for the period.
     */
    private function calculateResults()
    {

        $totalDeviation = 0;
        $totalPercentage = 0;
        $totalInvested = 0;
        $totalEstimated = 0;


        foreach ($this->_tickets as $ticket) {

            $ticket->deviation = abs($ticket->total_estimate - $ticket->total_invested_hours);
            $ticket->errorPercentage = ($ticket->deviation / $ticket->total_estimate) * 100;

            $totalDeviation += $ticket->deviation;
            $totalPercentage += $ticket->errorPercentage;

            $ticket->workRatio = $ticket->total_invested_hours / $ticket->total_estimate;

            $totalInvested += $ticket->total_invested_hours;
            $totalEstimated += $ticket->total_estimate;
        }

        $this->_avgDeviation = $totalDeviation / count($this->_tickets);
        $this->_avgErrorPercentage = $totalPercentage / count($this->_tickets);
        $this->_generalWorkRatio = $totalInvested / $totalEstimated;

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
        $tickets = $this->dataCollector->getAllTickets();

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

        if ($ticketDate >= $fromDate &&
            $ticketDate <= $toDate
        ) {
            $this->_tickets[] = $ticket;
        }
    }


} 