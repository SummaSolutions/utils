<?php
/**
 * @category    Summa
 * @package     Summa_ViewMails
 * @author      Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    protected $_processedResult = '';

    /**
     * Process email template code
     *
     * @param   array $variables
     * @return  string
     */
    public function getProcessedTemplate(array $variables = array())
    {
        $this->_processedResult = parent::getProcessedTemplate($variables);

        Mage::dispatchEvent('email_template_get_processed_template',
            array('email_template' => $this, 'variables' => $variables)
        );

        return $this->_processedResult;
    }

    public function getProcessedResult()
    {
        return $this->_processedResult;
    }

    public function setProcessedResult($processedResult)
    {
        $this->_processedResult = $processedResult;
    }

}
