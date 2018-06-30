<?php
use X4\Classes\XTreeEngine;
use X4\Classes\XRegistry;

class catalogComparsion
{
    public $tpl;
    public $comparsionName;

    public function __construct($class)
    {
        $this->_TMS = XRegistry::get('TMS');
        $this->_PDO = XRegistry::get('PDO');
        $this->_EVM = XRegistry::get('EVM');
        $this->comparsionName = $class;
    }


    public function handleComparsionBack($property, $value = null)
    {
        return $property;
    }

    public function handleProcessing(&$field, $objects, $gpth = false, $params)
    {
        return $objects;
    }


}

class equalComparsion
    extends catalogComparsion
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleProcessing(&$field, $objects, $gpth = false, $params)
    {


        $catalog = XRegistry::get('catalogFront');

        $property = $catalog->_commonObj->getPropertyType($field['propertyData']['params']['type']);

        if (!is_object($property)) return;


        $field['gpth'] = $gpth;

        $selectorParam = 'id';

        if ($field['propertyData']['isSKU']) $selectorParam = 'netid';

        $bigHashVal = array();
        $bigHashValActive = array();

        reset($objects);

        while (list(, $object) = each($objects)) {

            $value = $property->handleSearchFilterValue($object['params'][$field['gpth']], $object['params'], $field);
            if (!$value) continue;

            if (!is_array($value)) {
                $hashValSet = md5($value);

            } else {

                $hashValSet = md5(serialize($value));
                $bigHashVal = array_merge($bigHashVal, $value);

            }

            if (isset($value) && $value && !$filterMatrix[$hashValSet]['value']) {
                $filterMatrix[$hashValSet]['_filter']['counter']++;
                $filterMatrix[$hashValSet]['object'] = $object;
                $filterMatrix[$hashValSet]['value'] = $value;

            } elseif ($filterMatrix[$hashValSet]['value']) {
                if (!is_array($value)) {
                    $filterMatrix[$hashValSet]['_filter']['counter']++;
                }
            }


            if (isset($catalog->fullNodeIntersection) && in_array($object[$selectorParam], $catalog->fullNodeIntersection) or ($field['gpth'] == $params['lastChanged'])) {

                $filterMatrix[$hashValSet]['_filter']['active'] = 1;
                $filterMatrix[$hashValSet]['_filter']['counterActive']++;

                $bigHashValActive = array_merge($bigHashValActive, $value);


            } elseif (empty($catalog->fullNodeIntersection)) {
                $filterMatrix[$hashValSet]['_filter']['active'] = 1;
                $filterMatrix[$hashValSet]['_filter']['counterActive']++;

                if (!empty($value)&&is_array($value)&&is_array($bigHashValActive)) {
                    $bigHashValActive = array_merge($bigHashValActive, $value);
                }


            } elseif (!$filterMatrix[$hashValSet]['_filter']['counterActive']) {
                $filterMatrix[$hashValSet]['_filter']['counterActive'] = 0;
            }


        }

        if (!empty($bigHashVal)) {
            $field['_filter']['counter'] = array_count_values($bigHashVal);
        }

        if (!empty($bigHashValActive)) {
            $field['_filter']['counterActive'] = array_count_values($bigHashValActive);
        }


        if ($field['gpth'] == $params['lastChanged'] && isset($_SESSION['filterSets'][$gpth . $catalog->currentShowNode['id']])) {


            $filterMatrix = $_SESSION['filterSets'][$gpth . $catalog->currentShowNode['id']];
        }


        $_SESSION['filterSets'][$gpth . $catalog->currentShowNode['id']] = $filterMatrix;


        $filterMatrix = $property->handleSearchFilterSet($filterMatrix, $field, null);

        $filterMatrix = $property->handleSearchFilterGetFilterInfo($filterMatrix, $field, $params['outerLink']);

        $filterMatrix = $property->handleSearchFilterSorting($filterMatrix, $field);


        $matrixResort = array();


        foreach ($filterMatrix as $matrixElement) {
            if (!empty($matrixElement['_filter']['active'])) {
                array_unshift($matrixResort, $matrixElement);
            } else {
                $matrixResort[] = $matrixElement;
            }
        }

        return $matrixResort;

    }

}


class intervalComparsion
    extends catalogComparsion
{


    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleProcessing(&$field, $objects, $gpth = false, $params)
    {


        $catalog = XRegistry::get('catalogFront');

        $property = $catalog->_commonObj->getPropertyType($field['propertyData']['params']['type']);


        if (isset($field['propertyData']['isSKU']) && $field['propertyData']['isSKU']) {
            $ogpth = 'netid';

        } else {

            $ogpth = 'id';

        }

        if (empty($property)) return;

        while (list(, $object) = each($objects)) {
            if ($value = $property->handleSearchFilterValue($object['params'][$gpth], $object['params'], $field)) {

                $value = str_replace(",", ".", $value);
                $value = floatval($value);

                if (!isset($min)) {
                    $min = $value;
                    $minObj = $object['params'][$gpth];
                }

                if (isset($catalog->fullNodeIntersection) && in_array($object[$ogpth], $catalog->fullNodeIntersection)) {

                    $minActive = $value;
                }


                if (!isset($max)) {
                    $max = 0;
                    $maxActive = 0;
                }


                if (isset($catalog->fullNodeIntersection) && in_array($object[$ogpth], $catalog->fullNodeIntersection)) {
                    if ($minActive > $value) {
                        $minActive = $value;
                    }
                    if ($maxActive < $value) {
                        $maxActive = $value;
                    }

                }

                if ($min > $value) {
                    $min = $value;

                    if (is_array($object['params'][$gpth])) {
                        $minObj = $object['params'][$gpth];
                    } else {
                        $minObj = $value;
                    }


                }


                if ($max < $value) {
                    $max = $value;

                    if (is_array($object['params'][$gpth])) {
                        $maxObj = $object['params'][$gpth];
                    } else {
                        $maxObj = $value;
                    }

                }

            }


        }

        if ($min == $max) return;

        if (!is_array($minObj)) $minObj = floatval($minObj);
        $maxObj['active'] = $maxActive;
        $minObj['active'] = $minActive;
        $filterMatrix['min'] = $minObj;
        $filterMatrix['max'] = $maxObj;
        $filterMatrix = $property->handleSearchFilterGetFilterInfo($filterMatrix, $field, $params['outerLink']);

        return $filterMatrix;

    }


}


class sortComparsion
    extends catalogComparsion
{


    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function handleProcessing(&$field, $objects, $gpth = false, $params)
    {

        $catalog = XRegistry::get('catalogFront');
        $property = $catalog->_commonObj->getPropertyType($field['propertyData']['params']['type']);

        return $filterMatrix = $property->handleSearchFilterGetFilterInfo(true, $field, $params['outerLink']);
    }
}

?>