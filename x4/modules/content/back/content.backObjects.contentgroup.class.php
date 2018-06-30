<?php

trait _CONTENTGROUP
{
    public function onEdit_CONTENTGROUP($params)
    {
        $node = $this->_tree->getNodeInfo($params['id']);
        $this->result['data'] = $node['params'];
    }

    public function onSave_CONTENTGROUP($params)
    {

        if ($id = $this->_tree->initTreeObj(1, '%SAMEASID%', '_CONTENTGROUP', $params['data'])) {
            return new okResult('saved');
        }
    }


    public function onSaveEdited_CONTENTGROUP($params)
    {

        if ($id = $this->_tree->reinitTreeObj($params['id'], '%SAME%', $params['data'])) {
            return new okResult('saved');
        }
    }


}

?>