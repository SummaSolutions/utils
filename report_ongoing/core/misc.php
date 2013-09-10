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
    if ($ticket->draggingTime != '') {

        $output = 'bgcolor = "#EDBFAD"';
    } elseif ($ticket->fromPreviousPeriod) {
        $output = 'bgcolor = "#F2F255"';
    }


    return $output;
}
