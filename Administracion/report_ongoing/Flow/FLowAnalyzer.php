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

    private $_users;

    private $_tags = null;

    private $from;

    private $to;

    function setTags($tags)
    {

        if (strlen(trim($tags)) == 0) {
            $this->_tags = null;
        } else {
            $this->_tags = explode(',', $tags);
        }
    }

    function getFrom(){
        return $this->from;
    }

    function getTo(){
        return $this->to;
    }

    function getPerceivedDeviation()
    {

        return $this->getTotalInvestedHours() - $this->getTotalEstimatedHours();

    }

    function getPerceivedError()
    {

        if ($this->getTotalEstimatedHours() > 0) {

            return ($this->getPerceivedDeviation() / $this->getTotalEstimatedHours()) * 100;
        } else {
            return "n/a";
        }


    }


    /**
     * Analyze the specified period.
     * @param $from
     * @param $to
     */
    function analyzePeriod($from, $to, $planLevels, $users)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $this->from = $from;
        $this->to = $to;
        $this->_users = $users;
        $this->filterTickets($from, $to, $planLevels);
        $this->calculateTicketResults();
    }

    /**
     * Filter the tickets that were completed in the period.
     *
     * @param $from
     * @param $to
     * @return array
     */
    private function filterTickets($from, $to, $planLevels)
    {
        // Fetch all tickets for the space.
        $tickets = $this->_dataCollector->getAllTickets();

        foreach ($tickets as $ticket) {

            if ($this->isTicketToBeProcessed($planLevels, $ticket)) {

                $this->processTicket(
                    $from,
                    $to,
                    $ticket,
                    is_null($ticket->completed_date));
            }
        }
    }

    /**
     * @param $planLevels
     * @param $ticket
     */
    private function isTicketToBeProcessed($planLevels, $ticket)
    {

        if (in_array($ticket->hierarchy_type, $planLevels)) {
            return true;
        }

    }


/**
 * Verify if this particular ticket needs to be included in the report.
 * @param $from
 * @param $to
 * @param $ticket
 */
private
function processTicket($from, $to, $ticket, $isPending = false)
{
    $this->calculateFromRelated($ticket);

    // Adjust ticket estimation.
    // If it is task or no plan, copy the estimate
    // to the total estimate.
    if ($ticket->hierarchy_type <= 1) {
        $ticket->total_estimate = $ticket->estimate;
    }

    if ($isPending) {
        $this->processPending($from, $to, $ticket);

    } else {
        $this->processCompleted($from, $to, $ticket);
    }
}

/**
 * Process the ticket that are completed. Consider the right date, and
 * the exception list.
 * @param $from
 * @param $to
 * @param $ticket
 */
private
function processCompleted($from, $to, $ticket)
{

    $ticketDate = strtotime($ticket->completed_date);
    $fromDate = strtotime($from . ' 00:00:00');
    $toDate = strtotime($to . ' 23:59:59');

    if (($ticketDate >= $fromDate && $ticketDate <= $toDate) &&
        !$this->ticketIsInExceptionList($ticket) &&
        $this->isTicketRelatedToProperUsers($ticket)
    ) {

        if( $this->checkTags($ticket)){
            $this->_completedTickets[] = $ticket;
        }
    }
}

/**
 * Pending tickets. Consider the creation date, and the user.
 * @param $from
 * @param $to
 * @param $ticket
 */
private
function processPending($from, $to, $ticket)
{

    $ticketDate = strtotime($ticket->created_on);
    $fromDate = strtotime($from . ' 00:00:00');
    $toDate = strtotime($to . ' 23:59:59');

    if (($ticketDate >= $fromDate && $ticketDate <= $toDate) &&
        $this->isTicketRelatedToProperUsers($ticket)
    ) {

        if( $this->checkTags($ticket)){
            $this->_pendingTickets[] = $ticket;
        }
    }
}

/**
 * Verify that the ticket includes at least one of the
 * users defined to watch.
 */
private
function isTicketRelatedToProperUsers($ticket)
{
    if (is_null($this->_users)) {
        return true;
    }

    $comments = json_decode($this->_conn->getTicketComments($ticket->number));

    foreach ($comments as $comment) {
        if (in_array($comment->user_id, $this->_users)) {
            return true;
        }
    }

    return false;
}

    /**
     * @param $ticket
     * @return bool
     */
    private function checkTags($ticket)
    {
        if (is_array($this->_tags)) {

            // tags informed
            $tags = $this->_conn->getTicketTags(1, 100, $ticket->number);
            $tags = json_decode($tags);

            if( !is_array($tags)){
                return false;
            }
            else{
                foreach ($tags as $tag) {
                    if (in_array($tag->name, $this->_tags)) {
                        return true;
                    }
                }
            }
        }
        else{
            return true;
        }

    }


} 