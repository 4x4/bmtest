<?php

trait _FUSERSGROUP

{
    public function onEdit_FUSERSGROUP($params)
    {
        $data = $this->_tree->getNodeInfo($params['id']);
        $this->result['data'] = $data['params'];
    }

    public function onSaveEdited_FUSERSGROUP($params)
    {
        if ($id = $this->_tree->reInitTreeObj($params['id'], '%SAME%', $params['data'])) {
            return new okResult('fusersgroup-edited-saved');
        }
    }

    public function onSave_FUSERSGROUP($params)
    {
        if ($id = $this->_tree->initTreeObj(1, '%SAMEASID%', '_FUSERSGROUP', $params['data'])) {
            return new okResult('fusersgroup-saved');
        }
    }
}

?>