<?php

define('STATUS_MARKUP', "\n- - status\n");
define('STATUS_POSITION', "status\n");

require_once('RegexBuilder.php');
require_once('assembla.php');


class StatusHandler
{

    private $statuses;
    private $statusRegex;
    private $conn;

    public function __construct(AssemblaConnector $conn)
    {

        $this->conn = $conn;
    }

    public function initialize()
    {

        $this->fetchAllPossibleStatuses();

        $rBuilder = new RegexBuilder();
        $this->statusRegex = $rBuilder->buildStatusRegex($this->statuses);
    }

    public function getStatusHistory($number)
    {
        $comments = json_decode($this->conn->getTicketComments($number));
        $statusChanges = array();

        foreach ($comments as $comment) {

            if (strpos($comment->ticket_changes, STATUS_MARKUP) !== false &&
                preg_match($this->statusRegex, $comment->ticket_changes)
            ) {
                // This matched the status markup and the regex for statuses. Assuming it
                // really is a status change!
                $statusChanges[] = $this->extractStatus($comment);
            }
        }

        return $statusChanges;
    }

    private function fetchAllPossibleStatuses()
    {

        $this->statuses = array();
        $full_list = json_decode($this->conn->getSpaceStatuses());

        foreach ($full_list as $full_status) {
            $this->statuses[] = $full_status->name;
        }

    }

    private function extractStatus($comment)
    {

        $pieces = explode('-', $comment->ticket_changes);
        $index = $this->findStatusIndex($pieces);

        $change = new stdClass();
        $change->status_from = $this->cleanUp($pieces[$index]);
        $change->status_to = $this->cleanUp($pieces[$index + 1]);

        $change->date = $comment->created_on;

        return $change;

    }

    private function findStatusIndex($pieces)
    {

        $status = null;

        for ($i = 0; $i <= count($pieces); $i++) {
            if (strpos($pieces[$i], STATUS_POSITION) !== false) {
                $status = $i + 1;
                break;
            }
        }
        return $status;
    }

    private function cleanUp($value)
    {
        $value = str_replace("\n", "", $value);
        return trim($value);
    }

}