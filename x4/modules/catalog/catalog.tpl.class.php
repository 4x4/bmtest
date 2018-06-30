<?php
use X4\Classes\XRegistry;

class catalogTpl
    extends xTpl
    implements xModuleTpl
{
    //aliasSets

    public function __construct($name)
    {
        parent::__construct($name);
    }


    public function groupPropertiesByTags($params)
    {
        static $psetExtract = array();

        $object = $params['object'];

        if (!($tagsMatrix = $psetExtract[$object['_main']['PropertySetGroup']])) {
            $psetGroup = $this->_commonObj->getPropertyGroupSerialized($object['_main']['PropertySetGroup']);
            $tagsMatrix = array();
            foreach ($psetGroup['sets'] as $psetName => $pset) {
                foreach ($pset as $propertyName => $property) {
                    if (!empty($property['params']['tags'])) {

                        $tags = explode(';', $property['params']['tags']);
                        $tags = array_flip($tags);
                        foreach ($tags as $key => &$tag) {

                            $tag = array();
                            $tag[] = array($psetName, $propertyName, $property['params']);
                        }

                        $tagsMatrix = array_merge_recursive($tagsMatrix, $tags);

                    }

                }
            }

            $psetExtract[$object['_main']['PropertySetGroup']] = $tagsMatrix;
        }

        if (!empty($tagsMatrix)) {
            foreach ($tagsMatrix as $groupName => $element) {

                foreach ($element as $groupedValue) {
                    $key = $groupedValue[0];
                    $value = $groupedValue[1];
                    $groupedMatrix[$groupName][$value] = array('value' => $object[$key][$value], 'property' => $groupedValue[2]);

                }
            }


            return $groupedMatrix;
        } else {
            return array();
        }


    }

    public function getCurrentShowNode($params)
    {

        return $this->currentShowNode;
    }

    public function getRelativeSku($params)
    {
        return $this->getRelativeSkuByProps($params['id'], $params['propsToFilter']);
    }

    public function serializeArray($params)
    {
        return json_encode($params['data']);
    }

    public function skuFilter($params)
    {

        if (isset($params['sku'])) {

            $skuFiltered = array();

            foreach ($params['sku'] as $sku) {

                $filtered = true;

                foreach ($params['filter'] as $filterName => $filterValue) {
                    if (($sku['params'][$filterName] != $filterValue) && $filtered) {
                        $filtered = false;
                    }
                }

                if ($filtered) {
                    $skuFiltered[] = $sku;
                }

            }
            if ($params['uniq']) $skuFiltered = array_unique($skuFiltered);
            return $skuFiltered;

        }

    }

    public function skuUniq($params)
    {

        if (isset($params['sku'])) {
            $setted = array();
            $newSkuSet = array();


            if (is_array($params['param'])) {

                foreach ($params['sku'] as $key => $sku) {

                    $skuStringParam = '';


                    foreach ($params['param'] as $param) {

                        if (isset($sku['params'][$param]) && $sku['params'][$param]) {
                            $skuStringParam .= $sku['params'][$param];
                        }

                    }

                    if ($skuStringParam) {
                        $skuStringParam = md5($skuStringParam);

                        if (!in_array($skuStringParam, $setted)) {
                            $newSkuSet[$key] = $sku;
                            $setted[] = $skuStringParam;
                        }
                    }

                }
            } else {

                foreach ($params['sku'] as $key => $sku) {


                    if (isset($sku['params'][$params['param']]) && is_array($sku['params'][$params['param']])) {
                        $mark = Common::createMark($sku['params'][$params['param']]);
                    } else {


                        if ($sku['params'][$params['param']] == '[]') continue;

                        $mark = isset($sku['params'][$params['param']]) ? $sku['params'][$params['param']] : null;
                    }

                    if (isset($sku['params'][$params['param']]) && $sku['params'][$params['param']] && !in_array($mark, $setted)) {

                        $newSkuSet[$key] = $sku;

                        $setted[] = $mark;
                    }
                }

            }


            if (!empty($newSkuSet)) return $newSkuSet;

            return false;
        }
    }


    public function getFilter($params)
    {

        $filterItem = new filterItem($params['tree']);

        if (isset($params['filter'])) {
            foreach ($params['filter'] as $item) {
                $filterItem->addArray($item);
            }

            return $this->createFilter($filterItem);
        }
    }


    public function getConnectedSKUList($params)
    {

        $id = $params['id'];

        if (is_array($id)) {
            if ($skuObjects = $this->_sku->selectStruct('*')->selectParams('*')->addWhere(array('@id', '=', $id))->run()) {

                $skuObjects = $this->_commonObj->skuHandleFront($skuObjects);

                $objectsIds = array();
                foreach ($skuObjects as $sku) {

                    $objectsIds[] = $sku['netid'];
                }

                if ($objects = $this->_tree->selectStruct('*')->addWhere(array('@id', '=', $objectsIds))->format('valval', 'id', 'id')->run()) {

                    $objects = $this->getConnected(array('id' => $objects, 'linkId' => $params['linkId']));
                    foreach ($objects as $object) {
                        $objectsList[$object['_main']['id']] = $object;
                    }
                }


                foreach ($skuObjects as $key => &$skuObject) {
                    $object = $objectsList[$skuObject['netid']];
                    $object['_currentSKU'] = $skuObject;
                    $result[$skuObject['id']] = $object;
                }

                if ($params['doNotGroupDuplicates']) {
                    foreach ($id as $duplicate) {
                        $extResult[] = $result[$duplicate];
                    }

                } else {

                    $extResult = $result;
                }

                return $extResult;
            }

        }
        return false;

    }

    /**
     * @method: getConnected
     *
     * данная функция позволяет получить значение связанных объектов заданных типом поля Связь
     * @param json "id" - массив id связанных с объектом
     * @param json "linkId" - ид страницы сервера каталога
     * <code>
     *  {%F:#connectedObjects(module.catalog.tpl:getConnected({"id":"{F:obj>somegroup>someConnectedProperty}","linkId":"serverPageId"})%}  //выбираем все товары у которых установлен флаг is_on_main_page
     *   <ul>
     *    {%each({F:connectedObjects},id,obj)%}
     *      <li> id: {%F:id%} Имя: {%obj>someGroup>someprop%} </li>
     *    {%endeach%}
     *   </ul>
     * </code>
     * @return array
     */

    public function getConnected($params)
    {

        if (!$params['id']) return false;
        $filter['filterPack'] = array("f" => array("equal" => array("@id" => $params['id'])));

        if ($params['linkId']) {

            $filter['serverPageDestination'] = $this->createPageDestination($params['linkId']);
            $pages = xCore::loadCommonClass('pages');
            if ($module = $pages->getModuleByAction($params['linkId'], 'showCatalogServer')) {
                $filter['showBasicPointId'] = $module['params']['showBasicPointId'];
            }
        }

        $params['startpage'] = ($params['startpage']) ? $params['startpage'] : 0;
        $params['onpage'] = ($params['onpage']) ? $params['onpage'] : 50;


        if ($catObjects = $this->selectObjects($filter)) {
            if ($catObjects['objects']) {

                return $catObjects['objects'];

            } else {

                return false;
            }

        }

    }

    public function removeFilter($params)
    {
        if ($params['filter']) {
            $currentFilter = $this->filter;


            foreach ($params['filter'] as $filter) {

                if (!empty($filter['value'])) {
                    $index = array_search($filter['value'], $currentFilter[$params['tree']][$filter['type']][$filter['property']]);

                    if ($index !== false) {
                        unset($currentFilter[$params['tree']][$filter['type']][$filter['property']][$index]);
                    }

                } else {

                    if (is_array($currentFilter)) {
                        unset($currentFilter[$params['tree']][$filter['type']][$filter['property']]);
                    }

                }

            }

            $filterItem = new filterItem($params['tree']);

            if (isset($currentFilter['f'])) {
                foreach ($currentFilter['f'] as $k => $item) {

                    foreach ($item as $ikey => $ival) {
                        if (!empty($ival)) {
                            $filterItem->addArray(array('tree' => 'f', 'type' => $k, 'property' => $ikey, 'value' => $ival));
                        }
                    }
                }
            }

            if (isset($currentFilter['s'])) {
                foreach ($currentFilter['s'] as $k => $item) {

                    foreach ($item as $ikey => $ival) {
                        if (!empty($ival)) {
                            $filterItem->addArray(array('tree' => 's', 'type' => $k, 'property' => $ikey, 'value' => $ival));
                        }
                    }
                }
            }


            return $this->createFilter($filterItem, $params['add']);
        }

    }

    public function inFilter($params)
    {

        return $this->checkInFilter($params['tree'], $params['type'], $params['property'], $params['value']);
    }

    /**
     * @method: getMinMax
     * Получить минмум и максимум параметра SKU
     *
     * @param array $params ['skuList'] лист  SKU
     * @param string $params ['param'] параметр по которому необходми найти минимальное и максимальное значение
     * @param string $params ['subParam'] параметр для комплексных типов,например цены
     * @param string $params ['removeZeroValues']  убрать нулевые значение из наценки
     *
     */

    public function getMinMax($params)
    {

        if (isset($params['skuList'])) {


            $minMax = XARRAY::arrToLev($params['skuList'], 'id', 'params', $params['param']);

            if (isset($params['subParam'])) {
                $minMax = XARRAY::asKeyVal($minMax, $params['subParam']);
            }

            $minMax = array_unique($minMax);

            if ($params['removeZeroValues']) {
                $remove = array(0);
                $minMax = array_diff($minMax, $remove);
            }

            $minMax = array_diff($minMax, array(''));

            return array('min' => min($minMax), 'max' => max($minMax));
        }

    }

    public function getAliasedParams($params)
    {

        $object = $params['object'];

        if ($object['obj_type'] != '_SKUOBJ') {
            $psetGroup = $this->_commonObj->getPropertyGroupSerialized($object['_main']['PropertySetGroup']);
            $tagsMatrix = array();
            foreach ($psetGroup['sets'] as $psetName => $pset) {
                foreach ($pset as $propertyName => $property) {
                    $propertyNameAlias = $property['params']['alias'];
                    $groups[$psetName][$propertyName] = array('value' => $object[$psetName][$propertyName], 'alias' => $propertyNameAlias, 'type' => $property['params']['type']);

                }
            }
        } else {

            if ($skuGroup = $this->_sku->getNodeStruct($object['path'][1])) {

                $pset = $this->_commonObj->findPsetByName($skuGroup['basic']);

                foreach ($pset as $propertyName => $property) {

                    if ($params['clearEmpty'] && $object['params'][$propertyName]) {

                        $propertyNameAlias = $property['params']['alias'];
                        $groups[$propertyName] = array('value' => $object['params'][$propertyName], 'alias' => $propertyNameAlias, 'type' => $property['params']['type']);

                    } elseif (!$params['clearEmpty']) {
                        $propertyNameAlias = $property['params']['alias'];
                        $groups[$propertyName] = array('value' => $object['params'][$propertyName], 'alias' => $propertyNameAlias, 'type' => $property['params']['type']);

                    }


                }

            }


            if (is_array($params['exclude'])) {

                $exclude = array_flip($params['exclude']);
                $groups = array_diff_key($groups, $exclude);
            }

            if (is_array($params['include'])) {

                $include = array_flip($params['include']);
                $groups = array_intersect_key($groups, $include);
            }


        }


        return $groups;

    }

    public function getNode($params)
    {

        if ($catNode = $this->_tree->getNodeInfo($params['id'])) {
            $value = $this->_commonObj->convertToPSG($catNode, array
            (
                'serverPageDestination' => $params['destinationLink']
            ));

            return $value;

        }

    }

    public function getAncestor($params)
    {
        if ($params['id']) {

            $struct = $this->_tree->getNodeStruct($params['id']);
            $objectInfo = $this->_tree->getNodeInfo($struct['ancestor'], true);

            $value = $this->_commonObj->convertToPSG($objectInfo, array
            (
                'serverPageDestination' => $params['destinationLink']
            ));

            return $value;

        } else {

            return false;
        }
    }

    public function getMinMaxIshopPrice($params)
    {

        if (isset($params['skuList'])) {

            $minMaxStack = XARRAY::arrToLev($params['skuList'], 'id', 'params', $params['param']);

            $minMax = XARRAY::asKeyVal($minMaxStack, 'value');


            $minMax = array_unique($minMax);

            if ($params['removeZeroValues']) {
                $remove = array(0);
                $minMax = array_diff($minMax, $remove);
            }

            $minMax = array_diff($minMax, array(''));


            if (count($minMax) > 0) {

                $minKey = array_keys($minMax, min($minMax));
                $maxKey = array_keys($minMax, max($minMax));
                return array('min' => isset($minMaxStack[$minKey[0]]) ? $minMaxStack[$minKey[0]] : 0, 'max' => isset($minMaxStack[$maxKey[0]]) ? $minMaxStack[$maxKey[0]] : 0);
            } else {

                return (array('min' => 0, 'max' => 0));
            }


        }

    }

    public function forceSelectorArray($params)
    {

        if (empty($params['value'])) return false;

        if (!isset($params['value'][0])) {

            return array($params['value']);
        } else {

            return $params['value'];
        }

    }

    /**
     * @method: getOnPageList
     *
     * данная функция позволяет получить список возможных
     * значений параметра onpage(количество объектов на странице)  в виде массива
     * элемент массива {количество объектов}=>{ссылка}
     * {ссылка}- не передается если на данный момент в сессии записано именно
     * это количество выводимых объектов
     * <code>
     * {%F:#onpage(catalog:getOnPageList())%}
     * {%each({F:onpage},on_num,on_link)%}
     * <a href="{%F:on_link%}">{%F:on_num%}</a>
     * {%endeach%}
     * </code>
     * @return array
     */

    public function getOnPageList()
    {

        if ($this->onpageMulti) {

            foreach ($this->onpageMulti as $onpage) {

                $onpageList[$onpage] = array('link' => Common::setToUrl(XRegistry::get('TPA')->pageFullLink, array('onpage' => $onpage)),
                    'page' => $onpage
                );

            }
            return $onpageList;
        }
    }

    /**
     * @method: inSelector
     *    позволяет определить  входит ли значение(valueSearchedFor) в селектор
     *
     * @param "needle" - значение, прсиутствие которого нужно определеить в селекторе
     * @param "key" - ключ для значения селектора указывается через >
     * @param "value" - значение селектора
     *
     *   {%F:#isIn(catalog:inSelector({"needle":"valueSearchedFor","key":"selectorSet>selectorKey","value":"{F:selectorValue}"}))%}
     *
     * */
    public function inSelector($params)
    {

        $key = explode('>', $params['key']);

        if ($params['value']) {
            foreach ($params['value'] as $selector) {
                if (is_array($selector[$key[0]])) {
                    $inSelect = $selector[$key[0]][$key[1]];

                } else {
                    $inSelect = $selector[$key[1]];
                }


                if ($inSelect == $params['needle']) {
                    return true;
                }
            }
        }

        return false;


    }
    
    public function getObjectCountByStocks($params)
    {
        $count = 0;

            if(!empty($params['stocks']) && is_array($params['stocks'])) {
                foreach($params['stocks'] as $id => $skl) {
                    if($skl > 0) {
                        $count = $count + (int) $skl;
                    }
                }
            }

        return $count;
    }

    /**
     * @method: get_objects_by_filter
     *
     * данная функция позволяет получить список элементов согласно установленному фильтру, фильтр задается в json формате
     * @param json "filter" - указывается фильтр согласно которому будет произведена выборка
     * @param json "options" - указываются опции "catalogurl":{ссылка на каталог}
     * <code>
     *  {%F:#objects(catalog:get_objects_by_filter(|{"filter":{"equal":{"is_on_main_page":1}}}|))%}  //выбираем все товары у которых установлен флаг is_on_main_page
     *   <ul>
     *    {%each({F:objects},id,obj)%}
     *      <li> id: {%F:id%} Имя: {%obj>params>Name%} </li>
     *    {%endeach%}
     *   </ul>
     * </code>
     * @return array
     */

    public function getObjectsByFilter($params)
    {
        $objects = $this->getObjectsByFilterInner($params['filter'], $params['linkId'], $params['startpage'], $params['onpage']);

        if (!empty($objects)) {
            return $objects;
        }

        return false;

    }

    /**
     * @method: getCompareCount
     *
     * данная функция позволяет получить количество объектов находящихся в сравнении
     * <code>
     *  {%F:@comparse_count(catalog:get_comparse_count())%}
     * </code>
     *
     */

    public function getCompareCount($params)
    {
        if ($_SESSION['catalog']['comparsedata']) {
            return count($_SESSION['catalog']['comparsedata']);
        } else {
            return 0;
        }
    }

    public function inCompare($params)
    {
        if ($_SESSION['catalog']['comparsedata'][$params['id']]) {
            return true;
        }
    }

    public function getUrlFilterTransform($params)
    {
        return $this->_commonObj->buildUrlTransformation($params['url']);
    }


    public function sortUrlTransform($params)
    {
        if ('asc-' . $params['type'] == $params['value']) {
            return $params['asc'];
        }

        if ('desc-' . $params['type'] == $params['value']) {
            return $params['desc'];
        }

        return false;
    }


    public function getLink($params)
    {
        $params['id'] = (int)$params['id'];
        return $this->_commonObj->buildLink($params['id'], $params['destinationPage']);
    }

}


?>
