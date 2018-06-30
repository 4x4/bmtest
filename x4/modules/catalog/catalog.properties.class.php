<?php

use X4\Classes\XTreeEngine;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;
class catalogProperty
{
    public $tpl;
    public $_moduleLink = 'catalog';
    public $propertyName = array();
    public $psetStorage = array();
    public $psetIdToNameStorage = array();
    public $psetInfoStorage = array();


    public function __construct($class)
    {
        $this->_TMS = XRegistry::get('TMS');
        $this->_PDO = XRegistry::get('XPDO');
        $this->_EVM = XRegistry::get('EVM');
        $this->propertyName = $class;
    }

    public function safeUrlTransform($url)
    {
        return strtolower(str_replace(array('?', '&', '/', ' ', '--'), array('-', '-', '-', '-'), $url));

    }

    public function loadDefaultTpl()
    {
        static $loaded;

        if (!$loaded) {
            $this->_TMS->addFileSection(
                XRegistry::get(
                    'ADM')->loadModuleTpls($this->_moduleLink, array(array('tplName' => $this->propertyName)), true),
                true);
            $loaded = true;
        }
    }


    public static function handleSearchFilterCreatePrototypeItem($fieldData)
    {

        if ($fieldData['propertyData']['isSKU']) {
            $filterItem = new filterItem('s');
            $propertyPath = $fieldData['property'];

        } else {

            $filterItem = new filterItem('f');
            $propertyPath = $fieldData['propertySet'] . '.' . $fieldData['property'];
        }

        return array('filterItem' => $filterItem,
            'item' => array("type" => $fieldData['comparsionType'], "property" => $propertyPath));

    }


    public function renderBackOptionsTemplate()
    {
        $this->loadDefaultTpl();
        return $this->_TMS->parseSection($this->propertyName . 'Options');
    }


    /**
     * Заглушка для обработки свойства перед выводом в листинг
     */
    public function onListingView($value, $propertyInfo,$clmn)
    {
        return $value;
    }

    public function handleTypeBack($property, $value = null)
    {
        return $property;
    }

    public function handleRegularReverseUrlTransformation($transformation, $explodedQuery)
    {
        return;
    }


    public function prepareFacet($fieldName, $object, $field,&$matrix)
    {
        $fHash=md5($object['params'][$fieldName]);
        $matrix[$fieldName]['facet'][$fHash]=$object['params'][$fieldName];
        $matrix[$fieldName]['space'][$fHash][] = $object['id'];
    }

    public function buildFacet($values, $field)
    {
        return $values;
    }

    public function onListingPrepare($columns, $columnName)
    {

        return $columns;
    }

    public function handleTypeOnEdit($options, $value, $object)
    {
        return $value;
    }

    public function handleTypeOnSave($options, $value, $paramSet,$paramPath)
    {
        return $value;
    }

    public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {
        return $value;
    }

    public function handleOptions($options)
    {
        return $options;
    }

    public function handleSearchFilterSet($matrix, $field, $paramSet)
    {
        return $matrix;
    }

    public function handleSearchFilterGetFilterInfo($matrix, &$field, $outerLink = null)
    {
        return $matrix;
    }

    public function handleSearchFilterSorting($matrix, $field)
    {
        return $matrix;
    }

    public function handleSearchFilterValue($value, $options, $field)
    {
        return $value;
    }

    /* в этом методе обязательно требуется замещение старого ключа на новый
    */

    public function handleOnImport($value, $row, $rowName, $schemeRowName, $columns, $psetData, $oldRow)
    {
        $row[$rowName] = $value;
        return $row;

    }

    public function handleUrlTransformation($data, $property)
    {
        return;
    }

    public function handleOnBeforeImport($key, $columns, $context)
    {
    }


    public function renderBackTemplate()
    {
        $this->loadDefaultTpl();
        return $this->_TMS->parseSection($this->propertyName);
    }

    public function valueTransform()
    {
    }
}

class inputProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleSearchFilterSorting($matrix, $field)
    {

        $sorting = XARRAY::asKeyVal($matrix, 'value');
        if ($field['sort'] == 'asc') {
            asort($sorting);
        } else {

            arsort($sorting);
        }
        foreach ($sorting as $key => $sortItem) {
            $sorted[$key] = $matrix[$key];

        }
        return $sorted;
    }


    public function handleRegularReverseUrlTransformation($transform, $explodedQuery)
    {
        $regex = str_replace('{%F:value%}', '(\d+(\.\d+)?)', $transform['transform_url']);

        if ($transform['tree'] == 's') {
            $field = explode('.', $transform['field']);
            $field = $field[1];
        } else {
            $field = $transform['field'];
        }

        foreach ($explodedQuery as $query) {
            preg_match('/' . $regex . '/', $query, $matches);

            if (is_numeric($matches[1])) {

                return $transform['tree'] . '[' . $transform['comparsion'] . ']' . '[' . $field . ']=' . $matches[1];
            }
        }


    }


    public function handleUrlTransformation($data, $property)
    {

        $catalog = xCore::moduleFactory('catalog.back');

        if ($data['comparsion'] == 'sort') {
            $hash = Common::generateHash($property['options']['srcGroupId'] . Common::getmicrotime());

            $catalog->_TMS->generateSection($data['transform_url'], $hash);

            $field = $data['tree'] . "[{$data[comparsion]}][{$data[field]}]";

            $typeItems = array('signed', 'float', 'char');
            $sortItems = array('asc', 'desc');

            foreach ($sortItems as $item) {
                foreach ($typeItems as $itemType) {
                    $elementValue = $item . '-' . $itemType;
                    $catalog->_TMS->addMassReplace($hash, array('value' => $elementValue));
                    $to = trim($catalog->_TMS->parseSection($hash));

                    if (!empty($to)) {
                        $output[] = array('rule_id' => $data['id'], 'from' => "$field=" . $elementValue, 'to' => $to);
                    }
                }
            }


            $catalog->_commonObj->clearFieldsUrlTransform($data['id']);

            return $output;
        }

        if (empty($data['value']) && !empty($data['transform_url'])) {


            if ($data['tree'] == 's') {
                $tree = $catalog->_commonObj->_sku;
                $field = $property['basic'];
            } else {
                $tree = $catalog->_tree;
                $field = $data['field'];
            }

            $dataCatalog = $tree->selectStruct('*')->selectParams('*')->childs(1)->where(array($field, '<>', ''))->run();

            if (!empty($dataCatalog)) {

                if ($data['tree'] == 's') {
                    $result = $catalog->_commonObj->skuHandleFront($dataCatalog);

                } else {

                    $result = $catalog->_commonObj->convertToPSGAll($dataCatalog, array('doNotExtractSKU' => true));
                }

                $hash = Common::generateHash($property['options']['srcGroupId'] . Common::getmicrotime());

                $catalog->_TMS->generateSection($data['transform_url'], $hash);

                $fieldFull = $data['tree'] . "[{$data[comparsion]}][{$field}]";

                if ($data['multi']) {
                    $fieldFull .= '[]';
                }

                $fieldExploded = explode('.', $data['field']);

                $elementStack = [];

                foreach ($result as $element) {

                    if ($data['tree'] == 's') {
                        $elementValue = $element['params'][$field];

                    } else {

                        $elementValue = $element[$fieldExploded[0]][$fieldExploded[1]];
                    }

                    if (in_array($elementValue, $elementStack)) {
                        continue;

                    } else {

                        $elementStack[] = $elementValue;
                    }


                    $catalog->_TMS->addMassReplace($hash, array('object' => $element));

                    $to = trim($catalog->_TMS->parseSection($hash));


                    if (!empty($to)) {
                        $to = $this->safeUrlTransform($to);

                        $output[] = array('rule_id' => $data['id'], 'from' => "$fieldFull=" . $elementValue, 'to' => $to);
                    }

                }

                $catalog->_commonObj->clearFieldsUrlTransform($data['id']);
                return $output;
            }
        }


    }


    public function handleSearchFilterGetFilterInfo($matrix, &$field, $outerLink = false)
    {

        $item = catalogProperty::handleSearchFilterCreatePrototypeItem($field);

        $filterItem = $item['filterItem'];
        switch ($field['comparsionType']) {
            case  'equal' :


                foreach ($matrix as $key => $matrixItem) {

                    $filter = $item['item'];
                    $filter['value'] = $matrixItem['value'];
                    $filterItem->addArray($filter);

                    $matrix[$key]['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                    $matrix[$key]['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}][]";
                    $matrix[$key]['_filter']['inFilter'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $filter['type'], $field['gpth'], $filter['value']);
                }

                break;


            case  'interval' :

                $minFilter = $item['item'];
                $minFilter['type'] = 'from';
                $minFilter['value'] = $matrix['min'];
                $filterItem->addArray($minFilter);

                $maxFilter = $item['item'];
                $maxFilter['type'] = 'to';
                $maxFilter['value'] = $matrix['max'];
                $filterItem->addArray($maxFilter);

                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                $field['_filter']['filterNameMax'] = "{$filterItem->type}[to][{$maxFilter['property']}]";
                $field['_filter']['filterNameMin'] = "{$filterItem->type}[from][{$minFilter['property']}]";

                $field['_filter']['inFilterMax'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'to', $field['property']);
                $field['_filter']['inFilterMin'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'from', $field['property']);

                break;


            case 'sort':

                $filter = $item['item'];
                $filter['type'] = $field['comparsionType'];
                $filter['value'] = $field['sort'];
                $filter['override'] = true;


                if ($active = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $field['comparsionType'], $item['item']['property'])) {


                    if (strstr($active, $field['sort'])) {
                        $field['_filter']['active'] = true;

                    }

                }


                $filterItem->addArray($filter);
                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                $field['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}]";
                break;


        }

        return $matrix;

    }

}

class fileFolderProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function handleOnImport($value, $row, $rowName, $schemeRowName, $columns, $psetData, $oldRow)
    {

        $options = $columns[$schemeRowName];
        $folder = $options['fileFolder'];

        foreach ($columns as $keyRow => $val) {
            $replacment[] = '{' . $val['realName'] . '}';
            $replacer[] = $oldRow[$keyRow];
        }

        $shortFolder = str_replace($replacment, $replacer, $folder);

        $value = '/' . $shortFolder;

        $row[$rowName] = $value;
        return $row;

    }


}

class textareaProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function onListingView($value, $propertyInfo,$clmn)
    {
        $catalog = XRegistry::get('catalogBack');

        $value = XSTRING::findnCutSymbolPosition($value, " ", $catalog->_config['cutWordsTextAreaListing']);
        return $value;
    }
}

class fileProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function onListingView($value, $propertyInfo,$clmn)
    {
        return $value;
    }


    public function handleOnImport($value, $row, $rowName, $schemeRowName, $columns, $psetData, $oldRow)
    {

        $options = $columns[$schemeRowName];

        foreach ($columns as $keyRow => $val) {
            $replacment[] = '{' . $val['realName'] . '}';
            $replacer[] = $oldRow[$keyRow];
        }

        if ($file = $options['file']) {

            $value = str_replace($replacment, $replacer, $file);
        }

        $row[$rowName] = $value;
        return $row;

    }

}


class dateProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleTypeBack($property, $value = null)
    {
        return $property;

    }


    public function handleSearchFilterSorting($matrix, $field)
    {

        $sorting = XARRAY::asKeyVal($matrix, 'value');
        if ($field['sort'] == 'asc') {
            asort($sorting);
        } else {

            arsort($sorting);
        }
        foreach ($sorting as $key => $sortItem) {
            $sorted[$key] = $matrix[$key];

        }
        return $sorted;
    }

    public function handleSearchFilterGetFilterInfo($matrix, &$field, $outerLink = false)
    {

        $item = catalogProperty::handleSearchFilterCreatePrototypeItem($field);

        $filterItem = $item['filterItem'];
        switch ($field['comparsionType']) {
            case  'equal' :


                foreach ($matrix as $key => $matrixItem) {

                    $filter = $item['item'];
                    $filter['value'] = $matrixItem['value'];
                    $filterItem->addArray($filter);

                    $matrix[$key]['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                    $matrix[$key]['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}][]";
                    $matrix[$key]['_filter']['inFilter'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $filter['type'], $field['gpth'], $filter['value']);
                }

                break;


            case  'interval' :

                $minFilter = $item['item'];
                $minFilter['type'] = 'from';
                $minFilter['value'] = $matrix['min'];
                $filterItem->addArray($minFilter);

                $maxFilter = $item['item'];
                $maxFilter['type'] = 'to';
                $maxFilter['value'] = $matrix['max'];
                $filterItem->addArray($maxFilter);

                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                $field['_filter']['filterNameMax'] = "{$filterItem->type}[to][{$maxFilter['property']}]";
                $field['_filter']['filterNameMin'] = "{$filterItem->type}[from][{$minFilter['property']}]";

                $field['_filter']['inFilterMax'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'to', $field['property']);
                $field['_filter']['inFilterMin'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'from', $field['property']);

                break;


            case 'sort':


                $filter = $item['item'];
                $filter['type'] = $field['comparsionType'];
                $filter['value'] = $field['sort'];
                $filter['override'] = true;


                if ($active = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $field['comparsionType'], $item['item']['property'])) {


                    if (strstr($active, $field['sort'])) {
                        $field['_filter']['active'] = true;

                    }

                }


                $filterItem->addArray($filter);
                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                $field['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}]";
                break;


        }

        return $matrix;

    }


    public function handleTypeOnSave($property, $value, $paramSet,$paramPath)
    {

        return strtotime($value);

    }

    public function handleTypeOnEdit($property, $value,$object)
    {
        $catalog = XRegistry::get('catalogBack');
        return date($catalog->_config['dateListingFormat'], $value);
    }

    public function onListingView($value, $propertyInfo, $clmn)
    {
        $catalog = XRegistry::get('catalogBack');
        return date($catalog->_config['dateListingFormat'], $value);

    }
}

class imageProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleOnImport($value, $row, $rowName, $schemeRowName, $columns, $psetData, $oldRow)
    {

        $options = $columns[$schemeRowName];

        foreach ($columns as $keyRow => $val) {
            $replacment[] = '{' . $val['realName'] . '}';
            $replacer[] = $oldRow[$keyRow];
        }

        if ($folder = $options['getFirstFromFolder']) {
            $shortFolder = str_replace($replacment, $replacer, $folder);
            $folder = PATH_ . $shortFolder;
            if (is_dir($folder)) {
                foreach (new DirectoryIterator($folder) as $file) {
                    if ($file->isFile()) {
                        $value = '/' . $shortFolder . $file->getFilename();

                    }
                }
            }

        } elseif ($image = $options['image']) {

            $value = str_replace($replacment, $replacer, $image);
        }

        $row[$rowName] = $value;
        return $row;

    }
    
    
    public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {        
    
        if($property['options']['width'] or $property['options']['height'])
        {
            
          
            if (Common::isFileExists($value)) 
            {
                $value = XRegistry::get('ENHANCE')->imageTransform(array('r' => array
                (
                    'w' => $property['options']['width'],
                    'h' => $property['options']['height'],
                    'r' => $property['options']['proportions']

                )), array('value' => $value));
            
            } else {            
                $value = '';        
            }
        }
        
        return $value;
       
    }


    public function onListingView($value, $propertyInfo,$clmn)
    {
        $catalog = XRegistry::get('catalogBack');

        if (Common::isFileExists($value)) {
            $value = XRegistry::get('ENHANCE')->imageTransform(array('r' => array
            (
                'w' => $catalog->_config['imageListingSizeWidth'],
                'h' => $catalog->_config['imageListingSizeHeight']

            )), array('value' => $value));
        } else {
            $value = '';
        }

        return $value;
    }
}

class checkboxProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function handleSearchFilterGetFilterInfo($matrix, &$field, $outerLink = false)
    {

        $item = catalogProperty::handleSearchFilterCreatePrototypeItem($field);

        $filterItem = $item['filterItem'];
        switch ($field['comparsionType']) {
            case  'equal' :


                foreach ($matrix as $key => $matrixItem) {

                    $filter = $item['item'];
                    $filter['value'] = $matrixItem['value'];
                    $filterItem->addArray($filter);

                    $matrix[$key]['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                    $matrix[$key]['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}][]";
                    $matrix[$key]['_filter']['inFilter'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $filter['type'], $field['gpth'], $filter['value']);
                }

                break;

        }

        return $matrix;

    }


    public function handleUrlTransformation($data, $property)
    {

        $catalog = XRegistry::get('catalogBack');

        if (empty($data['value']) && !empty($data['transform_url'])) {
            $dataCatalog = $catalog->_tree->selectStruct('*')->selectParams('*')->childs(1)->where(array($data['field'], '<>', ''))->run();

            if (!empty($dataCatalog)) {
                $result = $catalog->_commonObj->convertToPSGAll($dataCatalog, array('doNotExtractSKU' => true));

                $hash = Common::generateHash($property['options']['srcGroupId'] . Common::getmicrotime());

                $catalog->_TMS->generateSection($data['transform_url'], $hash);

                $field = $data['tree'] . "[{$data[comparsion]}][{$data[field]}]";

                if ($data['multi']) {
                    $field .= '[]';
                }

                $fieldExploded = explode('.', $data['field']);

                $elementStack = [];

                foreach ($result as $element) {
                    $elementValue = $element[$fieldExploded[0]][$fieldExploded[1]];

                    if (in_array($elementValue, $elementStack)) {
                        continue;

                    } else {

                        $elementStack[] = $elementValue;
                    }

                    $catalog->_TMS->addMassReplace($hash, array('object' => $element));

                    $to = trim($catalog->_TMS->parseSection($hash));


                    if (!empty($to)) {
                        $to = $this->safeUrlTransform($to);

                        $output[] = array('rule_id' => $data['id'], 'from' => "$field=" . $elementValue, 'to' => $to);
                    }

                }


                $catalog->_commonObj->clearFieldsUrlTransform($data['id']);
                return $output;
            }
        }


    }

}

class fuserProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}

class currencyIshopProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleRegularReverseUrlTransformation($transform, $explodedQuery)
    {
        $regex = str_replace('{%F:value%}', '(\d+(\.\d+)?)', $transform['transform_url']);

        $field = explode('.', $transform['field']);
        foreach ($explodedQuery as $query) {
            preg_match('/' . $regex . '/', $query, $matches);

            if (is_numeric($matches[1])) {
                return $transform['tree'] . '[' . $transform['comparsion'] . ']' . '[' . $field[1] . ']=' . $matches[1];
            }
        }


    }

    public function handleTypeBack($property, $value = null)
    {
        $ishop = xCore::moduleFactory('ishop.back');

        if ($property['defaultValues'] = $ishop->_commonObj->getCurrenciesList()) {
            $property['defaultValues'] = XHTML::arrayToXoadSelectOptions($property['defaultValues']);
        }

        return $property;
    }


    public function prepareFacet($fieldName, $object, $field,&$matrix)
    {
        if($matrix[$fieldName]['facet']['min']===null)$matrix[$fieldName]['facet']['min']=$object['params'][$fieldName];

        if($object['params'][$fieldName]<$matrix[$fieldName]['facet']['min'])
        {
            $matrix[$fieldName]['facet']['min']=$object['params'][$fieldName];
        }

        if($object['params'][$fieldName]>$matrix[$fieldName]['facet']['max'])
        {
            $matrix[$fieldName]['facet']['max']=$object['params'][$fieldName];
        }

    }


    public function handleOnImport($value, $row, $rowName, $schemeRowName, $columns, $psetData, $oldRow)
    {
        static $currenciesList;

        if (!$currenciesList) {
            $ishop = xCore::moduleFactory('ishop.back');
            $currencies = $ishop->_commonObj->getCurrenciesList();
            $currencies = array_flip($currencies);
        }


        $optionsData = $columns[$schemeRowName];

        $value = str_replace(array(' ', ','), array('', ''), $value);
        if ($optionsData['option']) {
            $option = $optionsData['option'];

            if ($option == 'currency') {
                $value = $currencies[$value];
            }

            $row[$rowName . '__' . $option] = $value;
        } else {

            $row[$rowName] = $value;
        }

        return $row;

    }


    public function handleSearchFilterValue($value, $options, $field)
    {
        if (is_array($value)) {
            return $value['nonDiscountedValue'];
        } else {
            $field['basic'] = $field['property'];
            $object['params'] = $options;
            $object['obj_type'] = '_CATOBJ';
            $value = $this->handleTypeFront($value, $field, $object, $field['propertySet']);
            return $value['nonDiscountedValue'];
        }
    }


    public function handleSearchFilterGetFilterInfo($matrix, &$field, $outerLink = false)
    {
        $item = catalogProperty::handleSearchFilterCreatePrototypeItem($field);

        $filterItem = $item['filterItem'];
        switch ($field['comparsionType']) {
            case  'interval' :

                $minFilter = $item['item'];
                $minFilter['type'] = 'from';
                $minFilter['value'] = $matrix['min']['nonDiscountedValue'];
                $filterItem->addArray($minFilter);
                $maxFilter = $item['item'];
                $maxFilter['type'] = 'to';
                $maxFilter['value'] = $matrix['max']['nonDiscountedValue'];
                $filterItem->addArray($maxFilter);


                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                $field['_filter']['filterNameMax'] = "{$filterItem->type}[priceto][{$maxFilter['property']}]";
                $field['_filter']['filterNameMin'] = "{$filterItem->type}[pricefrom][{$minFilter['property']}]";

                //todo есть проблема с обработкой цены по каталогу в случае если она задана в объекте а не в ску,  должно быть $setName .$field['property']              
                $field['_filter']['inFilterMax'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'priceto', $field['property']);
                $field['_filter']['inFilterMin'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'pricefrom', $field['property']);

                break;


            case 'sort':

                $item['item']['property'] = $item['item']['property'] . '__in__' . $_SESSION['cacheable']['currency']['basic'];
                $filter = $item['item'];
                $filter['override'] = true;
                $filter['type'] = $field['comparsionType'];
                $filter['value'] = $field['sort'];


                if ($active = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $field['comparsionType'], $item['item']['property'])) {

                    if ($active == $field['sort']) {
                        $field['_filter']['active'] = true;

                    }

                    /* if($field['_filter']['active']=='asc')
                    {
                        $filter['value']='desc';
                    }else{
                        $filter['value']='asc';
                    }
                    */
                }
                $filterItem->addArray($filter);


                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);

                break;


        }

        return $matrix;

    }

    public function toCurrency($value, $fromCurrency, $toCurrency)
    {
        return ($fromCurrency / $toCurrency) * $value;
    }

    public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {
        static $currencyList;
        static $mainCurrency;

        $ishop = xCore::moduleFactory('ishop.back');

        $mainCurrency = $ishop->_commonObj->getCurrentCurrency();

        if (!$currencyList) {
            $currencyList = $ishop->_commonObj->getCurrenciesList(true);
        }


        if ($object['obj_type'] != '_CATOBJ') {
            $basicPath = $property['basic'];

        } else {

            $basicPath = $setName . '.' . $property['basic'];
        }


        $resultEvent = $this->_EVM->fire('catalog.property.currencyIshopProperty:beforeHandleTypeFront',
            array('instance' => $this,
                    'basicPath' => $basicPath,
                    'object' => $object,
                    'property' => $property,
                    'value' => $value));

        if (!empty($resultEvent)) {
            $object = $resultEvent['object'];
            $value = $resultEvent['value'];
        }


        $currencyId = $object['params'][$basicPath . '__currency'];

        $svalue['currency'] = $currencyList[$currencyId];
        $svalue['mainCurrency'] = current($mainCurrency);
        $svalue['realValueBeforeDiscount'] = $value;
        $svalue['value'] = $this->toCurrency($value, $svalue['currency']['rate'], $svalue['mainCurrency']['rate']);
        $svalue['nonDiscountedValue'] = $svalue['value'];

        $svalue['discounted'] = false;

        if ($discount = $object['params'][$basicPath . '__discount']) {
            $value = $discount;
            $discountInCurrentCurrency = $this->toCurrency($discount, $svalue['currency']['rate'], $svalue['mainCurrency']['rate']);
            $svalue['discountPercent'] = round((100 * ($svalue['value'] - $discountInCurrentCurrency) / $svalue['value']));
            $svalue['discounted'] = true;
            $svalue['value'] = $discountInCurrentCurrency;

        }

        $svalue['currency'] = $currencyList[$currencyId];
        $svalue['mainCurrency'] = current($mainCurrency);
        $svalue['realValue'] = $value;


        $svalue['nonDiscountedValueFormatted'] = number_format($svalue['nonDiscountedValue'], $svalue['mainCurrency']['divider'], $svalue['mainCurrency']['separator'], ' ');
        $svalue['valueFormatted'] = number_format($svalue['value'], $svalue['mainCurrency']['divider'], $svalue['mainCurrency']['separator'], ' ');


        $sv = $this->_EVM->fire('catalog.property.currencyIshopProperty:afterHandleTypeFront', array('instance' => $this, 'value' => $svalue));

        if (!empty($sv)) {
            $svalue = $sv;
        }


        return $svalue;
    }
}

class currencyProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}

class selectorProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function handleUrlTransformation($data, $property)
    {

        $catalog = xCore::moduleFactory('catalog.back');

        if (empty($data['value'])) {

            $dataCatalog = $catalog->_tree->selectStruct('*')->selectParams('*')->childs($property['options']['srcGroupId'], 1)->run();
            if (!empty($dataCatalog)) {
                $result = $catalog->_commonObj->convertToPSGAll($dataCatalog, array('doNotExtractSKU' => true));

                $hash = Common::generateHash($property['options']['srcGroupId'] . Common::getmicrotime());

                $catalog->_TMS->generateSection($data['transform_url'], $hash);

                $fieldExploded = explode('.', $data['field']);

                if ($data['tree'] == 'f') {
                    $field = $data['tree'] . "[{$data[comparsion]}][{$data[field]}]";

                } else {
                    $field = $data['tree'] . "[{$data[comparsion]}][{$fieldExploded[1]}]";
                }


                foreach ($result as $element) {

                    $catalog->_TMS->addMassReplace($hash, array('object' => $element));
                    $to = trim($catalog->_TMS->parseSection($hash));

                    if (!empty($to)) {
                        $to = $this->safeUrlTransform($to);
                        $output[] = array('rule_id' => $data['id'], 'from' => "$field=" . $element['_main']['id'], 'to' => $to);

                        if ($data['multi']) {
                            $output[] = array('rule_id' => $data['id'], 'from' => "{$field}[]=" . $element['_main']['id'], 'to' => $to);
                        }
                    }

                }

                $catalog->_commonObj->clearFieldsUrlTransform($data['id']);
                return $output;
            }
        }


    }

    public function handleSearchFilterSorting($matrix, $field)
    {


        if (empty($field['sortKey'])) {
            $field['sortKey'] = '_main.Name';
        }

        $sortKeyExpl = explode('.', $field['sortKey']);

        $sorting = XARRAY::asKeyVal($matrix, 'value');


        foreach ($sorting as $k => $sortItem) {
            $sortingItems[$k] = $sortItem[$sortKeyExpl[0]][$sortKeyExpl[1]];
        }

        if ($field['sort'] == 'asc') {
            asort($sortingItems);

        } else {

            arsort($sortingItems);
        }


        foreach ($sortingItems as $key => $sortItem) {
            $sorted[$key] = $matrix[$key];

        }
        return $sorted;
    }

    public function  buildFacet($facet, $field)
    {
        if(!empty($facet['facet'])){

            $innerSpace=$facetStack=array();

            foreach($facet['facet'] as $key=>$values)
            {
                $values=json_decode($values, true);

                $facetStack=array_merge($facetStack,$values);

                if(!empty($values)) {
                    foreach($values as $value) {
                        $innerSpace[$value]=$facet['space'][$key];
                    }
                }

            }

            if(!empty($facetStack)) {

                $facetStack = XRegistry::get('catalogBack')->_tree->selectStruct('*')->selectParams('*')->where(array('@id', '=', array_unique($facetStack)))->run();
                return array('facet'=>XRegistry::get('catalogBack')->_commonObj->convertToPSGAll($facetStack),'space'=>$innerSpace);

            }
        }
    }

    public function handleTypeBack($property, $value = null)
    {
        $catalog = XRegistry::get('catalogBack');
        
        if ($id = $property['options']['srcGroupId']) {
            $property['defaultValues'] = $catalog->_commonObj->_tree->selectStruct(array('id'))->selectParams(
                array('Name'))->childs($id,1)->format('valparams', 'id',
                'Name')->run();
            $property['defaultValues'] = XHTML::arrayToXoadSelectOptions($property['defaultValues']);
        
        }elseif($deep = $property['options']['deep']&&$deepBasic=$property['options']['deepBasic'])
        {            
            
             $itemId= $catalog->_commonObj->_tree->selectStruct(array('id'))->childs($value['id'],(int)$deep)->where(array('@basic','=',$deepBasic))->run();                
             
             if(!empty($itemId))
             {
                $property['defaultValues']=$catalog->_commonObj->_tree->selectStruct(array('id'))->selectParams(array('Name'))->childs($itemId[0]['id'],1)->format('valparams', 'id','Name')->run();
                

                $property['defaultValues'] = XHTML::arrayToXoadSelectOptions($property['defaultValues']);
             }
            
        }

        return $property;
    }

    public function onListingView($values, $name, $clmn)
    {
        static $nameCollector = array();

        $values = json_decode($values, true);

        if (isset($values)) {
            if (!empty($nameCollector)) {
                $grabFromDb = array_diff($values, array_keys($nameCollector));
            } else {
                $grabFromDb = $values;
            }

            $catalog = XRegistry::get('catalogBack');

            if (!empty($grabFromDb)) {
                $dta = $catalog->_tree->selectParams(array('Name'))->where(array
                (
                    '@id',
                    '=',
                    $grabFromDb
                ))->format('valparams', 'id', 'Name')->run();

                if (isset($dta) && is_array($dta)) {
                    $nameCollector = $nameCollector + $dta;
                }
            }

            foreach ($values as $val) {
                $nc[] = $nameCollector[$val];
            }

            return implode(',', $nc);
        }
    }


    public function handleOnBeforeImport($key, $columns, $importContext)
    {

        $query = "select `$key` from `importer` group by `$key`";
        $options = $columns[$key];
        $catalog = xRegistry::get('catalogBack');
        $result = $this->_PDO->query($query);

        if (empty($options['separator'])) {
            $options['separator'] = ',';
        }

        $importContext->import->proccesor->addColumnCopy('old.' . $key, $key);

        if ($selectors = $result->fetchAll(PDO::FETCH_COLUMN, 0)) {


            $initialSelectors = array_filter($selectors);
            $selectorStack = array();

            if (!empty($selectors)) {

                foreach ($initialSelectors as &$selector) {
                    $separated = explode($options['separator'], $selector);
                    $separated = array_map('trim', $separated);
                    $selectorStack = array_merge($selectorStack, $separated);
                    $selectorHash[md5($selector)] = $separated;
                }

                $selectorStack = array_unique($selectorStack);

                $existedSelectors = $catalog->_tree->selectStruct('*')->selectParams('*')->childs($options['source'], 1)->where(array
                (
                    $options['key'],
                    '=',
                    $selectorStack
                ))->format('paramsval', $options['key'], 'id')->run();


                //создаем объекты селекторов
                foreach ($selectorStack as $selectorObject) {
                    if (!isset($existedSelectors[$selectorObject])) {
                        $paramSet = array('PropertySetGroup' => $options['PropertySetGroup']);
                        $paramSet[$options['key']] = $selectorObject;

                        $objId = $catalog->_tree->initTreeObj($options['source'], '%SAMEASID%', '_CATOBJ', $paramSet);

                        $map[$selectorObject] = $objId;

                    } else {
                        $map[$selectorObject] = $existedSelectors[$selectorObject];

                    }

                }

                //конвертируем в карту импорта селекторов
                foreach ($selectorHash as &$item) {
                    $parts = array();

                    foreach ($item as $part) {

                        $parts[] = $map[$part];
                    }

                    $item = json_encode($parts);
                }


                if (isset($selectorHash)) {
                    foreach ($initialSelectors as $id => $element) {
                        $element = str_replace('\'', "''", $element);
                        $id = $selectorHash[md5($element)];
                        $query = "update `importer` set `{$key}`='{$id}'   where `{$key}`='{$element}' ";
                        $this->_PDO->exec($query);

                    }

                }


            }

        }

    }


    public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {
        static $chosenSelectors;

        if ($value) {
            if (is_array($value)) {
                $json = $value;
            } else {
                $json = json_decode($value, true);
            }

            if (!empty($json)) {
                $groupId = $property['options']['srcGroupId'];
                $catalog = xCore::moduleFactory('catalog.front');

                if($deep = $property['options']['deep']&&$deepBasic=$property['options']['deepBasic'])
                {

                    $itemId = $catalog->_commonObj->_tree->selectStruct(array('id'))->singleResult()->childs($object['id'],(int)$deep)->where(array('@basic','=',$deepBasic))->run();
                    $groupId = $itemId['id'];
                }

                if (!$chosenSelectors[$groupId] && isset($groupId) && $groupId) {
                    if ($chosenSelectors[$groupId] = $catalog->_tree->selectStruct('*')->selectParams('*')->where(array
                    (
                        '@ancestor',
                        '=',
                        (int)$groupId
                    ))->format('keyval', 'id')->run()
                    ) {
                        $chosenSelectorsNew = array();
                        foreach ($chosenSelectors[$groupId] as $valKey => $val) {
                                $val = $catalog->_commonObj->convertToPSG($val);

                                $valKey = '0' . $valKey;
                                $chosenSelectorsNew[$groupId][$valKey] = $val;
                        }

                        $chosenSelectors = $chosenSelectorsNew;
                    }
                }

                if (count($json) == 1) {
                    if (is_array($json)){
                        return $chosenSelectors[$groupId]['0'.$json[0]];
                    } else {
                        return $chosenSelectors[$groupId]['0' . $json];
                    }
                } else {
                    foreach ($chosenSelectors[$groupId] as $selectorKey => $selectorValue) {
                        if (in_array((int)$selectorKey, $json)){
                            if($property['options']['assoc']&&is_array($value)) {
                                $result[$selectorKey] = $chosenSelectors[$groupId][$selectorKey];
                            } else {
                                $result[] = $chosenSelectors[$groupId][$selectorKey];
                            }
                        }
                    }
                    return $result;
                }
            }
        }
    }

    public function handleOptions($options)
    {
        $catalog = XRegistry::get('catalogBack');

        if ($options['srcGroupId']) {
            $node = $catalog->_commonObj->_tree->selectStruct(
                array('id'))->getParamPath('Name')->where(array
            (
                '@id',
                '=',
                $options['srcGroupId']
            ))->run();

            $options['srcGroup'] = $node['paramPathValue'];
        }

        return $options;
    }


    public function handleSearchFilterValue($value, $options, $field)
    {

        if (is_array($value)) {
            if (isset($value['_main']['id'])) {
                $gradedValue[] = $value['_main']['id'];
            }

            return $gradedValue;

        } elseif ($value) {
            return json_decode($value, true);
        }
    }


    public function handleSearchFilterGetFilterInfo($matrix, &$field, $outerLink = false)
    {
        $item = catalogProperty::handleSearchFilterCreatePrototypeItem($field);

        $filterItem = $item['filterItem'];


        switch ($field['comparsionType']) {
            case  'equal' :
                foreach ($matrix as $key => $matrixItem) {

                    $filterItem->clear();
                    $filterItem = $item['filterItem'];


                    $filter = $item['item'];
                    $filter['type'] = 'like';
                    $filter['value'] = $matrixItem['value']['_main']['id'];
                    $filterItem->addArray($filter);


                    $matrix[$key]['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                    $matrix[$key]['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}][]";

                    $matrix[$key]['_filter']['inFilter'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $filter['type'], $filter['property'], $filter['value']);

                }

                break;

        }

        return $matrix;

    }

    public function handleTypeOnSave($property, $value, $paramSet,$paramPath)
    {

        if(!empty($value)&&$property['options']['onlyOne']==1)
        {

          $value= '["'.$value.'"]';
        }

        return $value;


    }


    public function handleSearchFilterSet($matrix, $field, $paramSet)
    {

        $elementStack = [];
        $elementStackActive = [];

        if (!empty($matrix)) {
            $elementValuesCounter = [];
            foreach ($matrix as $element) {
                if (is_array($element['value'])) {
                    $elementStack = array_merge($elementStack, $element['value']);
                    if ($element['_filter']['active']) {
                        $elementStackActive = array_merge($elementStackActive, $element['value']);
                    }
                }


            }


            if (isset($elementStack)) $elementStack = array_unique($elementStack);
            if (isset($elementStackActive)) $elementStackActive = array_unique($elementStackActive);


            $selectorElements = XRegistry::get('catalogFront')->_commonObj->_tree->selectStruct('*')->selectParams('*')->where(array('@id', '=', $elementStack))->run();


            if ($selectorElements) {


                $selectorElements = XRegistry::get('catalogFront')->_commonObj->convertToPSGAll($selectorElements);
                $exitMatrix = [];
                $j = 0;
                foreach ($selectorElements as $value) {

                    $exitMatrix[$j]['value'] = $value;

                    $exitMatrix[$j]['_filter']['counter'] = $field['_filter']['counter'][$value['_main']['id']];

                    $exitMatrix[$j]['_filter']['counterActive'] = $field['_filter']['counterActive'][$value['_main']['id']] ? $field['_filter']['counterActive'][$value['_main']['id']] : $field['_filter']['counter'][$value['_main']['id']];

                    if (in_array($value['_main']['id'], $elementStackActive)) {
                        $exitMatrix[$j]['_filter']['active'] = 1;
                    }

                    $j++;

                }

                return $exitMatrix;

            }

        }

    }
}


class connectionSKUProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }



      public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {

        if($property['options']['calculate']&&is_array($value))
        {

           $catalog = XRegistry::get('catalogFront');
           $skuObjects=null;

           $sku = $catalog->_sku->selectStruct('*')->selectParams('*')->where(array('@id','=',$value))->run();

            if (!empty($sku)) {

                $skuObjects = $catalog->_commonObj->skuHandleFront($sku);
            }

        return $skuObjects;

       }

       return $value;
    }



    public function handleOnBeforeImport($key, $columns, $importContext)
    {

        $query = "select `$key` from `importer` group by `$key`";
        $options = $columns[$key];
        $catalog = XRegistry::get('catalogBack');
        $result = $this->_PDO->query($query);

        $importContext->import->proccesor->addColumnCopy('old.' . $key, $key);

        if ($connections = $result->fetchAll(PDO::FETCH_COLUMN, 0)) {
            $connections = array_filter($connections);
            $connectionsStack = array();
            foreach ($connections as $connect) {
                $subConnections = explode(',', $connect);
                $subConnections = array_map('trim', $subConnections);
                $connectionsExploded[$connect] = $subConnections;
                $connectionsStack = array_merge($connectionsStack, $subConnections);
            }


            if (count($connectionsStack) > 0) {

                $existedConnections = $catalog->_commonObj->_sku->selectStruct('*')->selectParams('*')->where(array
                (
                    $options['property'],
                    '=',
                    $connectionsStack
                ))->format('paramsval', $options['property'], 'id')->run();


                if ($existedConnections) {
                    foreach ($connectionsExploded as $connKey => $oneConnect) {
                        foreach ($oneConnect as $connect) {
                            if ($existedConnections[$connect]) $outputConnections[$connKey][] = $existedConnections[$connect];

                        }
                        $outputConnections[$connKey] = json_encode($outputConnections[$connKey]);
                    }

                }


                if (isset($outputConnections)) {
                    foreach ($outputConnections as $id => $element) {

                        $query = "update `importer` set `{$key}`='{$element}'   where `{$key}`='{$id}' ";
                        $this->_PDO->exec($query);

                    }

                }


            }


        }


    }

    public function handleTypeOnEdit($property, $value, $object)
    {

        $catalog = XRegistry::get('catalogBack');


        if (is_array($value)) {
            if ($nodesSku = $catalog->_commonObj->_sku->selectStruct('*')->selectParams(array('Name'))->where(array
            (
                '@id',
                '=',
                $value
            ))->format('keyval', 'id')->run()
            ) {


                $catObjConnection = XARRAY::arrToLev($nodesSku, 'netid', 'params', 'Name');
                if ($nodesObj = $catalog->_commonObj->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array
                (
                    '@id',
                    '=',
                    array_keys($catObjConnection)
                ))->run()
                ) ;


                $nodesObj = XARRAY::arrToKeyArr($nodesObj, 'id', 'paramPathValue');


                foreach ($value as $node) {
                    $exvalue[] = array
                    (
                        'sid' => $node,
                        'name' => $nodesObj[$nodesSku[$node]['netid']] . '/[' . $nodesSku[$node]['params']['Name'] . ']'
                    );
                }
            }
        }

        return $exvalue;
    }
}


class connectionProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleOnBeforeImport($key, $columns, $importContext)
    {

        $query = "select `$key` from `importer` group by `$key`";
        $options = $columns[$key];
        $catalog = XRegistry::get('catalogBack');
        $result = $this->_PDO->query($query);

        $importContext->import->proccesor->addColumnCopy('old.' . $key, $key);

        if ($connections = $result->fetchAll(PDO::FETCH_COLUMN, 0)) {
            $connections = array_filter($connections);
            $connectionsStack = array();
            foreach ($connections as $connect) {
                $subConnections = explode(',', $connect);
                $subConnections = array_map('trim', $subConnections);
                $connectionsExploded[$connect] = $subConnections;
                $connectionsStack = array_merge($connectionsStack, $subConnections);
            }


            if (count($connectionsStack) > 0) {

                $promt = $catalog->_commonObj->_tree->selectStruct('*')->selectParams('*')->where(array
                (
                    $options['property'],
                    '=',
                    $connectionsStack
                ))->format('paramsval', $options['property'], 'id');


                if ($options['folderId']) {
                    $promt->childs($options['folderId']);
                }

                $existedConnections = $promt->run();


                if ($existedConnections) {
                    foreach ($connectionsExploded as $connKey => $oneConnect) {
                        foreach ($oneConnect as $connect) {
                            if ($existedConnections[$connect]) $outputConnections[$connKey][] = $existedConnections[$connect];

                        }
                        $outputConnections[$connKey] = json_encode($outputConnections[$connKey]);
                    }

                }


                if (isset($outputConnections)) {
                    foreach ($outputConnections as $id => $element) {

                        $query = "update `importer` set `{$key}`='{$element}'   where `{$key}`='{$id}' ";
                        $this->_PDO->exec($query);

                    }

                }


            }


        }
    }


    public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {
        if (!is_array($value)) {
            $json = json_decode($value, true);
        }

        if ($json != false){
            $value = $json;
        }


        $resultEvent = $this->_EVM->fire('catalog.property.connection:beforeHandleTypeFront',array('instance' => $this,'object' => $object, 'property' => $property, 'value' => $value));

        if(!empty($resultEvent['value'])){
                $value=$resultEvent['value'];
        }

        if($property['options']['calculate']&&is_array($value))
        {

            $catalog = XRegistry::get('catalogFront');
            $objects=null;
            $objects = $catalog->_tree->selectStruct('*')->selectParams('*')->where(array('@id','=',$value))->run();

            if (!empty($objects)) {

                $objects = $catalog->_commonObj->convertToPSGAll($objects);
            }

            return $objects;

       }

       return $value;
    }

    public function handleTypeBack($property, $value = null)
    {

        $catalog = XRegistry::get('catalogBack');
        $property['defaultValues']['propertySetGroups']=$catalog->_commonObj->getPropertyGroupSerialized($psg);


        return $property;
    }

    public function handleTypeOnEdit($property, $value,$object)
    {
        $catalog = XRegistry::get('catalogBack');

        if (is_array($value)) {
            if ($nodesPath = $catalog->_commonObj->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array
            (
                '@id',
                '=',
                $value
            ))->run()
            ) {
                $value = array();

                foreach ($nodesPath as $node) {
                    $value[] = array
                    (
                        'sid' => $node['id'],
                        'name' => $node['paramPathValue']
                    );
                }

            } else {
                return false;
            }

            return $value;
        }

    }
}


class searchFormProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleTypeBack($property, $value = null)
    {

        $catalog = XRegistry::get('catalogBack');

        if ($childs = $catalog->_commonObj->_searchForms->selectStruct(array(
            'id',
        ))->selectParams('*')->childs(1, 1)->format('valparams', 'id', 'Name')->run()
        ) {

            $property['defaultValues'] = XHTML::arrayToXoadSelectOptions($childs, false, true);

        }

        return $property;
    }


}

class mainProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleSearchFilterSorting($matrix, $field)
    {

        $sorting = XARRAY::asKeyVal($matrix, 'value');
        if ($field['sort'] == 'asc') {
            asort($sorting);
        } else {

            arsort($sorting);
        }
        foreach ($sorting as $key => $sortItem) {
            $sorted[$key] = $matrix[$key];

        }
        return $sorted;
    }


    public function handleUrlTransformation($data, $property)
    {

        $catalog = XRegistry::get('catalogBack');

        if (empty($data['value'])) {
            $dataCatalog = $catalog->_tree->selectStruct('*')->selectParams('*')->childs(1) - where(array($data['field'], '<>', '')) > run();

            if (!empty($dataCatalog)) {
                $result = $catalog->_commonObj->convertToPSGAll($dataCatalog, array('doNotExtractSKU' => true));

                $hash = Common::generateHash($property['options']['srcGroupId'] . Common::getmicrotime());
                $catalog->_TMS->generateSection($data['transform_url'], $hash);

                $field = $data['tree'] . "[{$data[comparsion]}][{$data[field]}]";


                if (isset($data['multi'])) {
                    $data['id'] .= '[]';
                }


                foreach ($result as $element) {
                    $catalog->_TMS->addMassReplace($hash, array('object' => $element));

                    $to = trim($catalog->_TMS->parseSection($hash));

                    if (!empty($to)) {
                        $to = $this->safeUrlTransform($to);

                        $output[] = array('rule_id' => $data['id'], 'field' => $field, 'from' => "$field=" . $element['_main']['id'], 'to' => $to);
                    }

                }


                $catalog->_commonObj->clearFieldsUrlTransform($data['id']);
                return $output;
            }
        }


    }


    public function handleSearchFilterGetFilterInfo($matrix, &$field, $outerLink = false)
    {

        $item = catalogProperty::handleSearchFilterCreatePrototypeItem($field);

        $filterItem = $item['filterItem'];
        switch ($field['comparsionType']) {
            case  'equal' :


                foreach ($matrix as $key => $matrixItem) {

                    $filter = $item['item'];
                    $filter['value'] = $matrixItem['value'];
                    $filterItem->addArray($filter);

                    $matrix[$key]['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                    $matrix[$key]['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}][]";
                    $matrix[$key]['_filter']['inFilter'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $filter['type'], $field['gpth'], $filter['value']);
                }

                break;


            case  'interval' :

                $minFilter = $item['item'];
                $minFilter['type'] = 'from';
                $minFilter['value'] = $matrix['min'];
                $filterItem->addArray($minFilter);

                $maxFilter = $item['item'];
                $maxFilter['type'] = 'to';
                $maxFilter['value'] = $matrix['max'];
                $filterItem->addArray($maxFilter);

                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                $field['_filter']['filterNameMax'] = "{$filterItem->type}[to][{$maxFilter['property']}]";
                $field['_filter']['filterNameMin'] = "{$filterItem->type}[from][{$minFilter['property']}]";

                $field['_filter']['inFilterMax'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'to', $field['property']);
                $field['_filter']['inFilterMin'] = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, 'from', $field['property']);

                break;


            case 'sort':


                $filter = $item['item'];
                $filter['type'] = $field['comparsionType'];
                $filter['value'] = $field['sort'];
                $filter['override'] = true;


                if ($active = XRegistry::get('catalogFront')->checkInFilter($filterItem->type, $field['comparsionType'], $item['item']['property'])) {


                    if (strstr($active, $field['sort'])) {
                        $field['_filter']['active'] = true;

                    }

                }


                $filterItem->addArray($filter);
                $field['_filter']['link'] = XRegistry::get('catalogFront')->createFilter($filterItem, !$field['useAsDirectLink'], $outerLink);
                $field['_filter']['filterName'] = "{$filterItem->type}[{$filter[type]}][{$filter['property']}]";
                break;


        }

        return $matrix;

    }

}




class stockProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function handleOnBeforeImport($key, $columns, $importContext)
    {

    }



    public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {
        if(!empty($object['params'])){

            $path=$setName.'.'.$property['basic'];
            $stoks=[];
            foreach($object['params'] as $key=>$value )
            {
                if(strstr($key,$path.'__')){
                    $store=explode('__',$key);
                    $stoks[$store[1]]=$value;
                }

            }

            return $stoks;
        }

    }

	    public function handleTypeBack($property, $value = null)
    {


         $ishop = xCore::moduleFactory('ishop.back');

        $stockDataSource=$ishop->_commonObj->getStocksList();
        $catalog = xCore::moduleFactory('catalog.back');
        $node=$catalog->_commonObj->_propertySetsTree->getNodeInfo($property['ancestor']);

        foreach($stockDataSource as $row)
        {
                $stockData[$row['basic']]=array('innerId'=>$row['id'],'stockName'=>$row['params']['Name'],'pName'=>$node['basic'].'.'.$property['basic'],'stockId'=>$row['basic'],'stockAddress'=>$row['params']['storeAddress']);
        }


        $property['defaultValues']=$stockData;


        return $property;
    }

    public function handleTypeOnEdit($property, $value, $object)
    {
        $ishop = xCore::moduleFactory('ishop.back');

        $stockData=$ishop->_commonObj->getStockAgregated($object['id']);

        return $stockData;
    }
}





class tableProperty
    extends catalogProperty
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function handleTypeOnSave($property, $value, $paramSet,$paramPath)
    {
        return  $value;

    }

    public function handleOnBeforeImport($key, $columns, $importContext)
    {

    }



    public function handleTypeFront($value = null, $property = null, $object = null, $setName = null)
    {

        if(is_string($value))
        {
            return json_decode($value,true);
        }else{

            return $value;
        }


    }


  
}

