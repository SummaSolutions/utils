<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        19/03/15
 * Time:        09:24
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Model_Shipping_Carrier_Standard
    extends Summa_Andreani_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'andreaniStandard';

    /**
     * Short String with carriers service
     * @var string
     */
    protected $_serviceType = 'standard';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'summa_andreani_standard';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'andreani_standard';

    /**
     * Function to return array with allowed Methods
     *
     * This model will be used like abstract for the real carriers
     * then this method will be used for get array of all andreani
     * services enabled
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            $this->_code=>$this->_getHelper()->__($this->_getHelper()->getConfigData('name', $this->getServiceType()))
        );
    }

}