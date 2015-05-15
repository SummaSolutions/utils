<?php
require_once("../Flow/FLowAnalyzer.php");
require_once("../core/misc.php");
$fileName = $_POST['file'];
//$result['error'] = 'false';
//$result['success'] = 'Satisfactorio';
//$result['html'] = '';

if (!file_exists('../parameters/' . $fileName)) {
    //mkdir('var/home/'.$subhomeName, 0777, true);
    $result['error'] = 'true';
    echo 'false';
}else{
    $serialized = file_get_contents('../parameters/' . $fileName);
    $analyzer = unserialize($serialized);
    include '../Flow/results.php';
}

?>