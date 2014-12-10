<?php
/**
 *
 * @category   Summa
 * @package    Summa_ViewMails
 * @author     Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout('systemPreview');

        $code = $this->getRequest()->getParam('code');
        if ($code) {
            $archive = Mage::getModel('summa_viewmails/email_archive');
            $archive->load($code, 'hash');
            if($archive->getId()) {
                $block =  $this->getLayout()->getBlock('content');
                $block->setData('archive', $archive);
            }
        }
        $this->renderLayout();
    }

}
