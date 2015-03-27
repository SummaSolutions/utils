<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        26/03/15
 * Time:        16:38
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Adminhtml_BranchController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('andreani_branches/andreani_branches')
            ->_title($this->__('Branch Management'))
            ->_addBreadcrumb($this->__('Branch Management'), $this->__('Branch Management'));

        return $this;
    }

    public function indexAction()
    {
        $Block = $this->getLayout()->createBlock('summa_andreani/adminhtml_branch');
        $this->_title($this->__('Branches'));
        $this->loadLayout()->_addContent($Block)->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_initAction();

        $id  = $this->getRequest()->getParam('id');
        $model = Mage::getModel('summa_andreani/branch');

        Mage::register('branches_branch', $model);

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('summa_andreani')->__('That branch don\'t exist.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : Mage::helper('summa_andreani')->__('New Branch'));

        $data = Mage::getSingleton('adminhtml/session')->getBranchData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('summa_andreani_branch', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('summa_andreani')->__('Edit Branch') : Mage::helper('summa_andreani')->__('New Branch'), $id ? Mage::helper('summa_andreani')->__('Edit Branch') : Mage::helper('summa_andreani')->__('New Branch'))
            ->_addContent($this->getLayout()->createBlock('summa_andreani/adminhtml_branch_edit')->setData('action', $this->getUrl('*/*/save')))
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = Mage::getSingleton('summa_andreani/branch');
            $model->setData($postData);

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('summa_andreani')->__('Branch was saved successfully.'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');

                return;
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('summa_andreani')->__('There was an error while branch was saved.'));
            }

            Mage::getSingleton('adminhtml/session')->setBazData($postData);
            $this->_redirectReferer();
        }
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model = Mage::getModel('summa_andreani/branch')->load($id);
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('summa_andreani')->__('The Branch has been deleted successfully'));
        }
        $this->_redirect('*/*');
    }

    public function messageAction()
    {
        $data = Mage::getModel('summa_andreani/branch')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }

    public function fetchBranchesAction()
    {
        if (Mage::getSingleton('summa_andreani/branch')->fetchBranches()) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('summa_andreani')->__('Branches has been fetched from Andreani Web Service successfully'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('summa_andreani')->__('Branches hasn\'t been fetched from Andreani Web Service successfully'));
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $branchIds = $this->getRequest()->getParam('branch');
        if(!is_array($branchIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('summa_andreani')->__('Please select branch(s).'));
        } else {
            try {
                $branch = Mage::getModel('summa_andreani/branch');
                foreach ($branchIds as $branchId) {
                    $branch->load($branchId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('summa_andreani')->__('Total of %d record(s) were deleted.', count($branchIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('summa_andreani/andreani_branches');
    }
}