<?php
/**
 * @author      Juan J Garay <jgaray@summasolutions.net>
 * @package     Useragent
 * @copyright   Summa Solutions <http://www.summasolutions.net>
 */

/*
 * get information about the device making the request
 */

class Guidance_Useragent_Helper_Data extends Mage_Core_Helper_Abstract {

    private $_device;
    private $_os;

    public function __construct()
    {
        $ua = $_SERVER['HTTP_USER_AGENT'];
        $iphone = strstr(strtolower($ua), 'mobile'); //Search for 'mobile' in user-agent (iPhone have that)
        $android = strstr(strtolower($ua), 'android'); //Search for 'android' in user-agent
        $windowsPhone = strstr(strtolower($ua), 'phone'); //Search for 'phone' in user-agent (Windows Phone uses that)
        $ipad = strstr(strtolower($ua), 'ipad'); //Search for iPad in user-agent
        $kindle = strstr(strtolower($ua), 'kindle'); //Search for iPad in user-agent

        //Find out if it is a tablet
        $androidTablet = false;
        if(strstr(strtolower($ua), 'android') ){//Search for android in user-agent
            if(!strstr(strtolower($ua), 'mobile')){ //If there is no ''mobile' in user-agent (Android have that on their phones, but not tablets)
                $androidTablet = true;
            }
        }

        // set OS value
        $this->_os = 'unknown';

        if ($windowsPhone){
            $this->_os = 'windowsMobile';
        }

        if ($android){
            $this->_os = 'android';
        }

        if ($iphone || $ipad){
            $this->_os = 'iOS';
        }

        //set device value
        $this->_device = 'unknown';

        if($androidTablet || $ipad || $kindle){ //If it's a tablet (iPad / Android / Kindly)
            $this->_device = 'tablet';
        }elseif($iphone || $android || $windowsPhone){ //If it's a phone and NOT a tablet
            $this->_device = 'mobile';
        }else{
            //If it's not a mobile device
            $this->_device = 'desktop';
        }
    }

    public function getDeviceType(){
        return $this->_device;
    }

    public function getOsName(){
        return $this->_os;
    }
}
