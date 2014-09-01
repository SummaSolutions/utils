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


    function getPerceivedDeviation()
    {

        return $this->getTotalInvestedHours() - $this->getTotalEstimatedHours();

    }

    function getPerceivedError()
    {

        if($this->getTotalEstimatedHours() > 0){

            return ($this->getPerceivedDeviation() / $this->getTotalEstimatedHours()) * 100;
        }
        else{
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

            if( in_array($ticket->hierarchy_type, $planLevels)){

                $this->processTicket(
                    $from,
                    $to,
                    $ticket,
                    is_null($ticket->completed_date));
            }
        }
    }

    /**
     * Verify if this particular ticket needs to be included in the report.
     * @param $from
     * @param $to
     * @param $ticket
     */
    private function processTicket($from, $to, $ticket, $isPending = false)
    {

        if( $isPending){
            $this->processPending($from, $to, $ticket);
        }
        else{
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
    private function processCompleted($from, $to, $ticket){

        $ticketDate = strtotime($ticket->completed_date);
        $fromDate = strtotime($from);
        $toDate = strtotime($to);

        if ( ($ticketDate >= $fromDate && $ticketDate <= $toDate) &&
            !$this->ticketIsInExceptionList($ticket) &&
            $this->isTicketRelatedToProperUsers($ticket)
        ) {
            $this->_completedTickets[] = $ticket;
        }
    }

    /**
     * Pending tickets. Consider the creation date, and the user.
     * @param $from
     * @param $to
     * @param $ticket
     */
    private function processPending($from, $to, $ticket){

        $ticketDate =  strtotime($ticket->created_on);
        $fromDate = strtotime($from);
        $toDate = strtotime($to);

        if( ($ticketDate >= $fromDate && $ticketDate <= $toDate) &&
            $this->isTicketRelatedToProperUsers($ticket)){

            $this->_pendingTickets[] = $ticket;
        }
    }

    /**
     * Verify that the ticket includes at least one of the
     * users defined to watch.
     */
    private function isTicketRelatedToProperUsers($ticket)
    {
        if( is_null($this->_users))
        {
            return true;
        }

        $comments = json_decode($this->_conn->getTicketComments($ticket->number));

        foreach ($comments as $comment)
        {
            if( in_array($comment->user_id, $this->_users))
            {
                return true;
            }
        }

        return false;
    }


} 