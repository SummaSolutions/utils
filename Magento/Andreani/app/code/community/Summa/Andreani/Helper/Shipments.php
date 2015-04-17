<?php
/**
 * @author: Facundo Capua
 *        Date: 5/4/12
 */
class Summa_Andreani_Helper_Shipments
    extends Mage_Core_Helper_Abstract
{
    protected static $_carriers = null;

    public function isOrderInfoLine($data)
    {
        return (boolean)(sizeof($data) == 4);
    }

    public function isItemLine($data)
    {
        return (boolean)(sizeof($data) == 3);
    }

    public function getOrderInfo($data, &$shipment)
    {
        list(
            $order_number,
            $tracking_number,
            $shipping_date,
            $carrier_name
            ) = $data;

        $carrier_name = trim(strtolower($carrier_name));
        if(!$this->_getCarrier($carrier_name)){
            $carrier_name = 'custom';
        }


        $shipment['order_info'] = array(
            'increment_id'    => (string)trim($order_number),
            'tracking_number' => (string)trim($tracking_number),
            'date'            => (string)trim($shipping_date),
            'carrier'         => (string)$carrier_name,
            'order'           => Mage::getModel("sales/order")->loadByIncrementId($order_number)
        );
    }

    public function getItemInfo($data, &$shipment)
    {
        list(
            $package_number,
            $sku,
            $qty
            ) = $data;

        $product = Mage::getModel('sales/order_item')->getCollection()
                    ->addFieldToFilter('order_id', $shipment['order_info']['order']->getId())
                    ->addFieldToFilter('sku', $sku)
                    ->load()
                    ->getFirstItem();
        if ($product) {
            if (empty($shipment['items_info'])) {
                $shipment['items_info'] = array();
            }

            $shipment['items_info'][$product->getId()] = $qty;
        }
    }

    public function isValidShipment($shipment)
    {
        $return = true;
        if (empty($shipment) || empty($shipment['order_info']) || empty($shipment['items_info'])) {
            $return = false;
        }

        return $return;
    }

    public function saveShipment($data)
    {
        $shipment_id        = null;
        $order              = $data['order_info']['order'];

        // Create invoice for the order
        /*if($order->canInvoice()) {
            //Create invoice with pending status
            $invoice_id = Mage::getModel('sales/order_invoice_api')
                ->create($order->getIncrementId(), array());

            $invoice = Mage::getModel('sales/order_invoice')
                ->loadByIncrementId($invoice_id);

            //set invoice status "paid"
            $invoice->pay()->save();
        }*/

        // Create shipment associated.
        if ($order->canShip()) {
            $shipment_id = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $data['items_info']);

            $track_id = Mage::getModel('sales/order_shipment_api')->addTrack(
                $shipment_id,
                $data['order_info']['carrier'],
                'Shipment - ' . $data['order_info']['date'],
                $data['order_info']['tracking_number']
            );

            Mage::getModel('sales/order_shipment_api')->sendInfo($shipment_id);
        }

        return $shipment_id;
    }

    protected function _getCarriers()
    {
        if(self::$_carriers === null){
            $storeId = Mage::app()->getStore()->getId();
            self::$_carriers = Mage::getSingleton('shipping/config')->getAllCarriers($storeId);
        }

        return self::$_carriers;
    }

    protected function _getCarrier($carrierCode)
    {
        foreach($this->_getCarriers() as $code => $carrier){
            if($carrierCode == $code){
                return $carrier;
            }
        }

        return false;
    }


}
