<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;


trait _TREAD
{

    public function onEdit_TREAD($params)
    {
        if ($node = $this->_tree->getNodeInfo($params['id'])) {


            $node['params']['basic'] = $node['basic'];
            $this->result['treadData'] = $node['params'];
        }
    }

    public function onSaveEdited_TREAD($params)
    {

        if ($node = $this->_tree->reInitTreeObj($params['id'], '%SAME%', $params['data'], '_TREAD')) {
            $this->pushMessage('tread-edited-saved');

        }
    }

    public function onSave_TREAD($params)
    {
        $basic = $params['data']['basic'];

        if ($objId = $this->_tree->initTreeObj(1, $basic, '_TREAD', $params['data'])) {
            $this->pushMessage('new-tread-saved');
            return new okResult();
        }
    }

    public function onCreate_TREAD($params)
    {
    }


    public function deleteTread($data)
    {

        foreach ($data['id'] as $id) {

            if ($result = $this->_tree->selectStruct(array('id'))->childs($data['id'], 1)->format('keyval', 'id')->run()) {
                $this->deleteComments(array_keys($result));
                $this->_tree->delete()->childs($data['id'])->run();
            }

            $this->_tree->delete()->where(array('@id', '=', $id))->run();
            $this->result['deletedList'][] = $id;
        }


    }


}

?>