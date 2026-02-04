<?php

namespace App\Factory;


class mapping
{
    public const SHEET_MAPPING = [
        "MASTER_PRODUCTS" => [
            'MasterProductID' => [
                'fieldName' => 'MasterProductId',
                'unique' => true,
                'isParent' => false
            ],
            'ProductFamily' => [
                'fieldName' => 'ProductFamily',
            ],
            'Category' => [
                'fieldName' => 'Category',
            ],
            'Description' => [
                'fieldName' => 'Description',
            ],
            'MountType' => [
                'fieldName' => 'MountType',
            ],
            'Technology' => [
                'fieldName' => 'Technology',
            ],
            'SharedFeatures' => [
                'fieldName' => 'SharedFeatures',
                'fieldType' => 'Multiselection'
            ],
            'HeroImage' => [
                'fieldName' => 'masterImage',
                'fieldType' => 'Image'
            ]
        ],
        "PRODUCT_MASTERS" => [
            'ProductMasterID' => [
                'fieldName' => 'ProductMasterId',
                'unique' => true,
                'isParent' => false
            ],
            'MasterProductID' => [
                'fieldName' => 'MasterProductId',
                'unique' => false,
                'isParent' => true
            ],
            'FormFactor' => [
                'fieldName' => 'FormFactor',
            ],
            'WattageGroup' => [
                'fieldName' => 'Wattage',
            ],
            'BeamAngleGroup' => [
                'fieldName' => 'BeamAngleGroup',
            ],
            'AllowedCCTs' => [
                'fieldName' => 'AllowedCcts',
                'fieldType' => 'Multiselection'
            ]
        ],
        "VARIANTS" => [
            'VariantID' => [
                'fieldName' => 'VariantId',
                'unique' => true,
                'isParent' => false
            ],
            'ProductMasterID' => [
                'fieldName' => 'ProductMasterId',
                'unique' => false,
                'isParent' => true
            ],
            'SKU' => [
                'fieldName' => 'Sku',
            ],
            'CCT' => [
                'fieldName' => 'Cct',
            ],
            'Lumens' => [
                'fieldName' => 'Lumens',
            ],
            'CRI' => [
                'fieldName' => 'Cri',
            ],
            'Voltage' => [
                'fieldName' => 'Voltage',
            ],
            'Finish' => [
                'fieldName' => 'Finish',
            ],
            'Price' => [
                'fieldName' => 'Price',
            ],
            'Stock' => [
                'fieldName' => 'Stock',
            ],
            'VariantImage' => [
                'fieldName' => 'variantImages',
                'fieldType' => 'ImageGallery'
            ]
        ]
    ];
}
