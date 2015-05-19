<?php
require_once("../Milestone/MilestoneAnalyzer.php");
require_once("../core/misc.php");
$fileName = $_POST['file'];

if (!file_exists('../parameters/' . $fileName)) {

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