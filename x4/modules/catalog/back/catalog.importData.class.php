<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;


trait importData
{

    public function importData($params)
    {


        $this->import = new X4\Classes\XImport();
        if ($params['filename'] and $params['categoryType'] and $params['objectType']) {
            $this->import->processTableImport(PATH_ . $params['filename']);

            $this->importParams = $params;
            $this->importParams['defaultPropertySetGroupCategories'] = $params['categoryType'];
            $this->importParams['defaultPropertySetGroupObjects'] = $params['objectType'];

            $this->importParams['id'] = (int)$params['ancestorId'];


            $this->prefetchPropertySets($this->importParams['defaultPropertySetGroupCategories']);
            $this->prefetchPropertySets($this->importParams['defaultPropertySetGroupObjects']);
            $this->columns = $this->import->getColumnModifiers();
            $this->beforeImport();
            $this->proccessCategories();
            $this->initObjects();

            $this->initSKUS();

            $object = XRegistry::get('EVM')->fire('module.' . $this->_moduleName . '.back:afterImport', array('params' => $params, 'instance' => $this));

            $this->pushMessage('imported');

            return true;

        } else {

            $this->pushError('error-data-not-provided');
            return false;
        }
    }


    public function beforeImport()
    {

        foreach ($this->columns as $key => $column) {

            if ($propertyObject = $this->_commonObj->getPropertyType($column['type'])) {

                $propertyObject->handleOnBeforeImport($key, $this->columns, $this);
            }

        }

    }

    public function prefetchPropertySets($psetGroup)
    {

        if (!isset($this->prefetchedSets[$psetGroup])) {
            $groupSerialized = $this->_commonObj->getPropertyGroupSerialized($psetGroup);
            if ($skuSet = $groupSerialized['setGroupParams']['skuLink']) {

                $skuLink = $this->_commonObj->_sku->getNodeStruct($groupSerialized['setGroupParams']['skuLink']);
                if ($pset = $this->_commonObj->findPsetByName($skuLink['basic'])) {

                    $item = array_search($skuLink['basic'], $this->_commonObj->psetIdToNameStorage);
                    $this->connectionSku[$psetGroup] = $item;
                    $this->skuLinksToId[$item] = $groupSerialized['setGroupParams']['skuLink'];

                    foreach ($pset as $propertyName => $property) {
                        $this->prefetchedSets[$item][$skuLink['basic'] . '.' . $propertyName] = $property;
                    }
                }

            }

            foreach ($groupSerialized['sets'] as $setName => $set) {
                foreach ($set as $propertyName => $property) {
                    $this->prefetchedSets[$psetGroup][$setName . '.' . $propertyName] = $property;
                }

            }
        }

    }

