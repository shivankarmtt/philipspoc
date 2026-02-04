<?php

namespace App\Services;

use App\Factory\common;
use Exception;


class productImportService
{
    private $header = [];
    private const DEFAULT_PRODUCT_PATH = "/Products";

    public function productImportService($excelRow, $header, $sheetDetails, $monitoringItem)
    {
        try {
            $this->header = $header;

            // Get or Create Products/Variants
            $productObject = $this->getProductObject($excelRow, $sheetDetails, $monitoringItem);

            foreach ($sheetDetails as $headerName => $fieldConfig) {
                // flips the header array for fast filter of fields
                $flipedHeader = array_flip($header);

                // skips the entry of parent identifier value
                if (array_key_exists("isParent", $fieldConfig) && $fieldConfig["isParent"]) {
                    continue;
                }

                // checks if the updating/adding field exists in header
                if (array_key_exists($headerName, $flipedHeader)) {
                    // defining setter for product class
                    $setter = "set" . $fieldConfig['fieldName'];

                    // checks if field is of special type
                    if (array_key_exists("fieldType", $fieldConfig) && !empty($fieldConfig["fieldType"])) {
                        $value = common::getFieldValues($fieldConfig["fieldType"], $excelRow[$this->getFieldIndex($headerName)], $monitoringItem);
                    } else {
                        $value = $excelRow[$this->getFieldIndex($headerName)];
                    }

                    // sets field value in object
                    $productObject->$setter($value);
                }
            }

            // saves the product object
            $productObject->save();
        } catch (Exception $e) {
            $monitoringItem->setMessage($e->getMessage());
        }
    }


    private function getProductObject($excelRow, $sheetDetails, $monitoringItem)
    {
        try {
            $getter = "";
            $parentGetter = "";
            $key = "";

            // Prepares the requiered values for product filter/creation
            foreach ($sheetDetails as $headerName => $fieldConfig) {
                // gets the product object if exists based on the check of unique defined in sheet mapping
                if (array_key_exists('unique', $fieldConfig) && $fieldConfig['unique']) {
                    $getter = "getBy" . $fieldConfig['fieldName'];
                    $key .= "_" . \Pimcore\Model\Element\Service::getValidKey($excelRow[$this->getFieldIndex($headerName)], 'object');
                }

                // gets the parent product object if exists based on the check of isParent defined in sheet mapping 
                if (array_key_exists('isParent', $fieldConfig) && $fieldConfig['isParent']) {
                    $parentGetter = "getBy" . $fieldConfig['fieldName'];
                    $parentObjectKey = $excelRow[$this->getFieldIndex($headerName)];
                    $objectParent = \Pimcore\Model\DataObject\Products::$parentGetter($parentObjectKey, true);
                }
            }

            $key = trim($key, "_");

            // gets the product object or creates one if not found
            if (!empty($key)) {
                $productObject = \Pimcore\Model\DataObject\Products::$getter($key, true);
                if (empty($productObject)) {
                    $productObject = new \Pimcore\Model\DataObject\Products;
                    $productObject->setKey($key);
                    if (!empty($objectParent)) {
                        $productObject->setParent($objectParent);
                        $productObject->setType(\Pimcore\Model\DataObject\Products::OBJECT_TYPE_VARIANT);
                    } else {
                        $productObject->setParent(\Pimcore\Model\DataObject\Service::createFolderByPath(self::DEFAULT_PRODUCT_PATH));
                    }
                    $productObject->setPublished(true);
                }
            }

            return $productObject;
        } catch (Exception $e) {
            $monitoringItem->setMessage($e->getMessage());
        }
    }


    /**
     * Gets the respective header index
     */
    private function getFieldIndex($field)
    {
        return array_search($field, $this->header);
    }
}
