<?php

class Summa_CmsTree_Block_Adminhtml_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/updateNode', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $node = Mage::registry('cmstree_currentnode');

        $fieldset = $form->addFieldset('edit_form',
            array('legend' => $this->__('Tree Node Information')));

        if ($node) {
            if ($this->getRequest()->getParam('store') && $node->isRoot()) {
                $useDefault = $fieldset->addField('use_default', 'select', array(
                    'label' => $this->__('Use Default'),
                    'required' => true,
                    'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
                    'value' => Mage::getStoreConfig('cmstree/use_default', $this->getRequest()->getParam('store')) ? 1 : 0,
                    'name' => 'use_default'
                ));

                $dependencies = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
                $dependencies->addFieldMap($useDefault->getHtmlId(), $useDefault->getName());
            }
            $title = $fieldset->addField('title', 'text', array(
                'label' => $this->__('Title'),
                'required' => true,
                'value' => isset($node) ? $node->getTitle() : '',
                'name' => 'title',
            ));
            $id = $fieldset->addField('id', 'hidden', array(
                'value' => isset($node) ? $node->getId() : '',
                'name' => 'id',
            ));
            $storeId = $fieldset->addField('store_id', 'hidden', array(
                'name' => 'store_id',
                'value' => isset($node) ? $node->getStoreId() : '',
            ));


            if (!$node->isRoot()) {

                $pageId = $fieldset->addField('page_id', 'text', array(
                    'label' => $this->__('Page Id'),
                    'required' => true,
                    'name' => 'page_id',
                    'value' => isset($node) ? $node->getPageId() : '',
                ));

                $parentId = $fieldset->addField('parent_id', 'text', array(
                    'label' => $this->__('Parent Id'),
                    'required' => true,
                    'name' => 'parent_id',
                    'value' => isset($node) ? $node->getParentId() : '',
                ));
                $path = $fieldset->addField('path', 'text', array(
                    'label' => $this->__('Path'),
                    'required' => true,
                    'name' => 'path',
                    'value' => isset($node) ? $node->getPath() : '',
                ));
                $position = $fieldset->addField('position', 'text', array(
                    'label' => $this->__('Position'),
                    'required' => true,
                    'name' => 'position',
                    'value' => isset($node) ? $node->getPosition() : '',
                ));
            }
        }

        if (isset($dependencies)) {
            $dependencies->addFieldMap($title->getHtmlId(), $title->getName())
                ->addFieldDependence(
                    $title->getName(),
                    $useDefault->getName(),
                    0
                );
            $this->setForm($form);
            $this->setChild('form_after', $dependencies);
        }
        return parent::_prepareForm();
    }
}