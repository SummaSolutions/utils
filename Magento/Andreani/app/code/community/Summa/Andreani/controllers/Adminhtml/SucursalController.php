<?php

class Summa_Andreani_Adminhtml_SucursalController
    extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sucursal/sucursal')
            ->_title($this->__('Sucursales Andreani'))->_title($this->__('Administrar Sucursales'))
            ->_addBreadcrumb($this->__('Sucursales Andreani'), $this->__('Sucursales Andreani'))
            ->_addBreadcrumb($this->__('Administrar Sucursales'), $this->__('Administrar Sucursales'));

        return $this;
    }

    public function indexAction()
    {
        $Block = $this->getLayout()->createBlock('summa_andreani/adminhtml_sucursal');
        $this->_title($this->__('Brands'))->_title($this->__('Brands'));
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
        $model = Mage::getModel('summa_andreani/sucursal');

        Mage::register('sucursales_sucursal', $model);
        
        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Esta sucursal ya no existe.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('Nueva Sucursal'));

        $data = Mage::getSingleton('adminhtml/session')->getSucursalData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('summa_andreani', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? $this->__('Editar Sucursal') : $this->__('Nueva Sucursal'), $id ? $this->__('Editar Sucursal') : $this->__('Nueva Sucursal'))
            ->_addContent($this->getLayout()->createBlock('summa_andreani/adminhtml_sucursal_edit')->setData('action', $this->getUrl('*/*/save')))
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = Mage::getSingleton('summa_andreani/sucursal');
            $model->setData($postData);

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('La sucursal se ha guardado.'));
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
                Mage::getSingleton('adminhtml/session')->addError($this->__('Ocurrio un error mientras se guardaba la sucursal.'));
            }

            Mage::getSingleton('adminhtml/session')->setBazData($postData);
            $this->_redirectReferer();
        }
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $model = Mage::getModel('summa_andreani/sucursal')->load($id);
            $model->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The Branch has been deleted successfully'));
        }
        $this->_redirect('*/*');
    }
    
    public function messageAction()
    {
        $data = Mage::getModel('summa_andreani/sucursal')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }

    public function massDeleteAction()
    {
        $branchIds = $this->getRequest()->getParam('sucursal');
        if(!is_array($branchIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select branch(s).'));
        } else {
            try {
                $branch = Mage::getModel('summa_andreani/sucursal');
                foreach ($branchIds as $branchId) {
                    $branch->load($branchId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were deleted.', count($branchIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sucursal/sucursal');
    }

}
