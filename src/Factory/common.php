<?php

namespace App\Factory;

use Exception;


class common
{

    /**
     * For special pimcore fields
     * Converts the input into desired values
     */
    public static function getFieldValues($fieldType, $value, $monitoringItem)
    {
        try {
            $processedValue = $value;

            if (!empty($fieldType) && !empty($value)) {
                // Get values for MultiSelect Field
                if ($fieldType == "Multiselection") {
                    $processedValue = explode(", ", $value);
                }

                // Get Image data for object
                if ($fieldType == "Image") {
                    $processedValue = \Pimcore\Model\Asset\Image::getByPath($value);
                }

                // Get Image Gallery data for object
                if ($fieldType == "ImageGallery") {
                    $items = [];
                    $valueToArray = explode(", ", $value);
                    foreach ($valueToArray as $val) {
                        $image = \Pimcore\Model\Asset\Image::getByPath($val);
                        $advancedImage = new \Pimcore\Model\DataObject\Data\Hotspotimage();
                        $advancedImage->setImage($image);
                        $items[] = $advancedImage;
                    }
                    $processedValue = new \Pimcore\Model\DataObject\Data\ImageGallery($items);
                }
            }

            return $processedValue;
        } catch (Exception $e) {
            $monitoringItem->setMessage($e->getMessage());
        }
    }
}