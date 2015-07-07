<?php

class Summa_Badge_Block_Adminhtml_Wysiwyg_Images_Content
    extends Mage_Adminhtml_Block_Cms_Wysiwyg_Images_Content
{

    public function getOnInsertUrl()
    {
        if (Mage::app()->getRequest()->getParam('badge') == '1') {
            return $this->getUrl('*/*/onInsert/badge/1');
        }

        return parent::getOnInsertUrl();

    }


}
