<?php

/**
 * Class Summa_Andreani_Block_Adminhtml_Sales_Order_Totals
 *
 * @category Summa
 * @package  Summa_Andreani
 * @author   Augusto Leao <aleao@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

class Summa_Andreani_Block_Adminhtml_Sales_Order_Totals
    extends Mage_Adminhtml_Block_Sales_Order_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();

        if (Mage::helper('summa_andreani')->getConfigData('apply_insurance_on_shipping_price')) {
            return $this;
        }

        $address    = $this->getSource()->getShippingAddress();
        $amount     = $address->getData('summa_andreani_insurance');
        if ($amount != 0) {
            $this->addTotal(new Varien_Object(
                array(
                    'code'       => 'summa_andreani_insurance',
                    'value'      => $amount,
                    'base_value' => $amount,
                    'label'      => $this->helper('summa_andreani')->getConfigData('insurance_subtotal_title')
                ), 
                array('shipping'))
            );
        }
        return $this;
    }
}