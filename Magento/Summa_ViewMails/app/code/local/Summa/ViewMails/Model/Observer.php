<?php
/**
 *
 * @category   Summa
 * @package    Summa_ViewMails
 * @author     Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_Model_Observer
{
    public function processEmail(Varien_Event_Observer $observer)
    {
        /* @var $helper Summa_ViewMails_Helper_Data */
        $helper = Mage::helper('summa_viewmails');
        $emailTemplate = $observer->getEvent()->getEmailTemplate();
        $processedResult = $emailTemplate->getProcessedResult();
        $variables = $observer->getEvent()->getVariables();

        if (!isset($variables['unsubscribeurl']) && isset($variables['email'])) {
            $subscriber = Mage::getModel('newsletter/subscriber');
            $variables['unsubscribeurl'] = $subscriber->loadByEmail($variables['email'])->getUnsubscriptionLink();
        }
        if (isset($variables['unsubscribeurl'])) {
            $processedResult = str_replace('{{var unsubscribeurl}}',$variables['unsubscribeurl'],$processedResult);
        }

        if ($helper->isEnabled() && $helper->canArchive($processedResult)) {
            $hash = $helper->generateHash();
            $inBrowserUrl = Mage::getUrl('emailviewer/index/index', array('code' => $hash));
            $processedResult = str_replace($helper::STR_LINK_SHOW_IN_BROWSER, $inBrowserUrl, $processedResult);
            $helper->archiveEmail($hash, $emailTemplate->getProcessedTemplateSubject($variables), $processedResult);
        } else {
            $processedResult = str_replace($helper::STR_LINK_SHOW_IN_BROWSER, '', $processedResult);
        }

        $emailTemplate->setProcessedResult($processedResult);

        return $this;
    }
}
