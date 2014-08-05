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
    protected $_pendingTickets;
    protected $_avgErrorPercentage;
    protected $_generalWorkRatio;
    protected $_excludedTickets;
    protected $_totalPonderatedDeviation;
    protected $_totalDeviation;
    protected $_totalInvestedHours;
    protected $_totalEstimatedHours;
    protected $_totalPonderation;

    const EPIC = 3;

    public function getTotalPonderation(){
        return $this->_totalPonderation;
    }

    public function getTotalEstimatedHours(){
        return $this->_totalEstimatedHours;
    }

    public function getCompletedTickets()
    {
        return $this->_completedTickets;
    }

    public function getPendingTickets()
    {
        return $this->_pendingTickets;
    }

    public function getExcludedTickets()
    {
        return $this->_excludedTickets;
    }

    public function getExceptions(){
        return $this->_exceptionsList;
    }

    public function getTotalPonderatedDeviation()
    {
        return $this->_totalPonderatedDeviation;
    }

    public function getTotalInvestedHours()
    {
        return $this->_totalInvestedHours;
    }

    public function getTotalDeviation()
    {
        return $this->_totalDeviation;
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
        $this->_pendingTickets = array();
        $this->_completedTickets = array();

    }


    function getTicketByNumber($number){
        return $this->_conn->getTicketByNumber($number);
    }

    /**
     * Calculate the indicators for the period.
     */
    protected function calculateTicketResults()
    {
        $totalInvested = 0;
        $totalEstimated = 0;
        $totalPonderatedDeviation = 0;
        $this->_totalDeviation = 0;
        $this->_totalInvestedHours = 0;
        $this->_totalPonderation = 0;
        $this->_totalEstimatedHours = 0;


        // First loop. Measure totals
        foreach ($this->_completedTickets as $ticket) {

            $this->calculate($ticket);
            $totalInvested += $ticket->total_invested_hours;
            $totalEstimated += $ticket->total_estimate;
            $this->_totalDeviation += $ticket->deviation;

            $ticketDate = strtotime($ticket->completed_date);
            $ticket->completed_date = date('d/m/Y', $ticketDate);
        }

        // Second loop. Calculate ponderations.
        foreach ($this->_completedTickets as $ticket) {

            $ticket->ponderation = ($ticket->total_invested_hours / $totalInvested) * 100;
            $this->_totalPonderation += $ticket->ponderation;
            $ticket->ponderated_deviation = ($ticket->deviation * $ticket->ponderation) / 100;
            $totalPonderatedDeviation += $ticket->ponderated_deviation;
        }

        if( count($this->_completedTickets) > 0){
            $this->_totalPonderatedDeviation = $totalPonderatedDeviation;
            $this->_generalWorkRatio = ($totalInvested / $totalEstimated) * 100;
            $this->_totalInvestedHours = $totalInvested;
            $this->_totalEstimatedHours = $totalEstimated;
        }
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

    /**
     * @param $ticket
     */
    protected function calculate($ticket)
    {
        if ($ticket->hierarchy_type != TicketsAnalyzer::EPIC) {

            $ticket->deviation = abs($ticket->estimate - $ticket->total_invested_hours);

            if($ticket->total_estimate > 0){
                $ticket->errorPercentage = ($ticket->deviation / $ticket->total_estimate) * 100;
                $ticket->workRatio = ($ticket->total_invested_hours / $ticket->estimate) * 100;
            }
            else{
                $ticket->errorPercentage = 100;
                $ticket->workRatio = 1000;
            }

            $ticket->isEpic = false;

        } else {

            $ticket->isEpic = true;
            $ticket->deviation = 0;
            $ticket->errorPercentage = 0;
            $ticket->workRatio = 0;
        }
    }

} 