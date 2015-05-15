<?php
require_once("../Flow/FLowAnalyzer.php");
require_once("../core/misc.php");
$fileName = $_POST['file'];

if (!file_exists('../parameters/' . $fileName)) {
    $result['error'] = 'true';
    echo 'false';
}else{
    $serialized = file_get_contents('../parameters/' . $fileName);
    $arrResullt = explode(' ',$serialized);
    if ($arrResullt[0] == '[ERROR]'){
        echo $serialized;
    }
    $analyzer = unserialize($serialized);
    unlink('../parameters/' . $fileName);
    include '../Flow/results.php';
}

?>