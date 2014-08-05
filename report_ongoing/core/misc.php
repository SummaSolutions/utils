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
    else if( isset($ticket->estimate) && $ticket->estimate == 0){
        $output = ' bgcolor = "#FFCC00" title="No estimation!!" ';
    }
    else if(isset($ticket->deviation) && $ticket->deviation == 0){
        $output = ' bgcolor = "#CCFF00" title="No deviation :)" ';
    }

    return $output;
}


