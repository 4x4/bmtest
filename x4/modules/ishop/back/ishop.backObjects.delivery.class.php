<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

trait _DELIVERY
{

    public function deleteDelivery($params)
    {
        $this->deleteObj($params, $this->_tree);
    }


    public function deliveryList($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'showNodesWithObjType' => array(
                '_DELIVERY'
            ),
            'columns' => array(

                'id' => array(),
                '>Name' => array(),
                'basic' => array(),
                '>description' => array()
            )
        );
        $source->setOptions($opt);
        $id = $this->_commonObj->getBranchId('DELIVERY');
        $this->result = $source->createView($id);
    }


    public function onSave_DELIVERY($params)
    {

        $ancestor = $this->_commonObj->createTunesBranch('DELIVERY');

        $basic = $params['data']['deliveryId'];
        unset($params['data']['deliveryId']);

        if ($this->_tree->initTreeObj($ancestor, $basic, '_DELIVERY', $params['data'])) {
            $this->pushMessage('delivery-saved');
        }

    }

    public function onSaveEdited_DELIVERY($params)
    {
        $basic = $params['data']['deliveryId'];
        unset($params['data']['deliveryId']);


        if ($this->_tree->reInitTreeObj($params['id'], $basic, $params['data'], '_DELIVERY')) {
            $this->pushMessage('delivery-edited-saved');
        }

    }

    public function onEdit_DELIVERY($params)
    {

        $node = $this->_tree->getNodeInfo($params['id']);
        $node['params']['deliveryId'] = $node['basic'];
        $this->result['data'] = $node['params'];

    }


}


?>