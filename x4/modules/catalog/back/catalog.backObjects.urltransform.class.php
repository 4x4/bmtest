<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

trait _URLTRANSFORM
{

    public function urlTransformTable($params)
    {

        $source = Common::classesFactory('TableJsonSource', array());

        $params['onPage'] = 100;

        $opt = array
        (
            'onPage' => $params['onPage'],
            'table' => 'catalog_url_transform',

            'order' => array
            (
                'id',
                'desc'
            ),

            'idAsNumerator' => 'id',

            'columns' => array
            (
                'id' => array(),
                'tree' => array(),
                'comparsion' => array(),
                'field' => array(),
                'transform_url' => array(),
                'priority' => array()
            )
        );

        $source->setOptions($opt);

        if (!$params['page']) $params['page'] = 1;

        $this->result = $source->createView($params['id'], $params['page']);


    }

    public function testTransformGo($params)
    {
        $this->result['transformed'] = $this->_commonObj->buildUrlTransformation($params['data']['inputTransform']);
    }

    public function onSave_URLTRANSFORM($params)
    {

        $params['data']['field'] = $params['data']['propertySet'] . '.' . $params['data']['property'];
        unset($params['data']['propertySet']);
        unset($params['data']['property']);

        $lastInserted = XPDO::insertIN('catalog_url_transform', $params['data']);
        $this->pushMessage('url-transform-saved');
        $this->_commonObj->clearFieldsUrlTransform((int)$lastInserted);
        $this->_commonObj->rebuildUrlTransformMatrix((int)$lastInserted);

        return new okResult();
    }


    public function onSaveEdited_URLTRANSFORM($params)
    {
        $params['data']['field'] = $params['data']['propertySet'] . '.' . $params['data']['property'];
        unset($params['data']['propertySet']);
        unset($params['data']['property']);
        XPDO::updateIN('catalog_url_transform', (int)$params['id'], $params['data']);

        $this->_commonObj->clearFieldsUrlTransform((int)$params['id']);
        $this->_commonObj->rebuildUrlTransformMatrix((int)$params['id']);

        $this->pushMessage('url-transform-saved');

    }

    public function onEdit_URLTRANSFORM($params)
    {

        $data = XPDO::selectIN('*', 'catalog_url_transform', (int)$params['id']);
        $this->result['data'] = $data[0];
        if ($this->result['data']['multi'] == 0) unset($this->result['data']['multi']);
        $explodedField = explode('.', $this->result['data']['field']);
        $this->result['data']['propertySet'] = $explodedField[0];
        $this->result['data']['property'] = $explodedField[1];

        $this->result['data']['propertySet'] = $this->getPsetsList($this->result['data']['propertySet'], true);
        
        $objectsFilters=array_merge($this->_commonObj->nativeSelectObjectsFilters,$this->_commonObj->selectObjectsFiltersAdd);
        
        $this->result['data']['comparsion'] = XHTML::arrayToXoadSelectOptions(array_combine($objectsFilters, $objectsFilters), $this->result['data']['comparsion']);

    }


    public function onCreate_URLTRANSFORM($params)
    {
        $this->getPsetListInitialData();
        
        $objectsFilters=array_merge($this->_commonObj->nativeSelectObjectsFilters,$this->_commonObj->selectObjectsFiltersAdd);      
        $this->result['data']['comparsion'] = XHTML::arrayToXoadSelectOptions(array_combine($objectsFilters, $objectsFilters));

    }


    public function deleteTransform($params)
    {

        if (!empty($params['id'])) {

            foreach ($params['id'] as $id) {
                XPDO::deleteIN('catalog_url_transform', (int)$id);
                $this->_commonObj->clearFieldsUrlTransform($id);
                $this->result['deleted'] = true;
            }

        }
    }


}

?>
