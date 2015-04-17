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

        if (Mage::helper('summa_andreani')->canCancelShipment($shipment))
        {
            $cancelShipmentUrl = $this->getUrl('adminhtml/andreani/cancelShipment', array(
                'id'=>$id
            ));
            $this->_addButton('cancelShipment', array(
                'label' => Mage::helper('sales')->__('Cancel Shipment'),
                'class' => 'save',
                'onclick'   => "setLocation('$cancelShipmentUrl')"
            ));
        }

        if (Mage::helper('summa_andreani')->canGenerateConstancy($shipment))
        {
            $generateLinkConstanciaUrl = $this->getUrl('adminhtml/andreani/generateLinkConstancy', array(
                'id'=>$id
            ));
            $this->_addButton('generateLinkConstancy', array(
                'label' => Mage::helper('summa_andreani')->__('Generate Link to Constancy'),
                'class' => 'save',
                'onclick'   => "setLocation('$generateLinkConstanciaUrl')"
            ));
        }
    }
}