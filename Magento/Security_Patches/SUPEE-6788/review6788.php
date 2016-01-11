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
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47'
    );

    /**
     * Default whitelist entries. Used if not able to load from DB.
     *
     * @var array
     */
    protected static $varsWhitelist = array(
        'web/unsecure/base_url',
        'web/secure/base_url',
        'trans_email/ident_general/name',
        'trans_email/ident_general/email',
        'trans_email/ident_sales/name',
        'trans_email/ident_sales/email',
        'trans_email/ident_support/name',
        'trans_email/ident_support/email',
        'trans_email/ident_custom1/name',
        'trans_email/ident_custom1/email',
        'trans_email/ident_custom2/name',
        'trans_email/ident_custom2/email',
        'general/store_information/name',
        'general/store_information/phone',
        'general/store_information/address',
    );

    protected static $blocksWhitelist = array(
        'core/template',
        'catalog/product_new',
        'enterprise_catalogevent/event_lister',
    );

    const CMS_CONTENT_TYPE_BLOCK_DB   = 'Static block';
    const CMS_CONTENT_TYPE_PAGE_DB    = 'Cms page';
    const CMS_CONTENT_TYPE_EMAIL_DB   = 'Email template in database';
    const CMS_CONTENT_TYPE_EMAIL_FILE = 'Email template in file';

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
            $dumpContent = $this->getArg('dump');

            $allChecks = array('queries', 'routes', 'content');
            $checks = $checks === true || $checks === 'all' ? $allChecks : explode(',', $checks);

            if (!is_array($checks)) {
                $checks = array($checks);
            }

            if ($dumpContent === true) {
                if (count($checks) === 1 && current($checks) === 'content') {
                    echo $this->checkContent('sql');
                } else {
                    echo $this->getColoredString("The option --dump can only be used with --check content.\n", 'red');
                }
            } else {

                foreach ($checks as $check) {
                    switch ($check) {
                        case 'queries':
                            echo $this->checkQueries();
                            break;

                        case 'routes':
                            echo $this->checkRoutes();
                            break;

                        case 'content':
                            echo $this->checkContent();
                            break;

                        default:
                            echo $this->getColoredString("Unknown check type.\n", 'red');
                            break;
                    }
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

            $return = $this->formatFileFindings($files, 'Line Number');
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

            $return = $this->formatFileFindings($files, 'Line Number');
        }

        return $return;
    }

    public function checkContent($format = 'table')
    {
        $resource = Mage::getSingleton('core/resource');
        $db       = $resource->getConnection('core_read');
        $result   = array();

        $this->loadWhitelists();

        $sql = "SELECT %s FROM %s WHERE %s LIKE '%%{{config %%' OR  %s LIKE '%%{{block %%'";


        // Blocks in the database
        $cmsBlockTable = $resource->getTableName('cms/block');
        $rows = $db->fetchAll(sprintf($sql, 'block_id as id, content, "cms_block" as content_type, identifier', $cmsBlockTable, 'content', 'content'));
        $this->check($rows, 'content', self::CMS_CONTENT_TYPE_BLOCK_DB, $result);


        // Pages in the database
        $cmsPageTable  = $resource->getTableName('cms/page');
        $rows = $db->fetchAll(sprintf($sql, 'page_id as id, content, "cms_page" as content_type, identifier', $cmsPageTable, 'content', 'content'));
        $this->check($rows, 'content', self::CMS_CONTENT_TYPE_PAGE_DB, $result);


        // Email templates in the database
        $emailTemplate = $resource->getTableName('core/email_template');
        $rows = $db->fetchAll(sprintf($sql, 'template_id as id, template_text, "core_email_template" as content_type, template_code as identifier', $emailTemplate, 'template_text', 'template_text'));
        $this->check($rows, 'template_text', self::CMS_CONTENT_TYPE_EMAIL_DB, $result);


        // Email templates in the locale directory
        $localeDir = Mage::getBaseDir('locale');
        $scan      = scandir($localeDir);
        $this->walkDir($scan, $localeDir, $result);


        return $format === 'sql' ? $this->dumpPermissions($result) : $this->formatFileFindings($result, 'Ocurrence');
    }

    /**
     * Initialize: Load whitelist entries from the database if possible.
     */
    protected function loadWhitelists()
    {
        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read     = $this->_resource->getConnection('core_read');
        $this->_write    = $this->_resource->getConnection('core_write');

        try {
            $this->_blocksTable	= $this->_resource->getTableName('admin/permission_block');
            if( $this->_read->isTableExists( $this->_blocksTable ) )
            {
                $this->blocksWhitelist = array();

                $sql         = "SELECT * FROM " . $this->_blocksTable . " WHERE is_allowed=1";
                $permissions = $this->_read->fetchAll( $sql );
                foreach( $permissions as $permission ) {
                    $this->blocksWhitelist[] = $permission['block_name'];
                }
            }
            else {
                $this->_blocksTable	= null;
            }
        }
        catch( Exception $e ) {
            $this->_blocksTable	= null;
        }

        try {
            $this->_varsTable		= $this->_resource->getTableName('admin/permission_variable');
            if( $this->_read->isTableExists( $this->_varsTable ) )
            {
                $this->varsWhitelist = array();

                $sql				= "SELECT * FROM " . $this->_varsTable . " WHERE is_allowed=1";
                $permissions		= $this->_read->fetchAll( $sql );
                foreach( $permissions as $permission ) {
                    $this->varsWhitelist[] = $permission['variable_name'];
                }
            }
            else {
                $this->_varsTable	= null;
            }
        }
        catch( Exception $e ) {
            // Exception means the whitelist doesn't exist yet, or we otherwise failed to read it in. That's okay. Move on.
            $this->_varsTable	= null;
        }
    }

    protected function check($rows, $field = 'content', $contentType, &$result)
    {
        if ($rows) {
            $blockMatch = '/{{block[^}]*?type=["\'](.*?)["\']/i';
            $varMatch   = '/{{config[^}]*?path=["\'](.*?)["\']/i';

            foreach ($rows as $res) {
                $target = ($field === null) ? $res: $res[$field];

                if (preg_match_all($blockMatch, $target, $matches)) {
                    foreach ($matches[1] as $match) {
                        if (!in_array($match, self::$blocksWhitelist)) {
                            $result[$contentType . ': ' . $res['identifier'] . ' (' . $res['id'] . ')'][] = 'block: ' . $match;
                        }
                    }
                }

                if (preg_match_all($varMatch, $target, $matches)) {
                    foreach ($matches[1] as $match) {
                        if (!in_array($match, self::$varsWhitelist)) {
                            $result[$contentType . ': ' . $res['identifier'] . ' (' . $res['id'] . ')'][] = 'variable: ' . $match;
                        }
                    }
                }
            }
        }
    }

    protected function walkDir(array $dir, $path = '', &$result)
    {
        foreach ($dir as $subdir) {
            if (strpos($subdir, '.') !== 0) {
                if(is_dir($path . DS . $subdir)) {
                    $this->walkDir(scandir($path . DS . $subdir), $path . DS . $subdir, $result);
                } elseif (is_file($path . DS . $subdir) && pathinfo($subdir, PATHINFO_EXTENSION) !== 'csv') {
                    $file = array(array(
                        'id'      => $path . DS . $subdir,
                        'content' => file_get_contents($path . DS . $subdir),
                    ) );
                    $this->check($file, 'content', self::CMS_CONTENT_TYPE_EMAIL_FILE, $result);
                }
            }
        }
    }

    public function formatFileFindings($files, $referenceLabel)
    {
        $return = '';
        $findingsCount = $filesCount = 0;
        foreach ($files as $fileName => $findings) {
            $return .= $this->getColoredString("-------------------------------------------------------------------------\n", 'white');
            $return .= $this->getColoredString("ELEMENT: ". $fileName."\n", 'white');
            $line = '---------------+--------------------------------------------------------+' . "\n";
            $return .= $this->getColoredString($line, 'white');
            $return .= $this->getColoredString(sprintf('%-20s|', $referenceLabel), 'white');
            $return .= $this->getColoredString(sprintf(' %-50s|', 'Content'), 'white');
            $return .= "\n";
            $return .= $this->getColoredString($line, 'white');
            foreach ($findings as $lineNumber => $code) {
                $return .= $this->getColoredString(sprintf('%-20s|', $lineNumber), 'white');
                $return .= $this->getColoredString(sprintf(' %-50s|', (strlen($code) > 46 ? substr($code, 0, 46).'...' : $code)), 'white');
                $return .= "\n";

                $findingsCount++;
            }

            $return .= $this->getColoredString($line, 'white');
            $return .= "\n\n";

            $filesCount++;
        }

        $return .= $this->getColoredString("-------------------------------------------------------------------------\n", 'red');
        $return .= $this->getColoredString(sprintf("Found %d potential issues in %d different files. PLEASE REVIEW THEM\n", $findingsCount, $filesCount), 'red');
        $return .= $this->getColoredString("-------------------------------------------------------------------------\n", 'red');

        return $return;
    }

    public function dumpPermissions($elements)
    {
        $result = '';
        $sqlTemplate = 'INSERT IGNORE INTO permission_%s (%s_name, is_allowed) VALUES ("%s", 1);';

        foreach ($elements as $elementGroup) {
            foreach ($elementGroup as $element) {
                list($contentType, $elementName) = explode(': ', $element);
                $sql = sprintf($sqlTemplate, $contentType, $contentType, $elementName);
                $result .= $sql . PHP_EOL;
            }
        }

        return $result;
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
