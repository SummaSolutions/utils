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

require_once('../core/assembla.php');
require_once('../core/DataCollector.php');
require_once('MilestoneIndicators.php');

class MilestoneAnalyzer
{
    private $_completeTickets;
    private $_incompleteTickets;
    private $_deferredTickets;
    private $_milestone;
    private $_exceptionsList;
    private $_completedStates;
    private $_indicators;

    function __construct($key, $secret, $space, $completedStates)
    {
        $this->conn = new AssemblaConnector($key, $secret, $space);
        $this->dataCollector = new DataCollector($key, $secret, $space);
        $this->_completedStates = $completedStates;
        $this->_indicators = new MilestoneIndicators();

        $this->_completeTickets = array();
        $this->_incompleteTickets = array();
        $this->_deferredTickets = array();
    }

    public function getMilestone(){
        return $this->_milestone;
    }

    public function getCompleteTickets(){
        return $this->_completeTickets;
    }

    public function getIncompleteTickets(){
        return $this->_incompleteTickets;
    }

    public function getDeferredTickets(){
        return $this->_deferredTickets;
    }

    public function getIndicators(){
        return $this->_indicators;
    }

    /**
     * Analize the requested milestone
     * @param $targetMilestoneID
     */
    public function AnalyzeMilestone($targetMilestoneID, $exceptionsList = null)
    {
        $this->_exceptionsList = $exceptionsList;
        $this->_milestone = json_decode($this->conn->getMilestone($targetMilestoneID));
        $this->analyzeTickets($targetMilestoneID);
        $this->calculateResults();

    }

    /**
     * Calculate the results of the milestone
     */
    private function calculateResults(){

        $this->_indicators->totalCompleted = count($this->_completeTickets);
        $this->_indicators->totalIncomplete = count($this->_incompleteTickets) + count($this->_deferredTickets);

        $this->_indicators->ticketsTotal = $this->_indicators->totalCompleted + $this->_indicators->totalIncomplete;

        $this->_indicators->completedPercentage =
            ($this->_indicators->totalCompleted / $this->_indicators->ticketsTotal) * 100;

        $this->_indicators->incompletePercentage =
            ($this->_indicators->totalIncomplete / $this->_indicators->ticketsTotal) * 100;
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
     * Verify of this ticket, that is in this sprint, needs to be counted as good or as bad.
     *
     * @param $ticket
     */
    private function processTicketInMilestone($ticket)
    {
        if ($this->ticketIsComplete($ticket)) {
            $this->_completeTickets[] = $ticket;

        } else {
            $this->_incompleteTickets[] = $ticket;
        }
    }

    /**
     * Verify if the ticket, which is not in the sprint, was in it sometime.
     * @param $ticket
     */
    private function processTicketNotInMilestone($ticket)
    {
        if (!$this->ticketIsInExceptionList($ticket) &&
            $this->ticketDidBelongToMilestone($ticket)
        ) {

            $this->_deferredTickets[] = $ticket;
        }
    }

    /**
     * Verify if the received ticket is in the exception
     * list (if it was defined)
     * @param $ticket
     */
    private function ticketIsInExceptionList($ticket)
    {
        $result = false;

        if (is_array($this->_exceptionsList)) {
            $result = in_array($ticket->number, $this->_exceptionsList);
        }

        return $result;
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
        $comments = json_decode($this->conn->getTicketComments($ticket->number));

        foreach ($comments as $comment) {

            if (strpos($comment->ticket_changes, MILESTONE_MARKUP) !== false &&
                strpos($comment->ticket_changes, $this->_milestone->title) !== false
            ) {
                // This matched the milestone markup and has the milestone in that line too. Assuming it
                // really belonged to the target milestone.
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param $targetMilestoneID
     */
    public function analyzeTickets($targetMilestoneID)
    {
        // Fetch all tickets for the space.
        $tickets = $this->dataCollector->getAllTickets();

        // Check each ticket.
        foreach ($tickets as $ticket) {
            if ($ticket->milestone_id == $targetMilestoneID) {
                $this->processTicketInMilestone($ticket);

            } else if ($ticket->milestone_id > $targetMilestoneID) {
                $this->processTicketNotInMilestone($ticket);
            }
        }
    }

}