<?php

require_once('AssemblaConnector.php');
require_once('StatusHandler.php');

class DataCollector
{

    private $conn;
    private $stHandler;
    private $respondedStatuses;
    private $startedStatuses;
    private $completedStatuses;

    function __construct($key, $secret, $space)
    {

        $this->conn = new AssemblaConnector($key, $secret, $space);
        $this->stHandler = new StatusHandler($this->conn);
    }

    public function initialize($responded, $started, $completed)
    {

        $this->respondedStatuses = $responded;
        $this->startedStatuses = $started;
        $this->completedStatuses = $completed;

        $this->stHandler->initialize();
    }

    public function getTicketsCreatedInRange($startDate, $endDate)
    {
        $from = new DateTime($startDate);
        $to = new DateTime($endDate);
        $tickets = array();

        $list = $this->getAllTickets();

        foreach ($list as $ticket) {
            $item = $this->checkTicketInclusion($ticket, $from, $to);
            if (!is_null($item)) {
                $tickets[$item->number] = $item;
            }
        }

        ksort($tickets);

        return $tickets;
    }





    private function checkTicketInclusion($ticket, $from, $to)
    {

        $item = null;

        $creationDate = new DateTime($ticket->created_on);

        if ($ticket->completed_date == "") {
            $completionDate = null;
        } else {
            $completionDate = new DateTime($ticket->completed_date);
        }

        $getIt = ($creationDate >= $from && $creationDate <= $to) ||
            ((is_null($completionDate) && ($creationDate <= $to)) ||
                ($completionDate >= $from && $completionDate <= $to));

        if ($getIt) {

            $item = $this->getTicketSummary($ticket);
            $this->calculateItemMetrics($item, $from);

        }

        return $item;
    }

    private function calculateItemMetrics($item, $from)
    {

        if ($item->responded != '') {
            $item->RespondedTime = $this->dateDiff($item->responded, $item->created);
        } else {
            $item->RespondedTime = '';
        }

        if ($item->started != '') {
            $item->startedTime = $this->dateDiff($item->started, $item->created);
        } else {
            $item->startedTime = '';
        }

        if ($item->completed != '') {
            $item->completedTime = $this->dateDiff($item->completed, $item->created);
            $item->draggingTime = "";
        } else {
            $item->completedTime = '';
            $item->draggingTime = $this->dateDiff(time(), $item->created);
        }

        $creation = new DateTime($item->created);
        $item->fromPreviousPeriod = ($creation < $from);
    }


    public function getAllTickets()
    {

        $allFetched = false;
        $tickets = array();

        $pageSize = 100;
        $page = 0;

        while (!$allFetched) {
            $pageTickets = $this->conn->getTickets($page, $pageSize);
            if ($pageTickets == "") {
                $allFetched = true;
            } else {
                $tickets = array_merge($tickets, json_decode($pageTickets));
                $page++;
            }
        }

        $tickets = $this->clearRepeated($tickets);

        // Sort tickets by number.
        usort(
            $tickets,
            function ($a, $b) {
                return $a->number - $b->number;
            }
        );

        return $tickets;
    }

    /**
     * Make a new array without repetition
     * @param $tickets
     * @return array
     */
    private function clearRepeated($tickets)
    {

        $clean = array();
        foreach ($tickets as $ticket) {
            $clean[$ticket->id] = $ticket;
        }
        return $clean;
    }

    public function getMilestones()
    {

        $data = $this->conn->getMilestones(0, 1000);

        return json_decode($data);
    }

    private function getTicketSummary($ticket)
    {

        $changes = $this->stHandler->getStatusHistory($ticket->number);

        $summary = new stdClass();
        $summary->number = $ticket->number;
        $summary->summary = $ticket->summary;
        $summary->created = $ticket->created_on;
        $summary->responded = $this->getFirstDate($changes, $this->respondedStatuses);
        $summary->started = $this->getFirstDate($changes, $this->startedStatuses);

        if (!is_null($ticket->completed_date)) {
            $summary->completed = $ticket->completed_date;
        } else {
            $summary->completed = $this->getFirstDate($changes, $this->completedStatuses);
        }

        return $summary;
    }


    public function getTicketFromNumber($number)
    {

        $ticket = json_decode($this->conn->getTicket($number));
        return $this->getTicketSummary($ticket);
    }

    private function getFirstDate($changes, $statuses)
    {

        $date = '';
        $found = false;
        $index = 0;

        while (!$found &&
            $index < count($statuses)) {

            $status = $statuses[$index++];
            $ocurrence = $this->getFirstOccurrence($status, $changes);

            if (!is_null($ocurrence)) {
                $date = $ocurrence->date;
                $found = true;
            }
        }

        return $date;
    }

    private function getFirstOccurrence($status, $changes)
    {

        $occurrence = null;

        foreach ($changes as $change) {

            if ($change->status_to == $status) {
                $occurrence = $change;
                break;
            }
        }

        return $occurrence;
    }


    private function dateDiff($time1, $time2)
    {

        if (!is_int($time1)) {
            $time1 = strtotime($time1);
        }
        if (!is_int($time2)) {
            $time2 = strtotime($time2);
        }

        $difference = $time1 - $time2;
        $hours = $difference / 3600; // 3600 seconds in an hour
        $minutes = ($hours - floor($hours)) * 60;
        $final_hours = round($hours, 0);
        $final_minutes = round($minutes);

        return sprintf('%02d', $final_hours) . ':' . sprintf('%02d', $final_minutes);

    }


}