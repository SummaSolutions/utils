<?php

/**
 * Class Summa_Andreani_Model_Sales_Order_Creditmemo_Total_Insurance
 *
 * @category Summa
 * @package  Summa_Andreani
 * @author   Augusto Leao <aleao@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

class Summa_Andreani_Model_Sales_Order_Creditmemo_Total_Insurance
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    /**
     * Collect credit memo subtotal
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return Mage_Sales_Model_Order_Creditmemo_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {

        $address = $creditmemo->getOrder()->getShippingAddress();
        $amount     = $address->getData('summa_andreani_insurance_amount');
        
        if ($amount) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $amount);
        }
        
        return $this;
    }
    
}