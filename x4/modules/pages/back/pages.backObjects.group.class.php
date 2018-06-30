<?php

trait _GROUP
{
    public function onEdit_GROUP($params)
    {
        $group = $this->_tree->getNodeInfo($params['id']);
        $this->result['data'] = $group['params'];
        $this->result['data']['path'] = $group['path'];
        $this->result['data']['path'][] = $params['id'];
        $this->result['data']['StartPage'] = $this->getStartPages($params['id'], $group['params']['StartPage']);
        $this->result['data']['basic'] = $group['basic'];
    }

    public function onCreate_GROUP($params)
    {
        if ($params['id']) {
            $this->initSlotz($params['id'], $params['modules']);
        }
    }

    public function onSave_GROUP($params)
    {
        if ($id = $this->_tree->initTreeObj($params['ancestor'], $params['data']['basic'], '_GROUP', $params['data'])) {
            $this->initSlotz($id, $params['modules']);
            $this->pushMessage('group-saved');
        }
    }

    public function onSaveEdited_GROUP($params)
    {
        if ($this->_tree->reInitTreeObj($params['id'], $params['data']['basic'], $params['data'], '_GROUP')) {
            $this->initSlotz($params['id'], $params['modules']);
            $this->pushMessage('group-saved');
        } else {
            return new badResult('group-saving-error');
        }
    }
}

?>