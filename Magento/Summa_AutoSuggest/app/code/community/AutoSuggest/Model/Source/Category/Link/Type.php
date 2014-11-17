<?php
/**
 * @author: Facundo Capua
 * Date: 5/20/13
 */

class Summa_AutoSuggest_Model_Source_Category_Link_Type
{
    const  CATEGORY_PAGE = 1;
    const  RESULTS_PAGE = 2;


    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::CATEGORY_PAGE, 'label'=>Mage::helper('summa_autosuggest')->__('Category Page')),
            array('value' => self::RESULTS_PAGE, 'label'=>Mage::helper('summa_autosuggest')->__('Search results Page')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::CATEGORY_PAGE => Mage::helper('summa_autosuggest')->__('Category Page'),
            self::RESULTS_PAGE => Mage::helper('summa_autosuggest')->__('Search results Page'),
        );
    }
}