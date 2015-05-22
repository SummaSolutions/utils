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
    protected $_ticketsWithNoEstimation;
    protected $_space;

    const EPIC = 3;

    public function getTotalTicketsWithNoEstimation(){
        return $this->_ticketsWithNoEstimation;
    }



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

    public function getSpace(){
        return $this->_space;
    }

    function __construct($key, $secret, $space, $exceptions)
    {
        $this->_conn = new AssemblaConnector($key, $secret, $space);
        $this->_space = $space;
        $this->_dataCollector = new DataCollector($key, $secret, $space);
        $this->_exceptionsList = $exceptions;
        $this->_excludedTickets = array();
        $this->_pendingTickets = array();
        $this->_completedTickets = array();
        $this->_ticketsWithNoEstimation = 0;

    }

    function getIncompleteTicketsInvestedHours()
    {
        $total = 0;
        foreach ($this->_pendingTickets as $ticket) {

            $total += $ticket->total_invested_hours;

        }

        return $total;

    }

    function getCompleteTicketsInvestedHours()
    {
        $total = 0;
        foreach ($this->_completedTickets as $ticket) {

            $total += $ticket->total_invested_hours;

        }

        return $total;
    }


    function getTicketByNumber($number){
        return $this->_conn->getTicketByNumber($number);
    }


    /**
     * Calculate estimations and investments from the related subtasks.
     * @param $parent
     */
    protected function calculateFromRelated($parent, $tickets = null){

        if( $parent->hierarchy_type != 2){
            // Do this only for stories. All others, they remain as they are.
            return;
        }

        // Get all related tickets
        $associations = json_decode($this->_conn->getRelatedTickets($parent->number));

        if( !is_array($associations)){
            return; // Nothing to do here!
        }

        $totalEstimate = 0;
        $totalInvested = 0;

        foreach($associations as $association){

            // Are we interested in this relationship?
            // 5 & 6 are subtasks associations.
            if( $association->relationship == 6 || $association->relationship == 5 ){

                // what's the related ticket?
                $related = null;
                if( $association->ticket2_id == $parent->id ){
                    $related = $association->ticket1_id;
                }
                else{
                    $related = $association->ticket2_id;
                }

                // Get the details
                $ticket = $this->getRelatedFromTickets($tickets,$related);

                if ($ticket == false){
                    $ticket = json_decode($this->_conn->getTicket(false, $related));
                }

                // Add up the values.
                $totalEstimate += $ticket->estimate;
                $totalInvested += $ticket->total_invested_hours;

            }

       }

        $parent->estimate = $totalEstimate;
        $parent->total_estimate = $totalEstimate;
        $parent->total_invested_hours = $totalInvested;

    }

    /**
     * Check if the related is in $tickets
     */
    protected function getRelatedFromTickets($tickets,$relatedId){
        $t = false;
        if ($tickets != null){
            foreach ($tickets as $ticket){
                if ($ticket->id == $relatedId){
                    $t = $ticket;
                }
            }
        }

        return $t;
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

            if( !is_null($ticket->completed_date)){
                $ticketDate = strtotime($ticket->completed_date);
                $ticket->completed_date = date('d/m/Y', $ticketDate);
            }

        }

        // Second loop. Calculate ponderations.
        foreach ($this->_completedTickets as $ticket) {

            $ticket->ponderation = ($ticket->total_invested_hours / $totalInvested) * 100;
            $this->_totalPonderation += $ticket->ponderation;

            if( is_numeric($ticket->errorPercentage)){
                $ticket->ponderated_deviation = ($ticket->errorPercentage * $ticket->ponderation) / 100;
                $totalPonderatedDeviation += $ticket->ponderated_deviation;
            }
            else{
                $ticket->ponderated_deviation = "n/a";
            }
        }


        if( count($this->_completedTickets) > 0){

            if( is_numeric($totalPonderatedDeviation)){
                $this->_totalPonderatedDeviation = $totalPonderatedDeviation;
            }

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

            $ticket->deviation = abs($ticket->total_estimate - $ticket->total_invested_hours);

            if($ticket->total_estimate > 0){
                $ticket->errorPercentage = ($ticket->deviation / $ticket->total_estimate) * 100;
                $ticket->workRatio = ($ticket->total_invested_hours / $ticket->total_estimate) * 100;
            }
            else{
                $ticket->errorPercentage = "n/a";
                $ticket->workRatio = "n/a";
                $this->_ticketsWithNoEstimation++;
            }

            $ticket->isEpic = false;

        } else {

            $ticket->isEpic = true;
            $ticket->deviation = "n/a";
            $ticket->errorPercentage = "n/a";
            $ticket->workRatio = "n/a";
        }
    }

} 