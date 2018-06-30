<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

trait _STORE
{

    public function deleteStore($params)
    {
        $this->deleteObj($params, $this->_tree);
    }


    public function storeList($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'showNodesWithObjType' => array(
                '_STORE'
            ),
            'columns' => array(

                'id' => array(),
                '>Name' => array(),
                'basic' => array(),
                '>storeAddress' => array()
            )
        );
        $source->setOptions($opt);
        $id = $this->_commonObj->getBranchId('STORE');
        $this->result = $source->createView($id);
    }


    public function onSave_STORE($params)
    {

        $ancestor = $this->_commonObj->createTunesBranch('STORE');

        $basic = $params['data']['storeId'];
        unset($params['data']['storeId']);

        if ($this->_tree->initTreeObj($ancestor, $basic, '_STORE', $params['data'])) {
            $this->pushMessage('store-saved');
        }

    }

    public function onSaveEdited_STORE($params)
    {
        $basic = $params['data']['storeId'];
        unset($params['data']['storeId']);


        if ($this->_tree->reInitTreeObj($params['id'], $basic, $params['data'], '_STORE')) {
            $this->pushMessage('store-edited-saved');
        }

    }

    public function onEdit_STORE($params)
    {

        $node = $this->_tree->getNodeInfo($params['id']);
        $node['params']['storeId'] = $node['basic'];
        $this->result['data'] = $node['params'];

    }


}


?>