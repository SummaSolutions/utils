<?php
/**
 * Created for  grandmarche.
 * @author:     mhidalgo@summasolutions.net
 * Date:        11/02/15
 * Time:        10:24
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Block_Adminhtml_Sales_Order_View
    extends Mage_Adminhtml_Block_Sales_Order_View
{
    public function __construct()
    {
        parent::__construct();
        if($this->_isAllowedAction('generate_shippings') && $this->getOrder()->canShip()){

            $this->_addButton('generate_shippings', array(
                'label'     => Mage::helper('sales')->__('Crear envios pendientes'),
                'onclick'   => 'setLocation(\'' . $this->getGenerateShippingsUrl() . '\')',
            ));
        }
    }

    public function getGenerateShippingsUrl()
    {
        return $this->getUrl('*/andreani/index');
    }
}