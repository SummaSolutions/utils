<?php

require_once 'abstract.php';

class Mage_Shell_Review6788
    extends Mage_Shell_Abstract
{

    protected $foregroundColors = array(
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37'
    );

    protected $backgroundColors = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47'
    );

    public function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $coloredString = "";

        // Check if given foreground color found
        if (isset($this->foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
        }
        // Check if given background color found
        if (isset($this->backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
        }

        // Add string and end coloring
        $coloredString .= $string . "\033[0m";

        return $coloredString;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('check')) {
            $checks = $this->getArg('check');
            $checks = !empty($checks) ? explode(',', $checks) : 'queries,routes';
            if (!is_array($checks)) {
                $checks = array($checks);
            }

            foreach ($checks as $check) {
                switch ($check) {
                    case 'queries':
                        echo $this->checkQueries();
                        break;

                    case 'routes':
                        echo $this->checkRoutes();
                        break;

                    default:
                        echo $this->getColoredString("Unknown check type.\n", 'red');
                        break;
                }
            }
        } else {
            echo $this->usageHelp();
        }
    }

    public function checkQueries()
    {
        $params = array(
            '--regexp="->\s*add(?:Field|Attribute)ToFilter\(\s*[^,;]*\s*[^;,\s\w.\'\"]\s*[^,;]*(?:,[^;]*\s*)?\)\s*;"',
            '-R',
            '-P',
            '-n'
        );
        $path = Mage::getBaseDir();
        $result = shell_exec('grep ' . implode(' ', $params) . ' ' . $path);
        if (empty($result)) {
            $return = $this->getColoredString("No problematic queries were found. Good Job!\n", 'green');
        }else{
            $lines = explode("\n", $result);
            $files = array();
            foreach($lines as $line){
                $line = trim($line);
                if(!empty($line)) {
                    $data = explode(':', $line);
                    $fileName = trim($data[0]);
                    $lineNumber = trim($data[1]);
                    $code = trim($data[2]);

                    if (!isset($fileName[$fileName])) {
                        $files[$fileName] = array();
                    }

                    $files[$fileName][$lineNumber] = $code;
                }
            }

            $return = $this->formatFileFindings($files);
        }

        return $return;
    }

    public function checkRoutes()
    {
        $params = array(
            '--regexp="<use>admin</use>"',
            '--include="*.xml"',
            '-R',
            '-P',
            '-n'
        );
        $basePath = Mage::getBaseDir('code');
        $communityPath = $basePath . DS . 'community';
        $localPath = $basePath . DS . 'local';
        $result = shell_exec('grep ' . implode(' ', $params) . ' ' . $communityPath);
        $result .= shell_exec('grep ' . implode(' ', $params) . ' ' . $localPath);
        if (empty($result)) {
            $return = $this->getColoredString("No problematic admin routes. Good Job!\n", 'green');
        }else{
            $lines = explode("\n", $result);
            $files = array();
            foreach($lines as $line){
                $line = trim($line);
                if(!empty($line)) {
                    $data = explode(':', $line);
                    $fileName = trim($data[0]);
                    $lineNumber = trim($data[1]);
                    $code = trim($data[2]);

                    if (!isset($fileName[$fileName])) {
                        $files[$fileName] = array();
                    }

                    $files[$fileName][$lineNumber] = $code;
                }
            }

            $return = $this->formatFileFindings($files);
        }

        return $return;
    }

    public function formatFileFindings($files)
    {
        $return = '';
        $findingsCount = $filesCount = 0;
        foreach($files as $fileName => $findings){
            $return .= $this->getColoredString("-------------------------------------------------------------------\n", 'white');
            $return .= $this->getColoredString("FILE: ". $fileName."\n", 'white');
            $line = '---------------+--------------------------------------------------+' . "\n";
            $return .= $this->getColoredString($line, 'white');
            $return .= $this->getColoredString(sprintf('%-15s|', 'Line Number'), 'white');
            $return .= $this->getColoredString(sprintf(' %-50s|', 'Code'), 'white');
            $return .= "\n";
            $return .= $this->getColoredString($line, 'white');
            foreach($findings as $lineNumber=>$code){
                $return .= $this->getColoredString(sprintf('%-15s|', $lineNumber), 'white');
                $return .= $this->getColoredString(sprintf(' %-50s|', (strlen($code) > 46 ? substr($code, 0, 46).' ...' : $code)), 'white');
                $return .= "\n";

                $findingsCount++;
            }

            $return .= $this->getColoredString($line, 'white');
            $return .= "\n\n";

            $filesCount++;
        }

        $return .= $this->getColoredString("-------------------------------------------------------------------\n", 'red');
        $return .= $this->getColoredString(sprintf("Found %d potential issues in %d different files. PLEASE REVIEW THEM\n", $findingsCount, $filesCount), 'red');
        $return .= $this->getColoredString("-------------------------------------------------------------------\n", 'red');

        return $return;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f review6788.php -- [options]
        php -f review6788.php --check queries,routes

  check <types>     Checks for potential issues. Possible types: queries|routes|all
  help              This help

USAGE;
    }

}

$shell = new Mage_Shell_Review6788();
$shell->run();
