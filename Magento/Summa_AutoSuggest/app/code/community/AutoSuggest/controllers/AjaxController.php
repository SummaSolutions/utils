<?php
/**
 * @author: Facundo Capua
 * Date: 5/2/13
 */

class Summa_AutoSuggest_AjaxController extends Mage_Core_Controller_Front_Action
{
    public function searchAction()
    {
        $query = Mage::helper('catalogsearch')->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */
        $query->setStoreId(Mage::app()->getStore()->getId());
        $response = array(
            'products'   => $this->_getProductArray(),
            'categories' => $this->_getCategoryArray(),
            'pages'      => $this->_getCmsPagesArray()
        );

        $this->_sendJsonResponse($response);
    }

    public function templatesAction()
    {
        $response = array(
            'products' => $this->_getAutoSuggestBlock('products'),
            'categories' => $this->_getAutoSuggestBlock('categories'),
            'pages' => $this->_getAutoSuggestBlock('pages'),
        );

        $this->_sendJsonResponse($response);
    }

    /**
     * @return Summa_AutoSuggest_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_autosuggest');
    }


    /**
     * @return Summa_AutoSuggest_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('summa_autosuggest/config');
    }

    /**
     * @param $response mixed
     */
    protected function _sendJsonResponse($response)
    {
        $json = json_encode($response);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($json);
        $this->getResponse()->clearHeader('Location');
        $this->getResponse()->setHttpResponseCode(200);
    }

    protected function _getProductArray()
    {
        $collection = $this->_getHelper()->getProductCollection();
        $limit      = $this->_getConfig()->getProductsCollectionLimit();
        $collection->setPageSize($limit);

        $result = array();
        if(Mage::getStoreConfig('autosuggest/products/show')){
            list($width, $height) = $this->_getConfig()->getThumbnailDimension();

            foreach ($collection as $item) {
                $data = array(
                    'sku'   => $item->getSku(),
                    'name'  => $item->getName(),
                    'price' => Mage::helper('core')->currency($item->getFinalPrice(), true, false),
                    'url'   => $item->getProductUrl(),
                );

                if ($this->_getConfig()->shouldDisplayProductsThumbnail()) {
                    $data['image'] = (string)Mage::helper('catalog/image')->init($item, 'thumbnail')->resize($width, $height);
                }

                $result[] = $data;
            }
        }

        return $result;
    }

    protected function _getCategoryArray()
    {
        $collection = $this->_getHelper()->getCategoryCollection();
        $result     = array();

        if(Mage::getStoreConfig('autosuggest/categories/show')){
            foreach ($collection as $item) {
                $data = array(
                    'name'  => $item->getName(),
                    'count' => $item->getProductCount(),
                    'url'   => $this->_getHelper()->getCategoryUrl($item),
                );

                $result[] = $data;
            }
        }

        return $result;
    }

    protected function _getCmsPagesArray()
    {
        $collection = $this->_getHelper()->getCmsPagesCollection();
        $collection->addFieldToFilter('is_active', 1);
        $result     = array();

        if(Mage::getStoreConfig('autosuggest/pages/show')){
            foreach ($collection as $item) {
                $data = array(
                    'name'  => ($item->getContentHeading()) ? $item->getContentHeading() : $item->getTitle(),
                    'url'   => Mage::helper('cms/page')->getPageUrl($item->getId()),
                );

                $result[] = $data;
            }
        }

        return $result;
    }

    private function _getAutoSuggestBlock($id){
        $block = '';
        if(Mage::getStoreConfig('autosuggest/'.$id.'/show')){
            $block = $this->getLayout()->createBlock('autosuggest/results')->setTemplate('autosuggest/templates/structures/'.$id.'.phtml')->toHtml();
        }
        return $block;
    }

}