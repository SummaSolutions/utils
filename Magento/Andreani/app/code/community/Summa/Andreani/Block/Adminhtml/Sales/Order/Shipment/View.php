<?php

/**
 * Summa_Andreani_Block_Adminhtml_Shipment_View
 *
 * @category Grandmarche
 * @package  Grandmarche_Andreani
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_Andreani_Block_Adminhtml_Sales_Order_Shipment_View
    extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{
    public function __construct()
    {
        parent::__construct();

        $shipment = $this->getShipment();
        $id = $shipment->getId();
        /** @var Summa_Andreani_Helper_Adminhtml $helper */
        $helper = Mage::helper('summa_andreani/adminhtml');
        if ($helper->canCancelShipment($shipment))
        {
            $cancelShipmentUrl = $this->getUrl('adminhtml/andreani/cancelShipment', array(
                'id'=>$id
            ));
            $this->_addButton('cancelShipment', array(
                'label' => $helper->__('Cancel Andreani Shipment'),
                'class' => 'save',
                'onclick'   => "setLocation('$cancelShipmentUrl')"
            ));
        }

        if ($helper->canGenerateConstancy($shipment))
        {
            $generateLinkConstancyUrl = $this->getUrl('adminhtml/andreani/generateLinkConstancy', array(
                'id'=>$id
            ));
            $this->_addButton('generateLinkConstancy', array(
                'label' => $helper->__('Generate Shipping Label with Constancy'),
                'class' => 'save',
                'onclick'   => "setLocation('$generateLinkConstancyUrl')"
            ));
        }

        if ($helper->canGenerateAndreaniRequest($shipment))
        {
            $generateAndreaniRequestUrl = $this->getUrl('adminhtml/andreani/generateAndreaniRequest', array(
                'id'=>$id
            ));
            $this->_addButton('generateAndreaniRequest', array(
                'label' => $helper->__('Generate Andreani Request'),
                'class' => 'save',
                'onclick'   => "setLocation('$generateAndreaniRequestUrl')"
            ));
        }
    }
}