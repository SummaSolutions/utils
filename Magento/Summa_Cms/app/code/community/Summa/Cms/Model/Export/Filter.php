<?php
/**
 * Summa Cms Model Export Filter Model
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Model_Export_Filter
    extends Mage_Widget_Model_Template_Filter
{
    /**
     * Will store the collected dependencies.
     *
     * @var array
     *
     */
    private $_collectedDependencies = array(
        Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK => array(),
        Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE  => array(),
    );

    /**
     * Returns all the collected dependencies.
     *
     * @return array
     *
     */
    public function getAllCollectedDependencies()
    {
        return $this->_collectedDependencies;
    }

    /**
     * Returns the collected dependencies for the given entity type.
     *
     * @param $entityType
     * @return mixed
     *
     */
    public function getCollectedDependenciesByEntityType($entityType)
    {
        return $this->_collectedDependencies[$entityType];
    }

    /**
     * Add a new collected dependency for a given entity type.
     *
     * @param $entityType
     * @param $value
     * @return Summa_Cms_Model_Export_Filter
     *
     */
    public function addCollectedDependency($entityType,$id, $value)
    {
        if (!in_array($value,$this->_collectedDependencies[$entityType])) {
            $this->_collectedDependencies[$entityType][$id] = $value;
        }
        return $this;
    }

    /**
     * Collects the dependencies for a given entity
     *
     * @param $entity Mage_Cms_Model_Block|Mage_Cms_Model_Page
     * @return array
     *
     */
    public function collectDependencies($entity)
    {
        $value = $entity->getContent();
        $recursive = true;
        $this->filter($value, $recursive);
        return $this->getAllCollectedDependencies();
    }

    /**
     * Filters the content taking into account only the widget directive.
     *
     * @param string $value
     * @param bool $recursive
     * @return mixed|string
     * @throws Exception
     *
     */
    public function filter($value, $recursive = false)
    {
        if(preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach($constructions as $index=>$construction) {
                $directive = $construction[1];
                if (strtolower($directive) == 'widget') {
                    $callback = array($this, $directive.'Directive');
                    try {
                        $replacedValue = call_user_func($callback, $construction, $recursive);
                    } catch (Exception $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }
        return $value;
    }

    /**
     * Replaces the entity id (numeric) with the entity identifier (string).
     * Magento is already prepared for this.
     *
     * @param $entityType
     * @param $params
     * @param bool $recursive
     * @return mixed
     *
     */
    public function replaceIdWithIdentifier($entityType, $params, $recursive = false)
    {
        if (isset($params[$entityType.'_id'])) {
            $candidateEntityId = $params[$entityType.'_id'];
            $collectedEntityIds = $this->getCollectedDependenciesByEntityType($entityType);
            $dependantEntity = Mage::getModel('cms/'.$entityType)->load($candidateEntityId);
            if ($dependantEntity->getId()) {
                $params[$entityType.'_id'] = $dependantEntity->getIdentifier();
                if (!in_array($dependantEntity->getIdentifier(), $collectedEntityIds)) {
                    $this->addCollectedDependency($entityType, $dependantEntity->getId(), $dependantEntity->getIdentifier());
                    if ($recursive) {
                        $this->filter($dependantEntity->getContent());
                    }
                }
            }
        }
        return $params;
    }


    /**
     * Widget directive, will replace entity ids with entity identifiers.
     *
     * @param array $construction
     * @param bool $recursive
     * @return string
     *
     */
    public function widgetDirective($construction, $recursive = false)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $params = $this->replaceIdWithIdentifier(Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK,$params,$recursive);
        $params = $this->replaceIdWithIdentifier(Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE,$params,$recursive);
        $newConstruction = $this->buildNewWidgetConstruction($params);
        return $newConstruction;
    }

    /**
     * Generates the new widget construction.
     *
     * @param $params
     * @return string
     *
     */
    private function buildNewWidgetConstruction($params)
    {
        $construction = '';
        foreach ($params as $name=>$value) {
            $construction .= ' '.$name.'="'.$value.'"';
        }
        return '{{widget'.$construction.'}}';
    }
}