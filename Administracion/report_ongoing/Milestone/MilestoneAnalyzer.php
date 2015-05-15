<?php

/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/6/13
 * Time: 4:51 PM
 *
 *
 *
 * Pedir sprint a analizar.
 *
 * Recorrer todos los tickets ->
 *
 * Pertenece al sprint
 * Está terminado -> suma del lado de los buenos.
 * No está terminado -> suma del lado de los malos.
 *
 * No pertenece al sprint
 * Perteneció alguna vez?
 * Si => ¿está en lista de excepciones?
 * => si -> no cuenta.
 * => no -> suma del lado de los malos.
 * No => no cuenta.
 *
 */
define('MILESTONE_MARKUP', "\n- - milestone_id\n");

require_once('../core/TicketsAnalyzer.php');
require_once('../core/StatusHandler.php');
require_once('MilestoneIndicators.php');

class MilestoneAnalyzer extends TicketsAnalyzer
{
    private $_incompleteTickets;
    private $_deferredTickets;
    private $_milestone;
    private $_completedStates;
    private $_indicators;

    private $from;

    private $to;

    function getFrom(){
        return $this->from;
    }

    function getTo(){
        return $this->to;
    }

    function __construct($key, $secret, $space, $completedStates, $exceptions)
    {
        parent::__construct($key, $secret, $space, $exceptions);

        $this->_completedStates = $completedStates;
        $this->_indicators = new MilestoneIndicators();

        $this->_completedTickets = array();
        $this->_incompleteTickets = array();
        $this->_deferredTickets = array();
    }

    public function getMilestone()
    {
        return $this->_milestone;
    }

    public function getIncompleteTickets()
    {
        return $this->_incompleteTickets;
    }

    function getIncompleteTicketsInvestedHours()
    {
        $total = 0;
        foreach ($this->_incompleteTickets as $ticket) {

            $total += $ticket->total_invested_hours;

        }

        return $total;

    }



    public function getDeferredTickets()
    {
        return $this->_deferredTickets;
    }

    public function getIndicators()
    {
        return $this->_indicators;
    }

    /**
     * Analize the requested milestone
     * @param $targetMilestoneID
     */
    public function AnalyzeMilestone($targetMilestoneID, $planLevels)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $this->_milestone = json_decode($this->_conn->getMilestone($targetMilestoneID));
        $this->analyzeTickets($targetMilestoneID, $planLevels);
        $this->calculateResults();
        $this->calculateTicketResults();
    }


    public function calculateTicketResults(){

        parent::calculateTicketResults();

        $stHandler = new StatusHandler($this->_conn);
        $stHandler->initialize();

        foreach($this->_completedTickets as $ticket){

            if( is_null($ticket->completed_date)){

                $ticket->completed_date = $stHandler->getCurrentStatusDate($ticket);
            }
        }

    }


    /**
     * @param $targetMilestoneID
     */
    public function analyzeTickets($targetMilestoneID, $planLevels)
    {
        // Fetch all tickets for the space.
        $tickets = $this->_dataCollector->getAllTickets();

        // Check each ticket.
        foreach ($tickets as $ticket) {

            if( in_array($ticket->hierarchy_type, $planLevels)){


                // Adjust ticket estimation.
                // If it is task or no plan, copy the estimate
                // to the total estimate.
                if( $ticket->hierarchy_type <= 1){
                    $ticket->total_estimate = $ticket->estimate;
                }
                

                if (!$this->ticketIsInExceptionList($ticket)) {

                    if ($ticket->milestone_id == $targetMilestoneID) {
                        $this->processTicketInMilestone($ticket);

                    } else {
                        $this->processTicketNotInMilestone($ticket);
                    }
                }
            }
        }
    }

    /**
     * Verify of this ticket, that is in this sprint, needs to be counted as good or as bad.
     *
     * @param $ticket
     */
    private function processTicketInMilestone($ticket)
    {
        $this->calculateFromRelated($ticket);

        if ($this->ticketIsComplete($ticket)) {

            $this->_completedTickets[] = $ticket;

        } else {

            $this->_incompleteTickets[] = $ticket;
        }
    }

    /**
     * Check whether the ticket is complete or not.
     * @param $ticket
     */
    private function ticketIsComplete($ticket)
    {
        return in_array($ticket->status, $this->_completedStates);
    }

    /**
     * Verify if the ticket, which is not in the sprint, was in it sometime.
     * @param $ticket
     */
    private function processTicketNotInMilestone($ticket)
    {
        if ($this->ticketDidBelongToMilestone($ticket)
        ) {
            $this->_deferredTickets[] = $ticket;
        }
    }

    /**
     * Check if the ticket did belong to the milestone once.
     *
     * @param $ticket
     * @return bool
     */
    private function ticketDidBelongToMilestone($ticket)
    {
        $result = false;

        // Get the ticket comments.
        $comments = json_decode($this->_conn->getTicketComments($ticket->number));

        if( !isset($comments)){
            return false;
        }

        foreach ($comments as $comment) {

            if (strpos($comment->ticket_changes, MILESTONE_MARKUP) !== false &&
                strpos($comment->ticket_changes, $this->_milestone->title) !== false
            ) {
                // This matched the milestone markup and has the milestone in that line too. Assuming it
                // really belonged to the target milestone.
                $result = true;
                $ticketMilestone = json_decode($this->_conn->getMilestone($ticket->milestone_id));
                $ticket->currentMilestone = $ticketMilestone->title;

                break;
            }
        }

        return $result;
    }

    /**
     * Calculate the results of the milestone
     */
    private function calculateResults()
    {

        $this->_indicators->totalCompleted = count($this->_completedTickets);
        $this->_indicators->totalIncomplete = count($this->_incompleteTickets) + count($this->_deferredTickets);

        $this->_indicators->ticketsTotal = $this->_indicators->totalCompleted + $this->_indicators->totalIncomplete;

        $this->_indicators->completedPercentage =
            ($this->_indicators->totalCompleted / $this->_indicators->ticketsTotal) * 100;

        $this->_indicators->incompletePercentage =
            ($this->_indicators->totalIncomplete / $this->_indicators->ticketsTotal) * 100;
    }

}