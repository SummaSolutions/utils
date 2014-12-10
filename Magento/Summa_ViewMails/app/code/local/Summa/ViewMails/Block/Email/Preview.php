<?php
/**
 *
 * @category   Summa
 * @package    Summa_ViewMails
 * @author     Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_Block_Email_Preview extends Mage_Core_Block_Template
{
    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getArchive()) {
            return '';
        }

        return $this->getArchive()->getBody();
    }
}
