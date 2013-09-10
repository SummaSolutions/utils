<?php

class RegexBuilder
{

    /**
     * Based on the possible statuses, build up a regex that will match any status.
     *
     * ((?=.*?\bcat\b)|(?=.*?\bdog\b)|((?=.*\bsalvajes\b)(?=.*\bunitarios\b)(?=.*\bborrachos\b))).*$
     *
     * This would match any line containing: "cat", "dog", or "salvajes unitarios borrachos" :)
     *
     */
    public function buildStatusRegex($statuses)
    {

        $statusRegex = "";

        foreach ($statuses as $status) {
            $term = $this->buildTermRegex($status);
            if ($statusRegex == "") {
                $statusRegex .= $term;
            } else {
                $statusRegex .= '|' . $term;
            }
        }
        $statusRegex = '/(' . $statusRegex . ').*$/';

        return $statusRegex;
    }


    private function buildTermRegex($status)
    {

        $words = explode(' ', $status);

        if (count($words) == 1) {
            $termRegex = '(?=.*?\b' . $status . '\b)';
        } else {
            $termRegex = '(';
            foreach ($words as $word) {
                $termRegex .= '(?=.*\b' . $word . '\b)';
            }
            $termRegex .= ')';
        }

        return $termRegex;
    }

}