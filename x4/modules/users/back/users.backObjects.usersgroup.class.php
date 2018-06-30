<?php

trait _USERSGROUP

{
    public function onEdit_USERSGROUP($params)
    {

        $data = $this->_tree->getNodeInfo($params['id']);
        $this->result['data'] = $data['params'];
    }

    public function onSaveEdited_USERSGROUP($params)
    {
        if ($id = $this->_tree->reInitTreeObj($params['id'], '%SAME%', $params['data'])) {
            return new okResult('fusersgroup-edited-saved');
        }
    }

    public function onSave_USERSGROUP($params)
    {

        if ($id = $this->_tree->initTreeObj(1, '%SAMEASID%', '_USERSGROUP', $params['data'])) {
            return new okResult('fusersgroup-saved');
        }
    }
}

?>