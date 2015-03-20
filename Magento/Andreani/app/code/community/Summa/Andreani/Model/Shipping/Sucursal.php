<?php

class Summa_Andreani_Model_Shipping_Sucursal
    extends Mage_Shipping_Model_Carrier_Abstract
{

    protected $_code = 'storepickup_andreani';

    public function getCode()
    {
        return $this->_code;
    }
    /**
     * Collect and get rates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result|bool|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
	$result = Mage::getModel('shipping/rate_result');

        $request->setShippingType('sucursal');

        $rateArray = $this->_getRateArray($request);
        
        // Calculate Order Subtotal
        $subTotalOrderPrice = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                $subTotalOrderPrice += $item->getPrice() * $item->getQty();
            }
        }
        
        $percentageToAdd = $subTotalOrderPrice * 1 / 100;
        foreach ($rateArray as $rate)
        {
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->getCode());
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($this->getCode());

                $method->setMethodTitle(Mage::helper('matrixrate')->__($rate['delivery_type']));

                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                $shippingPrice += $percentageToAdd;
                
                $method->setCost($rate['cost']);
                $method->setDeliveryType($rate['delivery_type']);

                $method->setPrice($shippingPrice);

                $result->append($method);
            }
        }
        
        return $result;
    }
    
    protected function _getRateArray($request)
    {
        $rate = Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate')->getNewRate($request);
        return $rate;
    }
        
    protected function _getStandardRate()
    {        
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');
        
        $rate->setCarrier($this->getCode());
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod($this->getCode());
        $rate->setMethodTitle($this->getConfigData('name'));
        
        // Starts at zero cost, calculates after picking a store
        $rate->setPrice(0);
        $rate->setCost(0);
        
        return $rate;
    }        

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        // TODO: Implement getAllowedMethods() method.
    }

    public function isStorePickupAvailable()
    {
        return Mage::getStoreConfig('carriers/storepickup/enable');
    }
    
    public function isEnabled()
    {
        return Mage::getStoreConfig('carriers/' . $this->_code . '/active') ? true : false;
    }
}