<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        17/04/15
 * Time:        11:29
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Model_Status
    extends Mage_Core_Model_Abstract
{
    CONST SHIPMENT_NEW = 1;
    CONST SHIPMENT_PROCESSING = 2;
    CONST SHIPMENT_COMPLETED = 3;
    CONST SHIPMENT_PENDING = 4;

    CONST ANDREANI_STATUS_NEW = 'Envío no ingresado';
    CONST ANDREANI_STATUS_PROCESSING = 'Envío ingresado al circuito operativo';
    CONST ANDREANI_STATUS_COMPLETED = 'Envío entregado';

    CONST ORDER_STATUS_NEW = 'andreani_shipping_new';
    CONST ORDER_STATUS_PROCESSING = 'andreani_shipping_processing';
    CONST ORDER_STATUS_COMPLETED = 'andreani_shipping_completed';
    CONST ORDER_STATUS_PENDING = 'andreani_shipping_pending';
    CONST ORDER_STATUS_FAILED = 'andreani_shipping_failed';

    protected $_checkedStatuses = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function updateAllAndreaniShipmentStatuses()
    {
        $collection = Mage::getModel('sales/order_shipment')->getCollection()
            ->addFieldToFilter(
                'summa_andreani_shipment_status',
                array(
                    'in' =>
                        array(
                            self::SHIPMENT_NEW,
                            self::SHIPMENT_PROCESSING
                        )
                )
            );

        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        foreach($collection as $shipment) {
            if (count($shipment->getAllTracks()))
            {
                /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                foreach ($shipment->getAllTracks() as $track) {
                    $track->getNumberDetail();
                }

                $results = Mage::helper('summa_andreani')->getStatusSingleton()->getCheckedStatuses();

                $status = $results[$shipment->getId()];

                if ($status->getIsStatusUpdateRequired())
                {
                    $shipment->setSummaAndreaniShipmentStatus($status->getStatusToUpdate())
                        ->save();
                }
            }
        }
    }

    /**
     * @param Mage_Sales_Model_Order_Shipment_Track $tracking
     * @param string $andreaniStatus
     *
     * @return Varien_Object
     */
    public function checkStatus(Mage_Sales_Model_Order_Shipment_Track $tracking,$andreaniStatus)
    {
        switch ($andreaniStatus) {
            case self::ANDREANI_STATUS_NEW:
                $result = $this->_processAndreaniStatusNew($tracking);
                break;
            case self::ANDREANI_STATUS_PROCESSING:
                $result = $this->_processAndreaniStatusProcessing($tracking);
                break;
            case self::ANDREANI_STATUS_COMPLETED:
                $result = $this->_processAndreaniStatusCompleted($tracking);
                break;
            default:
                $result = $this->_processAndreaniStatusDefault($tracking);
                break;
        }
        $this->_checkedStatuses[$tracking->getShipment()->getId()] =
            (!isset($this->_checkedStatuses[$tracking->getShipment()->getId()]))?
                $result:
                $this->_mergeResult($this->_checkedStatuses[$tracking->getShipment()->getId()],$result);
        return $result;
    }

    public function getCheckedStatuses()
    {
        return $this->_checkedStatuses;
    }

    protected function _mergeResult($lastResult,$result)
    {
        if (!is_null($result->getIsStatusUpdateRequired())) {
            if ($lastResult->getStatusToUpdate() < $result->getStatusToUpdate())
            {
                return $result;
            }
        }
        return $lastResult;
    }

    protected function _processAndreaniStatusNew(Mage_Sales_Model_Order_Shipment_Track $tracking)
    {
        $result = new Varien_Object();
        $result->setIsStatusUpdateRequired(0);
        $result->setStatusToUpdate(-1);
        $shipmentStatus = $tracking->getShipment()->getSummaAndreaniShipmentStatus();
        if ($shipmentStatus != self::SHIPMENT_NEW || $shipmentStatus == self::SHIPMENT_PENDING) {
            $result->setIsStatusUpdateRequired(1);
            $result->setStatusToUpdate(self::SHIPMENT_NEW);
        }
        return $result;
    }

    protected function _processAndreaniStatusProcessing(Mage_Sales_Model_Order_Shipment_Track $tracking)
    {
        $result = new Varien_Object();
        $result->setIsStatusUpdateRequired(0);
        $result->setStatusToUpdate(-1);
        $shipmentStatus = $tracking->getShipment()->getSummaAndreaniShipmentStatus();
        if ($shipmentStatus != self::SHIPMENT_PROCESSING || $shipmentStatus == self::SHIPMENT_PENDING) {
            $result->setIsStatusUpdateRequired(1);
            $result->setStatusToUpdate(self::SHIPMENT_PROCESSING);
        }
        return $result;
    }

    protected function _processAndreaniStatusCompleted(Mage_Sales_Model_Order_Shipment_Track $tracking)
    {
        $result = new Varien_Object();
        $result->setIsStatusUpdateRequired(0);
        $result->setStatusToUpdate(-1);
        $shipmentStatus = $tracking->getShipment()->getSummaAndreaniShipmentStatus();
        if ($shipmentStatus != self::SHIPMENT_COMPLETED || $shipmentStatus == self::SHIPMENT_PENDING) {
            $result->setIsStatusUpdateRequired(1);
            $result->setStatusToUpdate(self::SHIPMENT_COMPLETED);
        }
        return $result;
    }

    protected function _processAndreaniStatusDefault(Mage_Sales_Model_Order_Shipment_Track $tracking)
    {
        $result = new Varien_Object();
        return $result;
    }


}