<?php

class Summa_Badge_Block_Adminhtml_Badge_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('summa_badge_form');
        $this->setTitle($this->__('Badge Information'));
    }

    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('summa_badge');

        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->__('Badge Information'),
            'class'  => 'fieldset-wide',
        ));

        if ($model->getId()) {
            $fieldset->addField('badge_id', 'hidden', array(
                'name' => 'badge_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name'     => 'name',
            'label'    => $this->__('Name'),
            'title'    => $this->__('Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name'     => 'description',
            'label'    => $this->__('Description'),
            'title'    => $this->__('Description'),
            'required' => true,
        ));


        $fieldset->addField('url', 'text', array(
            'name'     => 'url',
            'label'    => $this->__('Url'),
            'title'    => $this->__('Url'),
            'onclick'  => '',
            'required' => true,
            'after_element_html' => '<button type="button" onclick="MediabrowserUtility.openDialog(\'' .Mage::getSingleton('adminhtml/url')->getUrl('/cms_wysiwyg_images/index') .'target_element_id/url/badge/1\')">' . $this->__('Select Badge') .'</button>',
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}