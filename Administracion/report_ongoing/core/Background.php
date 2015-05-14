<?php
/**
 * Created by PhpStorm.
 * User: abressan
 * Date: 9/9/13
 * Time: 10:37 AM
 */
require_once("FLowAnalyzer.php");
require_once("../core/misc.php");

class Background
{

    private $key;
    private $secret;
    private $project;
    private $exceptions;
    private $skipUserValidation;
    private $users;
    private $plan;
    private $filterByTags;
    private $tags;
    private $dateTo;
    private $datefrom;
    private $file;

    //function __construct($key,$secret,$project,$exceptions,$skipUserValidation,$users,$plan,$filterByTags,$tags = null,$dateFrom,$dateTo){
    function __construct($file){
        $serialized = file_get_contents('../parameters/' . $file);
        $arrParams = unserialize($serialized);
        var_dump($arrParams);
        $this->key = $arrParams['key'];
        $this->secret = $arrParams['secret'];
        $this->project = $arrParams['project'];
        $this->exceptions = $arrParams['exceptions'];
        $this->skipUserValidation = $arrParams['skipUserValidation'];
        $this->users = $arrParams['users'];
        $this->plan = $arrParams['plan'];
        $this->filterByTags = $arrParams['filterByTags'];
        $this->tags = $arrParams['tags'];
        $this->datefrom = $arrParams['dateFrom'];
        $this->dateTo = $arrParams['dateTo'];
        $this->file = $arrParams['file'];
        file_put_contents ( 'logueo.log' , 'PLAN: '. print_r($this->plan, true). PHP_EOL,FILE_APPEND );
        file_put_contents ( 'logueo.log' , 'KEY: '. $this->key. PHP_EOL,FILE_APPEND );
    }

    public function execute(){
        if (trim($this->exceptions) != '') {
            $exceptions = explode(',', $this->exceptions);
        } else {
            $exceptions = null;
        }

        file_put_contents ( 'logueo.log' , 'Empezamos a ejecutar ' . PHP_EOL,FILE_APPEND );
        $analyzer = new FLowAnalyzer($this->key, $this->secret, $this->project, $exceptions);

        $users = null;
        if( !isset($this->skipUserValidation))
        {
            if(isset($this->users) ){
                $users =  $this->users;
            }
        }

        if( !isset($this->plan)){
            echo "You have selected no plan level at all. Nothing to do!";
            file_put_contents ( '../parameters/' . $this->file, false );
            die;
        }
        else{

            if( isset($this->filterByTags) && $this->filterByTags == "1"){
                $analyzer->setTags($this->tags);
            }


            $analyzer->analyzePeriod($this->datefrom, $this->dateTo, $this->plan, $users);
            file_put_contents ( '../parameters/' . $this->file, serialize($analyzer) );
        }
    }
}


$background = new Background($argv['1']);
$background->execute();
?>