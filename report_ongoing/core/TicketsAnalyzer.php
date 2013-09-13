<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/11/13
 * Time: 9:36 AM
 */

require_once("AssemblaConnector.php");
require_once("DataCollector.php");


class TicketsAnalyzer {

    protected $_conn;
    protected $_dataCollector;
    protected $_completedTickets;
    protected $_avgDeviation;
    protected $_avgErrorPercentage;
    protected $_generalWorkRatio;
    protected $_excludedTickets;

    public function getCompletedTickets()
    {
        return $this->_completedTickets;
    }

    public function getExcludedTickets()
    {
        return $this->_excludedTickets;
    }

    public function getExceptions(){
        return $this->_exceptionsList;
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

    function __construct($key, $secret, $space, $exceptions)
    {
        $this->_conn = new AssemblaConnector($key, $secret, $space);
        $this->_dataCollector = new DataCollector($key, $secret, $space);
        $this->_exceptionsList = $exceptions;
        $this->_excludedTickets = array();
    }

    /**
     * Calculate the indicators for the period.
     */
    protected function calculateTicketResults()
    {
        $totalDeviation = 0;
        $totalPercentage = 0;
        $totalInvested = 0;
        $totalEstimated = 0;

        foreach ($this->_completedTickets as $ticket) {

            $ticket->deviation = abs($ticket->total_estimate - $ticket->total_invested_hours);
            $ticket->errorPercentage = ($ticket->deviation / $ticket->total_estimate) * 100;

            $totalDeviation += $ticket->deviation;
            $totalPercentage += $ticket->errorPercentage;

            $ticket->workRatio = ($ticket->total_invested_hours / $ticket->total_estimate) * 100;

            $totalInvested += $ticket->total_invested_hours;
            $totalEstimated += $ticket->total_estimate;
        }

        $this->_avgDeviation = $totalDeviation / count($this->_completedTickets);
        $this->_avgErrorPercentage = $totalPercentage / count($this->_completedTickets);
        $this->_generalWorkRatio = ($totalInvested / $totalEstimated) * 100;
    }


    /**
     * Verify if the received ticket is in the exception
     * list (if it was defined)
     * @param $ticket
     */
    protected function ticketIsInExceptionList($ticket)
    {
        $isInList = false;

        if (is_array($this->_exceptionsList)) {

            if(in_array($ticket->number, $this->_exceptionsList)){
                $isInList = true;
                $this->_excludedTickets[] = $ticket;
            }
        }

        return $isInList;
    }

} 