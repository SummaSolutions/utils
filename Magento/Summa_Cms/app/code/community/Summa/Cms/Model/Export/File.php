<?php
/**
 * Summa Cms Model Export File Model
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Model_Export_File
    extends Varien_Object
{
    const MIME_TYPE = 'text/csv';

    /**
     * MIME-type for 'Content-Type' header.
     *
     * @return string
     */
    public function getContentType()
    {
        return self::MIME_TYPE;
    }

    /**
     * Will store filename of the file.
     *
     * @var null
     *
     */
    private $_filename = null;

    /**
     * Will store the file handler.
     *
     * @var null
     *
     */
    private $_fileHandler = null;

    /**
     * Will store the destination folder path.
     *
     * @var null
     *
     */
    private $_destinationFolder = null;

    /**
     * Will store the collections to export.
     *
     * @var array
     *
     */
    private $_exportCollections = array();

    /**
     * Opens the file.
     *
     * @return Summa_Cms_Model_Export_File
     *
     */
    protected function openFile()
    {
        $this->_fileHandler = fopen($this->getFullFilename(),'w');
        return $this;
    }

    /**
     * Closes the file.
     *
     * @return Summa_Cms_Model_Export_File
     *
     */
    protected function closeFile()
    {
        fclose($this->_fileHandler);
        return $this;
    }

    /**
     * Writes a row in the file.
     *
     * @param $data
     * @return Summa_Cms_Model_Export_File
     *
     */
    protected function writeRow($data)
    {
        fputcsv($this->_fileHandler, $data, ',', '"');
        return $this;
    }

    /**
     * Returns the full filename of the file.
     *
     * @return string
     *
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Returns the filename (path + filename) of the file.
     *
     * @return string
     *
     */
    public function getFullFilename()
    {
        return $this->_destinationFolder.DS.$this->_filename;
    }

    /**
     * Sets the destination folder where the file will be created.
     *
     * @param $destinationFolder
     * @return Summa_Cms_Model_Export_File
     *
     */
    public function setDestinationFolder($destinationFolder)
    {
        if(!file_exists($destinationFolder) OR !is_dir($destinationFolder)){
            mkdir($destinationFolder,0777,true);
        }
        $this->_destinationFolder = $destinationFolder;
        return $this;
    }

    /**
     * Sets the filename for the file.
     *
     * @param $filename
     * @return Summa_Cms_Model_Export_File
     *
     */
    public function setFilename($filename)
    {
        $sanitizedFilename = preg_replace('/[^0-9a-z\.\_\-]/i','',$filename);
        if ($sanitizedFilename == $filename) {
            $this->_filename = $filename;
        } else {
            Mage::throwException('Not a valid filename');
        }
        return $this;
    }

    /**
     * Generates a row in the file that indicates the headers
     * for the specific entity collection.
     *
     * @param $entityCollection
     * @return Summa_Cms_Model_Export_File
     *
     */
    public function generateRowHeadersForEntityCollection($entityCollection)
    {
        $firstItem = $entityCollection->getFirstItem();
        $headers = array_keys($firstItem->getData());
        $this->writeRow($headers);
        return $this;
    }

    /**
     * Generates a row in the file that indicates the beginning of a entity type section.
     *
     * @param $entityType
     * @return Summa_Cms_Model_Export_File
     *
     */
    public function generateRowEntityTypeSection($entityType)
    {
        $this->writeRow(array(strtoupper($entityType)));
        return $this;
    }

    /**
     * Adds a new collection by entity type to the collections to export.
     *
     * @param $entityType
     * @param $entityCollection
     * @return Summa_Cms_Model_Export_File
     *
     */
    public function addEntityCollectionToExportByEntityType($entityType, $entityCollection)
    {
        $this->_exportCollections[$entityType] = $entityCollection;
        return $this;
    }

    /**
     * Generates the file.
     *
     * @return string
     *
     */
    public function generate()
    {
        $this->openFile();
        foreach ($this->_exportCollections as $entityType=>$entityCollection)
        {
            $this->generateRowEntityTypeSection($entityType);
            $this->generateRowHeadersForEntityCollection($entityCollection);
            foreach ($entityCollection as $entity){
                $this->writeRow($entity->getData());
            }
        }
        $this->closeFile();
        return $this->getFilename();
    }
}