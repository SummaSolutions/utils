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
    $arrResullt = explode(' ',$serialized);
    if ($arrResullt[0] == '[ERROR]'){
        echo $serialized;
    }
    $analyzer = unserialize($serialized);
    unlink('../parameters/' . $fileName);
    include '../Flow/results.php';
}

?>