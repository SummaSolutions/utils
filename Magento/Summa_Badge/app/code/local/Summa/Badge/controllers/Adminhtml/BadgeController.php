<?php

class Summa_Badge_Adminhtml_BadgeController
    extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
            ->renderLayout();
    }

    protected function _initAction()
    {

        $this->loadLayout();

        return $this;
    }

    public function newAction()
    {
        // We just forward the new action to a blank edit form
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initAction();
        // Get id if available
        $id = $this->getRequest()->getParam('badge_id');
        $model = Mage::getModel('summa_badge/badge');

        if ($id) {
            // Load record
            $model->load($id);

            // Check if record is loaded
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This badge no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Badge'));

        $data = Mage::getSingleton('adminhtml/session')->getBadgeData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('summa_badge', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? $this->__('Edit Badge') : $this->__('New Badge'), $id ? $this->__('Edit Badge') : $this->__('New Badge'))
            ->_addContent($this->getLayout()->createBlock('summa_badge/adminhtml_badge_edit')->setData('action', $this->getUrl('*/*/save')))
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = Mage::getSingleton('summa_badge/badge');
            $model->setData($postData);

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The badge has been saved.'));
                $this->_redirect('*/*/');

                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while saving this badge.'));
            }

            Mage::getSingleton('adminhtml/session')->setBadgeData($postData);
            $this->_redirectReferer();
        }
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('badges_ids');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select badges(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('summa_badge/badge')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('enterprise_banner')->__('An error occurred while mass deleting badges. Please review log and try again.')
                );
                Mage::logException($e);

                return;
            }
        }
        $this->_redirect('*/*/index');
    }

    public function messageAction()
    {
        $data = Mage::getModel('summa_badge/badge')->load($this->getRequest()->getParam('badge_id'));
        echo $data->getContent();
    }

}