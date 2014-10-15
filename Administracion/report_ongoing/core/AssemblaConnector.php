<?php

class AssemblaConnector
{

    private $url = 'https://api.assembla.com/v1/spaces/';
    private $headers;
    private $method = 'GET';
    private $data = array();
    private $key;
    private $secret;
    private $space;


    function __construct($key, $secret, $space, $format = 'json')
    {

        $this->key = $key;
        $this->secret = $secret;
        $this->space = $space;

        $this->headers = array(
            'X-Api-Key:' . $key,
            'X-Api-Secret:' . $secret,
            'Accept: application/' . $format);
    }

    /**
     * Get the specified ticket
     * @param bool $number
     * @param bool $id
     * @return mixed
     */
    public function getTicket($number = false, $id = false)
    {
        if ($number) {
            $url = '/tickets/' . $number . '.json';
        } elseif ($id) {
            $url = '/tickets/id/' . $id . 'json';
        } else {
            $url = '/tickets';
        }
        return $this->callAssembla($url);
    }

    /**
     * Get the ticket comments
     * @param $number
     * @return mixed
     */
    public function getTicketComments($number)
    {

        $url = '/tickets/' . $number . '/ticket_comments.json';
        return $this->callAssembla($url);
    }

    /**
     * Get all the tickets belonging to a particular space.
     * @return mixed
     */
    public function getTickets($page, $pageSize)
    {

        $url = '/tickets.json?report=0&page=' . $page . '&per_page=' . $pageSize;
        return $this->callAssembla($url);
    }

    /**
     * Get all the tags for a particular ticket
     * @param $page
     * @param $pageSize
     * @param $ticket
     * @return mixed
     */
    public function getTicketTags($page, $pageSize, $ticket){

        $url = '/tickets/' . $ticket . '/tags.json?page=' . $page . '&per_page=' . $pageSize;
        $test = $this->callAssembla($url);

        return $test;

    }

    /**
     * Get the tickets related to this one.
     * @param $ticket
     * @return mixed
     */
    public function getRelatedTickets($ticket){

        $url = '/tickets/' . $ticket . '/ticket_associations';
        $test = $this->callAssembla($url);

        return $test;
    }


    /**
     * Get all time entries for a particular task
     * @param $taskID
     * @param $page
     * @param $pageSize
     * @return mixed
     */
    public function getTimeEntries($taskID, $page, $pageSize)
    {

        $url = '/tasks/' . $taskID . '/time_entries.json?page=' . $page . '&per_page=' . $pageSize;
        return $this->callAssembla($url);
    }

    /**
     * Get all the tickets for a particular milestone
     *
     * @param $milestone
     * @param $pageSize
     * @return mixed
     */
    public function getMilestoneTickets($milestone, $page, $pageSize)
    {

        $url = '/tickets/milestone/' . $milestone . 'json?page=' . $page . '&per_page=' . $pageSize . '&ticket_status=all';

        return $this->callAssembla($url);

    }

    /**
     * Get milestones.
     * @return mixed
     */
    public function getMilestones($page, $pageSize)
    {

        $url = '/milestones/all.json?page=' . $page . '&per_page=' . $pageSize;

        $milestones = $this->callAssembla($url);

        return $milestones;

    }

    public function getTicketByNumber($number)
    {

        $url = '/tickets/' . $number;

        $ticket = json_decode($this->callAssembla($url));

        return $ticket;

    }



    /**
     * Get milestones.
     * @param $id
     * @return mixed
     */
    public function getMilestone($id)
    {

        $url = '/milestones/' . $id . '.json';

        return $this->callAssembla($url);
    }

    /**
     * Get the list of statuses for this space.
     * @return mixed
     */
    public function getSpaceStatuses()
    {
        $url = '/tickets/statuses.json';
        return $this->callAssembla($url);
    }

    public function getSpaceMembers()
    {

        $url = '/users';
        $resp = $this->callAssembla($url);
        return $resp;


    }

    /**
     * Make a call to Assembla's API, utilizing cUrl and the user/key.
     * @param $url
     * @return mixed
     */
    private function callAssembla($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->url . $this->space . $url,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_TIMEOUT => 100,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 30,
        ));

        if ($this->method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        } elseif ($this->method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }
        $this->method = 'GET';

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $response = curl_exec($ch);

        return ($response);
    }
}