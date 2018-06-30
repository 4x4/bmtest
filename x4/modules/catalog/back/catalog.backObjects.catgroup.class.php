<?php

trait _CATGROUP

{
    public function onEdit_CATGROUP($params)
    {

        
        if ($node = $this->_tree->getNodeInfo($params['id'], true)) {
            foreach ($node['params'] as $key => $param) {
                $nodeParams[$key] = $param;
            }

            $nodeParams['__PropertySetGroup'] = $nodeParams['PropertySetGroup'];
            $nodeParams['PropertySetGroup'] = $this->getPsetsGroupList($nodeParams['PropertySetGroup']);


            $node['params'] = $nodeParams;
            $node = $this->typeHandlerProccess($nodeParams['__PropertySetGroup'], $node);


            $this->result['catGroupData'] = $node;
        }
    }

    public function onSaveEdited_CATGROUP($params)
    {

        if ($basic = $params['catGroupData']['seo']['basic']) {
            unset ($params['catGroupData']['basic']);
        } else {
            $basic = '%SAME%';
        }

        $paramSet = $this->objParamConverter($params['catGroupData']);

        $paramSet = $this->typeHandlerProccessOnSave($paramSet['PropertySetGroup'], $paramSet);


        if ($node = $this->_tree->reInitTreeObj($params['id'], $basic, $paramSet, '_CATGROUP')) {
            $this->pushMessage('catgroup-edited-saved');

            $node = $this->_tree->getNodeStruct($params['id']);

            $this->result['ancestor'] = $node['ancestor'];
        }
    }

    public function onSave_CATGROUP($params)
    {
        
        if ($basic = $params['catGroupData']['seo']['basic']) {
            unset ($params['catGroupData']['seo']['basic']);
        } else {
            $basic = '%SAMEASID%';
        }

        $paramSet = $this->objParamConverter($params['catGroupData']);

        $ancestor = $paramSet['ancestorId'];
        unset ($paramSet['ancestorId']);

        $paramSet = $this->typeHandlerProccessOnSave($paramSet['PropertySetGroup'], $paramSet);

        if ($objId = $this->_tree->initTreeObj($ancestor, $basic, '_CATGROUP', $paramSet)) {
            $this->pushMessage('new-catgroup-saved');
            return new okResult();
        }
    }

    public function onCreate_CATGROUP($params)
    {
        $this->result['PropertySetGroup'] = $this->getPsetsGroupList();
    }

}
