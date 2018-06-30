<?php


xConfig::pushConfig(array(
    'moduleVersion'=>1,
    'iconClass' => 'i-folder2',
    'admSortIndex' => 90,
    'boostTree' => false,
    'imageListingSizeWidth' => 90,
    'imageListingSizeHeight' => 0,
    'imageListingCrop' => "4:3",
    'cutWordsTextAreaListing' => 25,
    'skuGroupListItemsPerPage' => 500,
    'dateListingFormat' => 'd-m-Y H:i:s',
    'actionable' => 1,
    'cacheInnerResources' => true,
    'cacheTree' => array
    (
        'tree' => true,
        'sku' => true,
        'searchForms' => true,
        'propertySetsTree' => true

    )

))


?>