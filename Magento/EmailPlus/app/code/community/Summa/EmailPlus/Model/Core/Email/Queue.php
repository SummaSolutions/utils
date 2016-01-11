<?php

/**
 * Created for  makro.
 *
 * @author      :     mhidalgo@summasolutions.net
 *              Date:        04/02/15
 *              Time:        15:06
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */
class Summa_EmailPlus_Model_Core_Email_Queue
    extends Mage_Core_Model_Email_Queue
{
    const CONFIGURATION_PATH = "system/emailplus/";

    /**
     * @param $config
     *
     * @return mixed
     */
    protected function getConfig($config)
    {
        return Mage::getStoreConfig(self::CONFIGURATION_PATH . $config);
    }

    /**
     * Send all messages in a queue
     *
     * @return Mage_Core_Model_Email_Queue
     */
    public function send()
    {
        /** @var $collection Mage_Core_Model_Resource_Email_Queue_Collection */
        $collection = Mage::getModel('core/email_queue')->getCollection()
            ->addOnlyForSendingFilter()
            ->setPageSize(self::MESSAGES_LIMIT_PER_CRON_RUN)
            ->setCurPage(1)
            ->load();
        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));
        /** @var $message Mage_Core_Model_Email_Queue */
        foreach ($collection as $message) {
            if ($message->getId()) {
                $parameters = new Varien_Object($message->getMessageParameters());
                if (Mage::getStoreConfigFlag(self::CONFIGURATION_PATH . "enable")) {
                    $config = array(
                        'ssl'      => strtolower($this->getConfig("ssl")),
                        'port'     => $this->getConfig("port"),
                        'auth'     => strtolower($this->getConfig("auth")),
                        'username' => $this->getConfig("username"),
                        'password' => $this->getConfig("password")
                    );
                    $transport = new Zend_Mail_Transport_Smtp($this->getConfig("host"), $config);
                    Zend_Mail::setDefaultTransport($transport);
                }
                if (!Mage::getStoreConfigFlag(self::CONFIGURATION_PATH . "enable") && $parameters->getReturnPathEmail() !== null) {
                    $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $parameters->getReturnPathEmail());
                    Zend_Mail::setDefaultTransport($mailTransport);
                }
                $mailer = new Zend_Mail('utf-8');
                foreach ($message->getRecipients() as $recipient) {
                    list($email, $name, $type) = $recipient;
                    $email = trim($email);
                    switch ($type) {
                        case self::EMAIL_TYPE_BCC:
                            $mailer->addBcc($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                        case self::EMAIL_TYPE_TO:
                        case self::EMAIL_TYPE_CC:
                        default:
                            $mailer->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
                            break;
                    }
                }
                if ($parameters->getIsPlain()) {
                    $mailer->setBodyText($message->getMessageBody());
                } else {
                    $mailer->setBodyHTML($message->getMessageBody());
                }
                $mailer->setSubject('=?utf-8?B?' . base64_encode($parameters->getSubject()) . '?=');
                $mailer->setFrom(trim($parameters->getFromEmail()), trim($parameters->getFromName()));
                if ($parameters->getReplyTo() !== null) {
                    $mailer->setReplyTo(trim($parameters->getReplyTo()));
                }
                if ($parameters->getReturnTo() !== null) {
                    $mailer->setReturnPath(trim($parameters->getReturnTo()));
                }
                try {
                    $mailer->send();
                    unset($mailer);
                    $message->setProcessedAt(Varien_Date::formatDate(true));
                    $message->save();
                } catch (Exception $e) {
                    unset($mailer);
                    $oldDevMode = Mage::getIsDeveloperMode();
                    Mage::setIsDeveloperMode(true);
                    Mage::logException($e);
                    Mage::setIsDeveloperMode($oldDevMode);

                    return false;
                }
            }
        }

        return $this;
    }
}