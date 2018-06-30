<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;

trait _PROPERTYSETGROUP
{
    public function onCreate_PROPERTYSETGROUP($params)
    {
        $this->result['skuLink'] = $this->getSkuLinkList();
    }

    public function onEdit_PROPERTYSETGROUP($params)
    {

        $this->result['propertySetGroupData'] = $this->_commonObj->_propertySetsTreeGroup->getNodeInfo($params['id']);

        $this->result['propertySetGroupData']['skuLink'] = $this->getSkuLinkList($this->result['propertySetGroupData']['params']['skuLink']);
    }

    public function onSaveEdited_PROPERTYSETGROUP($params)
    {
        $basic = $params['propertySetGroupData']['basic'];
        unset($params['propertySetGroupData']['basic']);

        $this->_commonObj->_propertySetsTreeGroup->reInitTreeObj($params['id'], $basic, $params['propertySetGroupData']);
        $this->_commonObj->_propertySetsTreeGroup->delete()->childs($params['id'])->run();

        if (!empty($params['ids'])) {
            $ids = array_keys($params['ids']);

            foreach ($ids as $externalId) {

                $this->_commonObj->_propertySetsTreeGroup->initTreeObj($params['id'], $externalId, '_PROPERTYSETLINK');

            }
        }

        //serialize propertygroup data
        $this->_commonObj->createPropertyGroupSerialized($params['id']);

        $this->pushMessage('propertysetgroup-saved');

    }

    public function onSave_PROPERTYSETGROUP($params)
    {

        $basic = $params['propertySetGroupData']['basic'];
        unset($params['propertySetGroupData']['basic']);

        if ($id = $this->_commonObj->_propertySetsTreeGroup->initTreeObj(1, $basic, '_PROPERTYSETGROUP', $params['propertySetGroupData'])) {

            if (!empty($params['ids'])) {
                $ids = array_keys($params['ids']);

                foreach ($ids as $externalId) {
                    $this->_commonObj->_propertySetsTreeGroup->initTreeObj($id, $externalId, '_PROPERTYSETLINK');
                }
            }

            //serialize propertygroup data
            $this->_commonObj->createPropertyGroupSerialized($id);
            $this->pushMessage('propertysetgroup-saved');
            return new okResult();
        }
    }


    public function deletePropertySetGroup($params)
    {
        $this->deleteObj($params, $this->_commonObj->_propertySetsTreeGroup);
    }


    public function propertyGroupsList($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_commonObj->_propertySetsTreeGroup
        ));
        $opt = array(
            'showNodesWithObjType' => array(
                '_PROPERTYSETGROUP'
            ),
            'columns' => array(
                'id' => array(),
                '>alias' => array(),
                'basic' => array()
            )
        );
        $source->setOptions($opt);

        $this->result = $source->createView(1);
    }


}
