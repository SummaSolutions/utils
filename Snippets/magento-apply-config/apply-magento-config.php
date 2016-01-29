<?php
$options = getopt('f:n::r::');
$filename = !empty($options['f']) ? $options['f'] : null;
$n98path = !empty($options['n']) ? $options['n'] : 'n98-magerun.phar';
$magentoRoot = !empty($options['r']) ? $options['r'] : '';

if (empty($filename) || !file_exists($filename)) {
    echo "ERROR: Invalid file\n";
    exit(1);
}

if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ',', '"')) !== FALSE) {
        list($path, $value, $action, $scope, $scopeId) = $data;

        $params = [];
        $n98cmd = "";
        switch ($action) {
            case 'set':
                $n98cmd = 'config:set';
                $params[] = $path;
                $params[] = '"'.$value.'"';
                break;
            case 'delete':
                $n98cmd = 'config:delete';
                $params[] = $path;
                break;
        }

        if(empty($n98cmd)){
            continue;
        }

        if (!empty($magentoRoot)) {
            $params[] = '--root-dir=' . $magentoRoot;
        }

        if (in_array($scope, ['websites', 'stores'])) {
            $params[] = '--scope=' . $scope;
            $params[] = '--scope-id=' . $scopeId;
        }


        $command = "php " . $n98path . " " . $n98cmd . " ". implode(' ', $params);

        $return = shell_exec($command);
        echo $return;
    }
    fclose($handle);
}
exit(0);