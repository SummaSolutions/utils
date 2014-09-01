<?php

class Summa_EmailPlus_Model_Core_Email_Template
    extends Mage_Core_Model_Email_Template
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
     * @return Zend_Mail
     */
    public function getMail()
    {
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

        return parent::getMail();
    }

    /**
     * Send mail to recipient
     *
     * @param   array|string      $email     E-mail(s)
     * @param   array|string|null $name      receiver name(s)
     * @param   array             $variables template variables
     *
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array())
    {
        if (Mage::getStoreConfigFlag(self::CONFIGURATION_PATH . "route_emails")) {
            $routeEmailAddress = $this->getConfig('route_email_address');
            if(!empty($routeEmailAddress)){
                $email = $routeEmailAddress;
            }
        }

        return parent::send($email, $name, $variables);
    }
}
