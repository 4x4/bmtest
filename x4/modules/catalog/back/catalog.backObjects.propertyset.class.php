<?php
use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;


trait _PROPERTYSET
{

    public function onEdit_PROPERTYSET($params)
    {
        $this->result['propertySetData'] = $this->_commonObj->_propertySetsTree->getNodeInfo($params['id']);
    }


    public function onSave_PROPERTYSET($params)
    {

        $basic = $params['propertySetData']['basic'];
        unset($params['propertySetData']['basic']);

        $psetId = $this->_commonObj->_propertySetsTree->initTreeObj(1, $basic, '_PROPERTYSET', $params['propertySetData']);

        if ($params['propertySetData']['isSKU']) {
            $this->createRootSkuGroup($basic, array(
                'Name' => $params['propertySetData']['alias']
            ));
        }


        if (!empty($params['properties'])) {
            foreach ($params['properties'] as $property) {
                $id = $this->_commonObj->_propertySetsTree->initTreeObj($psetId, $property['basic'], '_PROPERTY', $property['params']);
                if ($params['propertyVals'][$id]) {
                    $id = $this->_commonObj->_propertySetsTree->delete()->childs($id)->run();
                }
            }
        }

        return new okResult();
    }

    
      public function copyPropertySet($params)
    {
        $params['ancestor'] = 1;
        $this->copyObj($params, $this->_commonObj->_propertySetsTree);
    }


    public function onSaveEdited_PROPERTYSET($params)
    {

         
        $basic = $params['propertySetData']['basic'];
        unset($params['propertySetData']['basic']);
        $this->_commonObj->_propertySetsTree->reInitTreeObj($params['id'], $basic, $params['propertySetData']);
        if (!empty($params['properties'])) {

            $this->_commonObj->_propertySetsTree->delete()->childs($params['id'])->run();

            try {

                if ($params['propertySetData']['isSKU']) {
                    $this->createRootSkuGroup($basic, array(
                        'Name' => $params['propertySetData']['alias']
                    ));
                }

            } catch (Exception $e) {
                if ($e->getMessage() == 'non-uniq-ancestor') {
                    $this->result['exception'] = 'non-uniq-ancestor';
                }
            }


            foreach ($params['properties'] as $property) {


                $id = $this->_commonObj->_propertySetsTree->initTreeObj($params['id'], $property['basic'], '_PROPERTY', $property['params']);

                if ($property['params']['options']) {
                    $this->_commonObj->_propertySetsTree->initTreeObj($id, '%SAMEASID%', '_OPTIONS', $property['params']['options']);
                }
            }
        }

        $this->rebuildPropSetsSerialized($params['id']);
        $this->pushMessage('propertyset-saved');
    }


    public function deletePropertySet($params)
    {
        if ($plist = $this->_commonObj->_propertySetsTreeGroup->selectStruct(array(
            'ancestor',
            'basic'
        ))->where(array(
            '@basic',
            '=',
            $params['id']
        ))->run()
        ) {
            foreach ($plist as $pl) {
                if (false !== ($key = array_search($pl['basic'], $params['id']))) {
                    unset($params['id'][$key]);
                }

                $this->result['nodelete'][] = $pl['id'];
            }
        }


        if (is_array($this->result['nodelete']))
            $this->pushError('some-propertyset-cannot-be-deleted');

        $isSku = $this->_commonObj->_propertySetsTree->selectParams(array(
            'isSKU'
        ))->selectStruct(array(
            'basic'
        ))->where(array(
            "@id",
            '=',
            $params['id']
        ))->format('valval', 'basic', 'id')->run();

        if ($deleted = $this->deleteObj($params, $this->_commonObj->_propertySetsTree)) {
            if (isset($isSku)) {
                if ($isSku = array_intersect($isSku, $deleted)) {

                    $ids = $this->_commonObj->_sku->selectStruct(array(
                        'id'
                    ))->where(array(
                        '@basic',
                        '=',
                        array_keys($isSku)
                    ))->format('valval', 'id', 'id')->run();
                    $this->_commonObj->_sku->delete()->childs(array_keys($ids))->run();
                    $this->_commonObj->_sku->delete()->where(array(
                        '@id',
                        '=',
                        array_keys($ids)
                    ))->run();
                }

            }
        }
    }


    public function propertySetsList($params)
    {

        $this->_commonObj->translateWord('yes');

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_commonObj->_propertySetsTree
        ));
        $opt = array(
            'showNodesWithObjType' => array(
                '_PROPERTYSET'
            ),
            'columns' => array(
                'id' => array(),
                '>alias' => array(),
                'basic' => array(),
                '>isSKU' => array('transformList' => array(1 => $this->_commonObj->translateWord('yes')))
            )
        );
        $source->setOptions($opt);
        $this->result = $source->createView(1);
    }


    public function propertiesList($params)
    {

        if ($params['basic']) {
            $node = $this->_commonObj->_propertySetsTree->selectStruct(array('id'))->where(array('@ancestor', '=', 1), array('@basic', '=', $params['basic']))->singleResult()->run();
            $params['id'] = $node['id'];
        }

        if ($childs = $this->_commonObj->_propertySetsTree->selectStruct(array(
            'id',
            'basic',
            'obj_type',
            'ancestor'
        ))->selectParams('*')->childs($params['id'], 2)->run()
        ) {
            foreach ($childs as $id => $child) {
                if ($child['obj_type'] == '_PROPERTY') {
                    $this->result['property'][$child['id']] = $child;
                } elseif ($child['obj_type'] == '_OPTIONS') {
                    $options[$child['ancestor']] = $child;
                }
            }

            if (isset($options)) {
                foreach ($options as $id => $child) {
                    $typeObject = $this->_commonObj->getPropertyType($this->result['property'][$id]['params']['type']);

                    if (method_exists($typeObject, 'handleOptions')) {
                        $child['params'] = $typeObject->handleOptions($child['params']);
                    }


                    $this->result['property'][$id]['params']['options'] = $child['params'];
                }

            }

        }
    }


}

?>
