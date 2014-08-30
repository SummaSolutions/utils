<?php

function format_date($date)
{

    if ($date != '') {
        $date = new DateTime($date);
        return $date->format('m/d/Y H:i:s');
    } else {
        return $date;
    }
}

function alterTr($ticket)
{
    $output = '';

    if (isset($ticket->draggingTime) && $ticket->draggingTime != '') {
        $output = 'bgcolor = "#EDBFAD"';

    } elseif (isset($ticket->fromPreviousPeriod) && $ticket->fromPreviousPeriod) {
        $output = 'bgcolor = "#F2F255"';
    }
    else if( isset($ticket->isEpic) && $ticket->isEpic){
        $output = ' bgcolor = "#BEE6CD" title="This is an Epic" ';
    }
    else if( isset($ticket->total_estimate) && $ticket->total_estimate == 0){
        $output = ' bgcolor = "#E6B1BB" title="No estimation" ';
    }
    else if(
        isset($ticket->total_estimate) &&
        isset($ticket->total_invested_hours) &&
        $ticket->total_invested_hours > $ticket->total_estimate
        )
    {
        $output = ' bgcolor = "#F5F598" title="More than estimated" ';
    }

    else if(
        isset($ticket->total_estimate) &&
        isset($ticket->total_invested_hours) &&
        $ticket->total_invested_hours < $ticket->total_estimate
    )
    {
        $output = ' bgcolor = "#D6F2CE" title="Less than estimated" ';
    }


    return $output;
}


function formatValue($value){

    if( is_numeric($value)){
        return number_format($value,2);
    }
    else{
        return $value;
    }

}

function showPlanLevel($hierarchy)
{
    $value = "";

    switch($hierarchy){

        case 0:
            $value = "No plan level";
            break;
        case 1:
            $value = "Subtask";
            break;
        case 2:
            $value = "Story";
            break;
        case 3:
            $value = "Epic";
            break;
    }

    return $value;

}