<?php

trait _LINK
{
    public function onEdit_LINK($params)
    {
        $link = $this->_tree->getNodeInfo($params['id']);
        
        $node = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', $link['params']['connectedPageId']))->run();

        $link['params']['connectedPage'] = $node['paramPathValue'];
        
        $this->result['data'] = $link['params'];
    }

    public function onSaveEdited_LINK($params)
    {


        if ($this->_tree->reInitTreeObj((int)$params['data']['id'], '%SAME%', $params['data'], '_LINK')) {
            $this->pushMessage('link-saved');
        }
    }

    public function onSave_LINK($params)
    {

        if ($this->_tree->initTreeObj((int)$params['data']['ancestorId'], $params['data']['basic'], '_LINK', $params['data'])) {
            $this->pushMessage('link-saved');
        } else {
            return new badResult('link-not-saved');
        }
    }

    public function onCreate_LINK($params)
    {

    }
}

?>