    public function importCategoryBuild($field)
    {

        if ($field) {

            $query = "select * from importer where `{$field}` LIKE '#%'";
            if ($result = $this->_PDO->query($query)) {
                $prevLevel = 0;
                $levelStack[$prevLevel] = 'root';
                $categories=array();
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $this->categoriesInfo[$row['id']] = $row;

                    $category = $row[$field];

                    $level = substr_count($category, '#');

                    if ($prevLevel > $level) {
                        $prevLevel = $level;
                    }

                    $category = str_replace('#', '', $category);

                    $levelStack[$level] = $category;


                    if ($prevLevel <= $level) {

                        $duplicate = array_search($category, $categories[$levelStack[$level - 1]]);

                        if ($duplicate) {


                            $this->categoryDuplicates[$row['id']] = $duplicate;

                        } else {

                            $categories[$levelStack[$level - 1]][$row['id']] = $category;

                        }

                    } else {
                        $duplicate = array_search($category, $categories[$levelStack[$level - 1]]);

                        if ($duplicate) {
                            $this->categoryDuplicates[$row['id']] = $duplicate;


                        } else {

                            $categories[$levelStack[$prevLevel]][$row['id']] = $category;

                        }

                    }


                    $prevLevel = $level;


                }

            }

            $this->categories = $categories;
        }

    }

    public function recursivePresenceCheck($treesub, $startNode, $openNode = 'root')
    {

        if (!empty($this->categories)) {
            foreach ($this->categories[$openNode] as $key => $node) {
                $finder = false;
                $category = $this->categories[$openNode][$key];


                if (isset($treesub->tree[$startNode])) {
                    foreach ($treesub->tree[$startNode] as $enode) {
                        if ($enode['params']['Name'] == $node) {
                            $this->updateCategories[$key] = $enode['id'];


                            $this->categoriesStack[$openNode][$key] = array(
                                'internalId' => $enode['id'],
                                'Name' => $this->categories[$openNode][$key]
                            );

                            $finder = true;
                            break;
                        }

                    }
                }

                if (isset($this->categories[$node])) {

                    $this->recursivePresenceCheck($treesub, $enode['id'], $category);
                }

                if (!$finder)
                    $this->categoriesStack[$openNode][$key] = array(
                        'Name' => $this->categories[$openNode][$key]
                    );
            }
        }


    }


    public function importFilter($type, $filters, $inputParams)
    {
        foreach ($filters as $filter => $params) {
            $key = key($params);

            $inputParams['value'] = XNameSpaceHolder::call('module.' . $this->_moduleName . '.back', $type . $key, $inputParams, $params[$key]);
        }

        return $inputParams['value'];

    }


    public function recordProcessing($row, $isSku = false)
    {
        $this->columns = $this->import->getColumnModifiers();

        if (!$isSku) {
            $psets = $this->prefetchedSets[$row['PropertySetGroup']];
            $extRow = array('PropertySetGroup' => $row['PropertySetGroup']);
            $parent = null;

        } else {

            $psets = $this->prefetchedSets[$row['PropertySetGroup']];
            $parent = $row['Parent'];
            unset($row['PropertySetGroup'], $row['Parent']);
        }


        foreach ($row as $rowName => $rowItem) {
            $schemeRowName = $rowName;
            $rowName = $this->columns[$rowName]['realName'];

            if ($rowName == 'Name') {
                if ($isSku) {
                    $extRow['Name'] = substr($row[$schemeRowName], 1);

                } else {
                    $extRow['Name'] = $row[$schemeRowName];

                }

                continue;
            }


            if (!isset($psets[$rowName])) {
                continue;
            }

            $propertyObject = $this->_commonObj->getPropertyType($psets[$rowName]['params']['type']);


            $arrayParams = array('value' => $row[$schemeRowName], 'parent' => $parent, 'row' => $extRow, 'rowName' => $rowName, 'schemeRowName' => $schemeRowName, 'columns' => $this->columns, 'psetData' => $psets[$rowName], 'oldRow' => $row, 'context' => $this);

            if (isset($this->columns[$schemeRowName]['filterBefore'])) {
                $extRow[$rowName] = $this->importFilter('_importFilterBefore', $this->columns[$schemeRowName]['filterBefore'], $arrayParams);
            }


            $arrayParams['extRow'] = $extRow = $propertyObject->handleOnImport($row[$schemeRowName], $extRow, $rowName, $schemeRowName, $this->columns, $psets[$rowName], $row);


            if (isset($this->columns[$schemeRowName]['filterAfter'])) {
                $extRow[$rowName] = $this->importFilter('_importFilterAfter', $this->columns[$schemeRowName]['filterAfter'], $arrayParams);

            }


        }

        if ($isSku) {
            reset($extRow);
            while (list($key, $value) = each($extRow)) {
                if ($newKey = strchr($key, '.')) {
                    $newKey = substr($newKey, 1);
                    $extRow[$newKey] = $extRow[$key];
                    unset($extRow[$key]);
                }

            }


        }

        return $extRow;
    }


    public function reInitCategories()
    {

        if (isset($this->updateCategories)) {
            foreach ($this->updateCategories as $key => $category) {


                $paramSet = $this->categoriesInfo[$key];


                $paramSet['PropertySetGroup'] = $this->importParams['defaultPropertySetGroupCategories'];

                unset($paramSet['id'], $paramSet['internalId'], $paramSet['status']);

                $paramSet['Name'] = str_replace('#', '', $paramSet['Name']);


                if (isset($paramSet['basic'])) {
                    if (!$basic = $this->handleBasicImport($paramSet['basic'], $paramSet)) {
                        $basic = '%SAME%';
                    }


                } else {

                    $basic = '%SAME%';
                }


                $paramSet = $this->recordProcessing($paramSet);


                $this->_tree->reInitTreeObj($category, $basic, $paramSet, '_CATGROUP');


            }
        }

    }

    public function initCategories($startNode = 'root', $ancestor)
    {

        if (isset($this->categoriesStack[$startNode])) {

            foreach ($this->categoriesStack[$startNode] as $id => &$category) {

                if (!isset($category['internalId'])) {
                    $paramSet = $this->categoriesInfo[$id];


                    $paramSet['PropertySetGroup'] = $this->importParams['defaultPropertySetGroupCategories'];

                    unset($paramSet['id'], $paramSet['internalId'], $paramSet['status']);

                    $paramSet['Name'] = $category['Name'];

                    if (isset($paramSet['basic'])) {
                        $basic = $this->handleBasicImport($paramSet['basic'], $paramSet);

                    } else {

                        $basic = '%SAMEASID%';
                    }


                    $paramSet = $this->recordProcessing($paramSet);

                    $paramSet['Name'] = $category['Name'];


                    if ($objId = $this->_tree->initTreeObj($ancestor, $basic, '_CATGROUP', $paramSet)) {
                        $category['internalId'] = $objId;
                    }

                } else {

                    $objId = $category['internalId'];
                }

                if (isset($this->categoriesStack[$category['Name']])) {
                    $this->initCategories($category['Name'], $objId);
                }
            }
        }

    }


    public function mapCategories()
    {
        if (isset($this->categoriesStack) && (count($this->categoriesStack) > 0))
            foreach ($this->categoriesStack as $key => $category) {

                foreach ($category as $syncKey => $innerCat) {
                    $this->categoriesInfo[$syncKey]['internalId'] = $innerCat['internalId'];


                    $filteredDuplicates = array_filter($this->categoryDuplicates, function ($element) use ($syncKey) {
                        return isset($element) && $element == $syncKey;
                    });

                    if (count($filteredDuplicates) > 0) {

                        foreach ($filteredDuplicates as $sync => $duplicate) {
                            $this->categoriesInfo[$sync]['internalId'] = $innerCat['internalId'];
                        }
                    }


                }

            }

        if (!empty($this->categoriesInfo)) {
            $this->import->setInternalIds($this->categoriesInfo);
        }

    }


    public function initSKUS()
    {

        if ($this->skuData) {
            if ($skuIds = array_keys($this->skuData)) {
                while (list($k, $v) = each($skuIds)) {
                    if (trim($v) == '') unset($skuIds[$k]);
                }
            }

            if ($this->importParams['uniq'] == 'sku') {
                $inKey = $param = str_replace('sku.', '', $this->PK);

            } else {
                $param = '@netid';
                $inKey = $this->PK;
            }


            if ($oldNodes = $this->_commonObj->_sku->selectStruct('*')->selectParams('*')->where(array($param, '=', $skuIds))->run()) {
                foreach ($oldNodes as $key => $oldNode) {
                    $nodesMap[$oldNode['netid']][$oldNode['params'][$inKey]] = $oldNode['id'];
                    $nodesMapId[$oldNode['params'][$inKey]] = $key;
                }


            }


            foreach ($this->skuData as $key => $skuItems) {
                foreach ($skuItems as $item) {

                    if (!$item['PropertySetGroup']) {

                        $skuLink = $oldNodes[$nodesMapId[$item[$this->PK]]]['path'][1];
                        $item['PropertySetGroup'] = array_search($skuLink, $this->skuLinksToId);
                        $key = $oldNodes[$nodesMapId[$item[$this->PK]]]['netid'];
                    }

                    $skuLink = $this->skuLinksToId[$item['PropertySetGroup']];

                    $item['Parent'] = $key;


                    if ($item = $this->recordProcessing($item, true)) {

                        if ((isset($nodesMap[$key]) && (isset($nodesMap[$key][$item[$inKey]])))) {
                            $this->_commonObj->_sku->reInitTreeObj($nodesMap[$key][$item[$inKey]], '%SAME%', $item, '_SKUOBJ', $key);

                        } elseif ($this->importParams['uniq'] != 'sku') {

                            $this->_commonObj->_sku->initTreeObj($skuLink, '%SAMEASID%', '_SKUOBJ', $item, $key);

                        }
                    }
                }

            }

        }


    }


    public function childsPick($currentAncestor)
    {
        $selectParams = array($this->PK);

        if ($childs = $this->_tree->selectStruct('*')->selectParams($selectParams)->childs($currentAncestor, 1)->format('valparams', 'id', $this->PK)->run()) {
            $childs = array_flip($childs);
            return $childs;
        }

    }

    public function handleBasicImport($basic, $rowCopy)
    {

        if ($options = $this->columns['basic']) {
            if (isset($options['Template'])) {
                foreach (array_keys($rowCopy) as $key) {
                    $keys[] = '{' . $key . '}';
                }

                $basic = str_replace($keys, $rowCopy, $options['Template']);

            }


            if (isset($options['search']) && isset($options['replace'])) {
                $basic = str_replace($options['search'], $options['replace'], $basic);

            } else {

                $basic = str_replace(array(' ', ',', '/', '.', ')', '('), array('-', '', '-', '-', '', ''), $basic);
            }

        }

        $basic = strtolower(XCODE::translit($basic));

        if ($tempBasic = XRegistry::get('EVM')->fire('module.' . $this->_moduleName . '.back:onBasicImport', array('params' => array('basic' => $basic)))) {
            $basic = $tempBasic['basic'];
        }

        return $basic;


    }

    public function initObjects()
    {

        $i = 0;

        $query = "select * from importer";

        if ($result = $this->_PDO->query($query)) {
            $currentAncestor = $this->importParams['id'];

            $childs = $this->childsPick($currentAncestor);

            $this->importParams['uniq'] = $this->columns[$this->PK]['uniq'];

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $i++;
                if ($row['internalId']) {
                    $currentAncestor = $row['internalId'];
                    $childs = $this->childsPick($currentAncestor);

                    continue;
                }

                if (($this->importParams['uniq'] == 'sku') or (strpos($row[$this->PK], '*') === 0)) {

                    if ($this->importParams['uniq'] == 'sku') $lastObjectId = $row[$this->PK];

                    //фейковая передача сета; только для сохранения связи 
                    $row['PropertySetGroup'] = $this->connectionSku[$paramSet['PropertySetGroup']];

                    if (!empty($lastObjectId)) {
                        $this->skuData[$lastObjectId][] = $row;
                    }

                    continue;
                }


                $rowCopy = $row;
                unset($rowCopy['id'], $rowCopy['internalId'], $rowCopy['status']);
                $paramSet = $rowCopy;
                $paramSet['PropertySetGroup'] = $this->importParams['defaultPropertySetGroupObjects'];


                if (isset($rowCopy['basic'])) {
                    $basic = $this->handleBasicImport($rowCopy['basic'], $rowCopy);

                } else {

                    $basic = '%SAMEASID%';
                }

                $paramSet = $this->recordProcessing($paramSet);


                if ($reseted = XRegistry::get('EVM')->fire('module.' . $this->_moduleName . '.back:beforeImportObject', array('paramSet' => $paramSet))) {
                    $paramSet = $reseted;
                }


                if (isset($childs[$paramSet[$this->PK]])) {
                    if ($basic == '%SAMEASID%') $basic = '%SAME%';


                    $this->_tree->reInitTreeObj($childs[$paramSet[$this->PK]], $basic, $paramSet, '_CATOBJ');

                    $objId = $childs[$paramSet[$this->PK]];

                } else {

                    $objId = $this->_tree->initTreeObj($currentAncestor, $basic, '_CATOBJ', $paramSet);

                }

                $lastObjectId = $objId;
                $this->rowToIdConvertion[$row['id']] = $objId;
                $this->importedObjects[$objId] = array('row' => $paramSet, 'oldRow' => $row);

            }
        }

    }


    public function proccessCategories()
    {


        if ($primaryKeys = $this->import->getColumnModifiersByType('PK')) {
            $field = key($primaryKeys);
            $this->PK = $field;
            $this->importCategoryBuild($field);


            $treeSub = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
                '@obj_type',
                '=',
                '_CATGROUP'
            ))->childs($this->importParams['id'])->asTree()->run();

            $this->recursivePresenceCheck($treeSub, $this->importParams['id']);
            $this->initCategories('root', $this->importParams['id']);
            $this->reInitCategories();
            $this->mapCategories();

        }
    }
}

?>
