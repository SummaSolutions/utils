<?php

class ConsoleTable
{
    protected $_headers = array();

    protected $_data = array();

    protected $_columnMaxWidths = array();

    public function setHeaders($headers)
    {
        $this->_headers = $headers;
        foreach ($this->_headers as $columnKey => $header) {
            $this->_columnMaxWidths[$columnKey] = strlen($header);
        }
    }

    public function addRow($row)
    {
        $this->_data[] = $row;
        foreach ($this->_headers as $columnKey => $header) {
            $this->_columnMaxWidths[$columnKey] = max(strlen($row[$columnKey]), $this->_columnMaxWidths[$columnKey]);
        }
    }

    public function setData($data)
    {
        $this->_data = array();
        foreach ($data as $row) {
            $this->addRow($row);
        }
    }

    public function toString()
    {
        $strHeader = '| ';

        $dividerLine = '+-';
        foreach ($this->_headers as $columnKey => $header) {
            $strHeader .= str_pad($header, $this->_columnMaxWidths[$columnKey]) . ' | ';
            $dividerLine .= str_repeat('-', $this->_columnMaxWidths[$columnKey]) . '-+-';
        }
        $dividerLine = substr($dividerLine, 0, -1) . PHP_EOL;
        $strHeader .= PHP_EOL;

        $strTable = $dividerLine . $strHeader . $dividerLine;
        foreach ($this->_data as $row) {
            $strTable .= '| ';
            foreach ($this->_headers as $columnKey => $header) {
                $strTable .= str_pad($row[$columnKey], $this->_columnMaxWidths[$columnKey]) . ' | ';
            }
            $strTable .= PHP_EOL;
        }

        return $strTable . $dividerLine;
    }
}