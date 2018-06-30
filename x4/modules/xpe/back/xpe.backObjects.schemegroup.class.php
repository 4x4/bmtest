<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

trait _SCHEMEGROUP
{
    public function onCreate_SCHEMEGROUP($params)
    {


    }

    public function onSave_SCHEMEGROUP($params)
    {

        $id = $this->_tree->initTreeObj(1, $params['data']['Name'], '_SCHEMEGROUP', $params['data']);

        if (!empty($params['schemeItems'])) {

            foreach ($params['schemeItems'] as $item) {
                if (!empty($item)) {
                    $this->_tree->initTreeObj($id, $item['params']['Name'], '_SCHEMEITEM', $item['params']);
                }

            }
        }


        $this->pushMessage('items-saved');
        return new okResult();
    }

    public function onEdit_SCHEMEGROUP($params)
    {

        $data = $this->_tree->getNodeInfo($params['id']);
        $this->result['data'] = $data['params'];
        $this->result['data']['Name'] = $data['basic'];

        $childs = $this->_tree->selectStruct('*')->selectParams('*')->format('keyval', 'id')->childs($params['id'], 1)->run();

        if (!empty($childs)) {
            foreach ($childs as $child) {
                $one = array();
                $one['params'] = $child['params'];
                $one['params']['Name'] = $child['basic'];
                $this->result['schemesItems'][] = $one;
            }
        }


    }

    public function onSaveEdited_SCHEMEGROUP($params)
    {
        $this->_tree->reInitTreeObj($params['id'], $params['data']['Name'], $params['data']);
        if (!empty($params['schemeItems'])) {

            $this->_tree->delete()->childs($params['id'])->run();

            foreach ($params['schemeItems'] as $item) {
                if (!empty($item)) {
                    $this->_tree->initTreeObj($params['id'], $item['params']['Name'], '_SCHEMEITEM', $item['params']);
                }

            }
        }

        $this->pushMessage('items-saved');
        return new okResult();

    }

}

?>