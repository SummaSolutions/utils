<?php

/**
 * Class Summa_Andreani_Model_Sales_Order_Invoice_Total_Insurance
 *
 * @category Summa
 * @package  Summa_Andreani
 * @author   Augusto Leao <aleao@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

class Summa_Andreani_Model_Sales_Order_Invoice_Total_Insurance
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    /**
     * Collect invoice subtotal
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Mage_Sales_Model_Order_Invoice_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {

        $address = $invoice->getOrder()->getShippingAddress();
        $amount     = $address->getData('summa_andreani_insurance_amount');
        
        if ($amount) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $amount);
        }
        
        return $this;
    
    }
    
}