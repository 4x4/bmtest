<?php

use X4\Classes\XRegistry;



trait _CATOBJ
{

    public function analyseBackConnections($id)
    {


        if ($propList = $this->_commonObj->_propertySetsTree->selectStruct('*')->selectParams('*')->where(array('type', '=', 'connection'))->run()) {


            foreach ($propList as $element) {
                $struct = $this->_commonObj->_propertySetsTree->getNodeStruct($element['ancestor']);
                if ($connected = $this->_tree->selectStruct('*')->selectParams(array('Name'))->where(array($struct['basic'] . '.' . $element['basic'], 'rlike', '"' . $id . '"'))->getParamPath('Name')->run()) {


                    $connectionMatrix[] = array('property' => $struct['basic'] . '.' . $element['basic'],
                        'alias' => $element['params']['alias'],
                        'id' => $element['id'],
                        'connected' => $connected);

                }


            }

            return $connectionMatrix;

        }


    }


    public function onSave_CATOBJ($params)
    {


        if ($basic = $params['catObjData']['seo']['basic']) {
            unset($params['catObjData']['seo']['basic']);
        } else {
            $basic = '%SAMEASID%';
        }


        $paramSet = $this->objParamConverter($params['catObjData']);
        $ancestor = $paramSet['ancestorId'];
        unset($paramSet['ancestorId']);

        $paramSet = $this->typeHandlerProccessOnSave($paramSet['PropertySetGroup'], $paramSet);


        $eventResult = XRegistry::get('EVM')->fire($this->_moduleName . '.back:onSaveCATOBJ', array('paramSet' => $paramSet));

        if (isset($eventResult['paramSet'])) {
            $paramSet = $eventResult['paramSet'];
        }

        if ($objId = $this->_tree->initTreeObj($ancestor, $basic, '_CATOBJ', $paramSet)) {
            if (isset($params['skuData'])) {
                $this->skuInit($params['skuData'], $params['skuLink'], $objId);
            }

            $this->pushMessage('new-catobj-saved');
        } else {
            $this->pushMessage('new-catobj-saved');
        }
    }

    public function onSaveEdited_CATOBJ($params)
    {


        $position = $params['catObjData']['Position'];

        unset($params['catObjData']['Position']);
        $paramSet = $this->objParamConverter($params['catObjData']);


        if ($basic = $params['catObjData']['seo']['basic']) {
            unset($params['catObjData']['seo']['basic']);
        } else {
            $basic = '%SAME%';
        }


        $paramSet = $this->typeHandlerProccessOnSave($paramSet['PropertySetGroup'], $paramSet);

        $eventResult = XRegistry::get('EVM')->fire($this->_moduleName . '.back:onSaveEditedCATOBJ', array('paramSet' => $paramSet));

        if (isset($eventResult['paramSet'])) {
            $paramSet = $eventResult['paramSet'];
        }

        if ($objId = $this->_tree->reInitTreeObj($params['id'], $basic, $paramSet, '_CATOBJ')) {

            $this->_tree->setStructData($params['id'], 'rate', $params['catObjData']['rate']);


            if (isset($params['skuData'])) {
                $this->result['skuTransformedId'] = $this->skuInit($params['skuData'], $params['skuLink'], $params['id']);
            }


            if (!empty($position)) {

                foreach ($position as &$positionElement) {
                    $node = $this->_tree->getNodeInfo((int)$positionElement);

                    if ($node['obj_type'] == '_CATOBJ') {
                        $positionElement = $node['ancestor'];
                    }

                }

                $this->_tree->syncNetIdObjects($params['id'], $position);
            }


            $this->pushMessage('catobj-edited-saved');
            return new okResult();
        }
    }

    public function onCreate_CATOBJ($params)
    {
        $this->result['PropertySetGroup'] = $this->getPsetsGroupList();
    }


    public function onEdit_CATOBJ($params)
    {


        if ($node = $this->_tree->getNodeInfo($params['id'], true)) {
            foreach ($node['params'] as $key => $param) {
                $nodeParams[$key] = $param;
            }


            if ($backConnections = $this->analyseBackConnections($params['id'])) {
                $this->result['backConnections'] = $backConnections;
            }

            $nodeParams['__PropertySetGroup'] = $nodeParams['PropertySetGroup'];
            $nodeParams['PropertySetGroup'] = $this->getPsetsGroupList($nodeParams['PropertySetGroup']);

            $node['params'] = $nodeParams;


            $node = $this->typeHandlerProccess($nodeParams['__PropertySetGroup'], $node);

            $node['params']['seo.basic'] = $node['basic'];

            $netid = $this->_tree->selectStruct('*')->where(array('@netid', '=', $node['id']))->getParamPath('Name')->run();

            if (!empty($netid)) {

                foreach ($netid as $net) {
                    $netStack[$net['id']] = $net['paramPathValue'];
                }


                $node['params']['Position'] = XHTML::arrayToXoadSelectOptions($netStack, $netStack);
            }


            $this->result['catObjData'] = $node;

            $this->result['PSG'] = array(
                'skuLink' => $this->_commonObj->_propertySetsTreeGroup->readNodeParam($nodeParams['__PropertySetGroup'], 'skuLink')
            );


            if ($this->result['PSG']['skuLink']) {
                $skuGroup = $this->_commonObj->_sku->selectStruct(array('basic'))->where(array('@id', '=', $this->result['PSG']['skuLink']))->singleResult()->run();
                $skuPset = $this->_commonObj->findPsetByName($skuGroup['basic']);

                $skuData = $this->getSkuForObject($params['id']);

                $this->result['skuData'] = $skuData;
            }


        }
    }


    public function deleteCatObj($params)
    {

        if ($deleted = $this->deleteObj($params, $this->_tree, true)) {   //удаляем связанные sku        
            $this->_commonObj->_sku->delete()->where(array('@netid', '=', $deleted))->run();
        }

    }


}

