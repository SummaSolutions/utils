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

    /**
     * Function to create a new Shipment and Add Tracking code
     * @param $data
     *
     * @return null
     */
    public function saveShipment($data)
    {
        $shipment_id        = null;
        $order              = $data['order_info']['order'];

        // Create shipment associated.
        if ($order->canShip()) {
            $shipment_id = Mage::getModel('sales/order_shipment_api')->create($order->getIncrementId(), $data['items_info']);

            $track_id = Mage::getModel('sales/order_shipment_api')->addTrack(
                $shipment_id,
                $data['order_info']['carrier'],
                $data['order_info']['title'],
                $data['order_info']['tracking_number']
            );

            Mage::getModel('sales/order_shipment_api')->sendInfo($shipment_id);
        }

        return $shipment_id;
    }

    /**
     * Function to get All Carriers inside an array
     * @return array
     */
    protected function _getCarriers()
    {
        if(self::$_carriers === null){
            $storeId = Mage::app()->getStore()->getId();
            self::$_carriers = Mage::getSingleton('shipping/config')->getAllCarriers($storeId);
        }

        return self::$_carriers;
    }

    /**
     * Function to get Carrier based-on Carrier code
     * @param $carrierCode
     *
     * @return bool
     */
    protected function _getCarrier($carrierCode)
    {
        foreach($this->_getCarriers() as $code => $carrier){
            if($carrierCode == $code){
                return $carrier;
            }
        }

        return false;
    }

    /**
     * @param $data
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment($data)
    {
        if (is_int($data)) {
            return Mage::getModel('sales/order_shipment')->loadByIncrementId($data);
        } elseif ($data instanceof Mage_Sales_Model_Order_Shipment) {
            return $data;
        }
        return Mage::getModel('sales/order_shipment');
    }

    /**
     * @param $link
     *
     * @return string
     */
    public function preparePdf($link)
    {
        $pdfString = file_get_contents($link);
        $endPDF = strrpos($pdfString, '%%EOF');
        return substr($pdfString,0,$endPDF + 6);
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param $andreaniResponse
     *
     * @throws Zend_Pdf_Exception
     */
    public function addShippingLabel($shipment,$andreaniResponse)
    {
        $shipment = $this->getShipment($shipment);
        $labelsContent = array($andreaniResponse->getShippingLabelContent());
        $outputPdf = $this->_combineLabelsPdf($labelsContent);
        $shipment->setShippingLabel($outputPdf->render())->save();
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param $andreaniResponse
     * @param Summa_Andreani_Model_Shipping_Carrier_Abstract $carrier
     */
    public function addTrackingCode($shipment,$andreaniResponse,$carrier)
    {
        $shipment = $this->getShipment($shipment);
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        $track = Mage::getModel('sales/order_shipment_track');
        $track->setCarrierCode($carrier->getCode())
            ->setTitle(Mage::helper('summa_andreani')->getConfigData('title',$carrier->getServiceType()))
            ->setNumber($andreaniResponse->getTrackingNumber());
        $shipment->addTrack($track)->save();
    }

    /**
     * Combine array of labels as instance PDF
     *
     * @param array $labelsContent
     * @return Zend_Pdf
     */
    protected function _combineLabelsPdf(array $labelsContent)
    {
        $outputPdf = new Zend_Pdf();
        foreach ($labelsContent as $content) {
            if (stripos($content, '%PDF-') !== false) {
                $pdfLabel = Zend_Pdf::parse($content);
                foreach ($pdfLabel->pages as $page) {
                    $outputPdf->pages[] = clone $page;
                }
            } else {
                $page = $this->_createPdfPageFromImageString($content);
                if ($page) {
                    $outputPdf->pages[] = $page;
                }
            }
        }
        return $outputPdf;
    }

    /**
     * Create Zend_Pdf_Page instance with image from $imageString. Supports JPEG, PNG, GIF, WBMP, and GD2 formats.
     *
     * @param string $imageString
     * @return Zend_Pdf_Page|bool
     */
    protected function _createPdfPageFromImageString($imageString)
    {
        $image = imagecreatefromstring($imageString);
        if (!$image) {
            return false;
        }

        $xSize = imagesx($image);
        $ySize = imagesy($image);
        $page = new Zend_Pdf_Page($xSize, $ySize);

        imageinterlace($image, 0);
        $tmpFileName = sys_get_temp_dir() . DS . 'shipping_labels_'
            . uniqid(mt_rand()) . time() . '.png';
        imagepng($image, $tmpFileName);
        $pdfImage = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($pdfImage, 0, 0, $xSize, $ySize);
        unlink($tmpFileName);
        return $page;
    }
}
