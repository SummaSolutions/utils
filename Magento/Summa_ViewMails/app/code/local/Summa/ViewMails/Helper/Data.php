<?php
/**
 *
 * @category   Summa
 * @package    Summa_ViewMails
 * @author     Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_Helper_Data extends Mage_Core_Helper_Abstract
{
    const STR_LINK_SHOW_IN_BROWSER = '{{var viewinbrowser}}';

    public function isEnabled()
    {
        $enabled = Mage::getStoreConfig('summa/emails/enabled');
        return $enabled;
    }

    public function canClearEmailArchive()
    {
        $clear = Mage::getStoreConfig('summa/emails/clear_email_archive');
        return $clear;
    }

    public function getPeriod()
    {
        $period = Mage::getStoreConfig('summa/emails/period');
        return $period;
    }

    public function canArchive($string)
    {
        return (strpos($string, self::STR_LINK_SHOW_IN_BROWSER) !== false);
    }

    public function archiveEmail($hash, $subject, $body)
    {
        $archive = Mage::getModel('summa_viewmails/email_archive');

        $archive->setHash($hash);
        $archive->setSubject($subject);
        $archive->setBody($this->minify($body));
        $archive->setCreatedAt(Mage::getModel('core/date')->timestamp(time()));
        try {
            $archive->save();
        } catch (Excepton $e) {
            Mage::log('Error on Email Archive save');
        }
    }

    public function generateHash()
    {
        $chars = Mage_Core_Helper_Data::CHARS_PASSWORD_LOWERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_UPPERS
            . Mage_Core_Helper_Data::CHARS_PASSWORD_DIGITS;

        return Mage::helper('core')->getRandomString(32, $chars);
    }

    public function minify($html)
    {
        $search = array(
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        if (preg_match("/\<body/i",$html) == 1 && preg_match("/\<\/body\>/i",$html) == 1) {
            $html = preg_replace($search, $replace, $html);
        }
        return $html;
    }
}
