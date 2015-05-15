<?php
require_once("../Milestone/MilestoneAnalyzer.php");
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
    if (current(explode(' ',$serialized)) == '[ERROR]'){
        echo $serialized;
    }
    $analyzer = unserialize($serialized);
    unlink('../parameters/' . $fileName);
    include '../Milestone/results.php';
}

?>