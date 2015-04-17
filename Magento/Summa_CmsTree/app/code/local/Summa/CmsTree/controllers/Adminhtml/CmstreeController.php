<?php

class Summa_CmsTree_Adminhtml_CmstreeController
    extends Mage_Adminhtml_Controller_Action
{
    private function _initNode($id = null)
    {
        $node = Mage::getModel('summa_cmstree/cmsTree');
        if ($id) {
            $node->load($id);
        } else {
            $node = $node->getRootNode($this->getRequest()->getParam('store'));
        }
        Mage::register('cmstree_currentnode', $node);
        return $node;
    }


    public function indexAction()
    {
        $this->_initNode($this->getRequest()->getParam('id'));
        $this->loadLayout()
            ->_setActiveMenu('cms/cmstree');
        $this->renderLayout();
    }

    public function massAddNodeAction()
    {
        $storeId = $this->getRequest()->getParam('store');

        if (isset($storeId) && !($storeId == '') && $this->getRequest()->getParam('page_id')) {
            $parent = $this->_initNode($this->getRequest()->getParam('current_node'));

            $position = $parent->getLastPosition();
            $level = $parent->getLevel() + 1;

            $collection = Mage::getModel('cms/page')->getCollection()
                ->addFieldToFilter('page_id', array('in' => $this->getRequest()->getParam('page_id')));

            foreach ($collection as $page) {
                $node = Mage::getModel('summa_cmstree/cmsTree');
                $node->setTitle($page->getTitle())
                    ->setPageId($page->getId())
                    ->setParentId($parent->getId())
                    ->setStoreId($storeId)
                    ->setPosition(++$position)
                    ->setLevel($level);

                $node->save();
                $node->setPath($parent->getPath() . '/' . $node->getId());
                $node->save();
            }
        }
        $this->_redirectReferer();
    }


    public function cmstreeJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setIsTreeWasExpanded(false);
        }
        if ($node = $this->_initNode($this->getRequest()->getParam('id'))) {
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('summa_cmstree/adminhtml_tree')
                    ->getTreeJson($node)
            );
        }
    }


    public function editAction()
    {
        if (($node = $this->_initNode($this->getRequest()->getParam('id')))) {
            $this->loadLayout();
            /**
             * Build response for ajax request
             */
            if ($this->getRequest()->getQuery('isAjax')) {
                $response = new Varien_Object(array(
                    'content' => $this->getLayout()->createBlock('summa_cmstree/adminhtml_edit')->toHtml()
                        . $this->getLayout()->createBlock('summa_cmstree/adminhtml_tree'),
                    'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
                ));
                $this->getResponse()->setBody(
                    Mage::helper('core')->jsonEncode($response->getData())
                );
                return;
            }
        }
        $this->_redirectReferer();
    }


    public function updateNodeAction()
    {
        $params = $this->getRequest()->getParams();

        if ($this->getRequest()->getParam('id')) {
            $node = $this->_initNode($this->getRequest()->getParam('id'));
            Mage::getConfig()->saveConfig('cmstree/use_default', $params['use_default'], 'stores', $params['store_id']);
            if (!$params['use_default']) {
                $node->setTitle($params['title']);
                if (!$node->isRoot()) {
                    $node->setPageId($params['page_id'])
                        ->setStoreId($params['store_id'])
                        ->setParentId($params['parent_id'])
                        ->setPath($params['path'])
                        ->setPosition($params['position']);
                }
                $node->save();
                Mage::unregister('cmstree_currentnode');
                Mage::register('cmstree_currentnode', $node);
            }
            $this->_redirect('*/*/index', array('store' => $params['store_id'], 'id' => $node->getId()));
        }
    }


    public function deleteNodeAction()
    {
        if ($this->getRequest()->getParam('id')) {
            $node = $this->_initNode($this->getRequest()->getParam('id'));
            try {
                $node->delete();
                Mage::getSingleton('core/session')->addSuccess($this->__('Success'));
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($this->__($e));
                Mage::logException($e);
            }
        } else {
            Mage::getSingleton('core/session')->addError($this->__('Node delete error'));
        }
        $this->_redirectReferer();
    }

    public function moveAction()
    {
        if ($node = $this->_initNode($this->getRequest()->getParam('id'))) {
            $parentNodeId = $this->getRequest()->getParam('pid', false);
            $prevNodeId = $this->getRequest()->getParam('aid', false);

            try {
                $node->move($parentNodeId, $prevNodeId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}