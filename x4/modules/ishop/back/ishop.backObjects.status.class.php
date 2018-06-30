<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

trait _STATUS

{
    public function statusList($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'showNodesWithObjType' => array(
                '_STATUS'
            ),
            'columns' => array(

                'id' => array(),
                '>Name' => array(),
                'basic' => array()

            )
        );
        $source->setOptions($opt);
        $id = $this->_commonObj->getBranchId('STATUS');
        $this->result = $source->createView($id);
    }


    public function onEdit_STATUS($params)
    {

        $node = $this->_tree->getNodeInfo($params['id']);
        $node['params']['statusId'] = $node['basic'];
        $this->result['data'] = $node['params'];

    }


    public function onSave_STATUS($params)
    {

        $ancestor = $this->_commonObj->createTunesBranch('STATUS');

        $basic = $params['data']['statusId'];
        unset($params['data']['statusId']);

        if ($this->_tree->initTreeObj($ancestor, $basic, '_STATUS', $params['data'])) {
            $this->pushMessage('status-saved');
        }


    }


    public function onSaveEdited_STATUS($params)
    {
        $basic = $params['data']['statusId'];
        unset($params['data']['statusId']);


        if ($this->_tree->reInitTreeObj($params['id'], $basic, $params['data'], '_STATUS')) {
            $this->pushMessage('status-edited-saved');
        }

    }


}

?>