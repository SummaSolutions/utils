<?php

/**
 * Class Summa_Andreani_Model_Sales_Quote_Address_Total_Insurance
 *
 * @category Summa
 * @package  Summa_Andreani
 * @author   Augusto Leao <aleao@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

class Summa_Andreani_Model_Sales_Quote_Address_Total_Insurance
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /**
     * Total Code name
     *
     * @var string
     */
    protected $_code = 'summa_andreani_insurance';

    
    /**
     * Reset Insurance Data
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return Summa_Andreani_Model_Sales_Quote_Address_Total_Insurance
     */
    protected function _resetFields(Mage_Sales_Model_Quote_Address $address)
    {
        // Reset insurance Amount
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
        $address->unsetData('summa_andreani_insurance_amount');
        $address->unsetData('base_summa_andreani_insurance_amount');

        return $this;
    }
    
    /**
     * Get total label name
     *
     * @return string
     */
    public function getLabel()
    {
        return Mage::helper('summa_andreani')->getConfigData('insurance_subtotal_title');
    }
    
    /**
     * Check if totals can be calculated
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return boolean
     */
    protected function _canCollect(Mage_Sales_Model_Quote_Address $address)
    {
        if (Mage::helper('summa_andreani')->getConfigData('apply_insurance_on_shipping_price')) {
            return false;
        }
        if (($address->getAddressType() == 'billing')) {
            return false;
        }
        if (!count($this->_getAddressItems($address))) {
            return false;
        }
        return true;
    }
    
    /**
     * Calculate the total of the insurance applied
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return float
     */
    protected function _getInsuranceAmount(Mage_Sales_Model_Quote_Address $address)
    {
        
        $helper = Mage::helper('summa_andreani');
        $amount = 0;

        if(!(!$helper->getConfigData('apply_insurance_when_free_shipping') && !(int) $address->getBaseShippingAmount())
            || $helper->getConfigData('apply_insurance_when_free_shipping')){
            
            $percentage = Mage::helper('summa_andreani')->getConfigData('insurance');
            $amount = $address->getSubtotal() * $percentage / 100;
        }
        
        return $amount;
        
    }
    
    /**
     * Collect totals information about insurance
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     *
     * @return $this|Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        
        if(!$this->_canCollect($address)){
            return $this;
        }
        
        $this->_resetFields($address);
        $amount = $this->_getInsuranceAmount($address);
        
        if($amount){
            $address->setSummaAndreaniInsuranceAmount($amount);
            $address->setBaseSummaAndreaniInsuranceAmount($amount);
            $this->_addAmount($amount);
            $this->_addBaseAmount($amount);
        }

        return $this;
    }

    /**
     * Add totals information to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     *
     * @return Summa_Andreani_Model_Sales_Quote_Address_Total_Insurance
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        parent :: fetch($address);

        if(Mage::helper('summa_andreani')->getConfigData('apply_insurance_on_shipping_price')){
            return $this;
        }

        $amount = $this->_getInsuranceAmount($address);
        
        if ($amount) {
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $amount
            ));
        }

        return $this;
    }

}