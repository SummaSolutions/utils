<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        16/04/15
 * Time:        11:56
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */
class Summa_Andreani_Block_Sales_Order_Total
    extends Mage_Core_Block_Template
{
    /**
     * Get label cell tag properties
     *
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get order store object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * Get value cell tag properties
     *
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    public function initTotals()
    {

        if ((float) $this->getOrder()->getBaseSummaAndreaniInsuranceAmount()) {
            $source = $this->getSource();
            $value  = $source->getSummaAndreaniInsuranceAmount();

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'summa_andreani_insurance',
                'strong' => false,
                'label'  => Mage::helper('summa_andreani')->getConfigData('insurance_subtotal_title'),
                'value'  => $source instanceof Mage_Sales_Model_Order_Creditmemo ? - $value : $value
            )));
        }

        return $this;
    }
}