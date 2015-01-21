<?php
/**
 * @author      Juan J Garay <jgaray@summasolutions.net>
 * @package     Reflection
 * @copyright   Summa Solutions <http://www.summasolutions.net>
 */

/*
 * utilities to get information about classes through PHP Reflection API
 */
class Guidance_Reflection_Helper_Data extends Mage_Core_Helper_Abstract {

    /* obtiene el nombre de la clase 2 niveles arriba de la indicada en los argumentos*/ 
    public function getGrandParentClassName($className){
        $selfReflection = new ReflectionClass($className);
        $parentReflection = new ReflectionClass($selfReflection->getParentClass()->getName());
        $grandParentClassName = $parentReflection->getParentClass()->getName();

        return $grandParentClassName;
    }
}
?>
