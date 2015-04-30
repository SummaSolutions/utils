<?php
/**
 * Created by PhpStorm.
 * User: glopez
 * Date: 27/11/14
 * Time: 17:07
 */

class Grandmarche_Matrixrate_Model_Mysql4_Carrier_Matrixrate extends Webshopapps_Matrixrate_Model_Mysql4_Carrier_Matrixrate
{
    public function getNewRate(Mage_Shipping_Model_Rate_Request $request,$zipRangeSet=0)
    {
        $read = $this->_getReadAdapter();
        $write = $this->_getWriteAdapter();

        $postcode = $request->getDestPostcode();
        $table = Mage::getSingleton('core/resource')->getTableName('matrixrate_shipping/matrixrate');

        if ($zipRangeSet && is_numeric($postcode)) {
            #  Want to search for postcodes within a range
            $zipSearchString = ' AND '.$postcode.' BETWEEN dest_zip AND dest_zip_to )';
        } else {
            $zipSearchString = $read->quoteInto(" AND ? LIKE dest_zip )", $postcode);
        }

        $deliveryType = null;
        if(!is_null($request->getShippingType())) {
            $deliveryType = $request->getShippingType();
        }

        $newdata=array();
        for ($j=0;$j<10;$j++)
        {

            $select = $read->select()->from($table);
            switch($j) {
                case 0:
                    $select->where(
                        $read->quoteInto(" (dest_country_id=? ", $request->getDestCountryId()).
                        $read->quoteInto(" AND dest_region_id=? ", $request->getDestRegionId()).
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $request->getDestCity()).
                        $zipSearchString
                    );
                    break;
                case 1:
                    $select->where(
                        $read->quoteInto(" (dest_country_id=? ", $request->getDestCountryId()).
                        $read->quoteInto(" AND dest_region_id=?  AND dest_city=''", $request->getDestRegionId()).
                        $zipSearchString
                    );
                    break;
                case 2:
                    $select->where(
                        $read->quoteInto(" (dest_country_id=? ", $request->getDestCountryId()).
                        $read->quoteInto(" AND dest_region_id=? ", $request->getDestRegionId()).
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $request->getDestCity())
                    );
                    break;
                case 3:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? ", $request->getDestCountryId()).
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $request->getDestCity()).
                        $zipSearchString
                    );
                    break;
                case 4:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? ", $request->getDestCountryId()).
                        $read->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $request->getDestCity())
                    );
                    break;
                case 5:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $request->getDestCountryId()).
                        $zipSearchString
                    );
                    break;
                case 6:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? ", $request->getDestCountryId()).
                        $read->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $request->getDestRegionId())
                    );
                    break;

                case 7:
                    $select->where(
                        $read->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $request->getDestCountryId())
                    );
                    break;
                case 8:
                    $select->where(
                        "  (dest_country_id='0' AND dest_region_id='0'".
                        $zipSearchString
                    );
                    break;

                case 9:
                    $select->where(
                        "  (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
                    );
                    break;
            }

            if (is_array($request->getMRConditionName())) {
                $i = 0;
                foreach ($request->getMRConditionName() as $conditionName) {
                    if ($i == 0) {
                        $select->where('condition_name=?', $conditionName);
                    } else {
                        $select->orWhere('condition_name=?', $conditionName);
                    }
                    // Tranform ex: 504 to 510
                    $value = ((int)($request->getData($conditionName) * 0.1) * 10) + 10;
                    $select->where('condition_from_value<=?', $value);
                    $i++;
                }
            } else {
                $value = ((int)($request->getData($request->getMRConditionName()) * 0.1) * 10) + 10;
                $select->where('condition_name=?', $request->getMRConditionName());
                $select->where('condition_from_value<=?', $value);
                $select->where('condition_to_value>=?', $value);
            }

            $select->where('website_id=?', $request->getWebsiteId());

            /*
             * Adding deliveryType filter
             */
            if(!is_null($deliveryType)) {
                $select->where('delivery_type=?', $deliveryType);
            }

            $select->order('dest_country_id DESC');
            $select->order('dest_region_id DESC');
            $select->order('dest_zip DESC');
            $select->order('condition_from_value DESC');
            /*
            pdo has an issue. we cannot use bind
            */

            $row = $read->fetchAll($select);
            if (!empty($row))
            {
                // have found a result or found nothing and at end of list!
                foreach ($row as $data) {
                    $newdata[]=$data;
                }
                break;
            }
        }
        return $newdata;
        }
}