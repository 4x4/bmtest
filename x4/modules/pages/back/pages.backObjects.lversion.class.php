<?php

trait _LVERSION
{
    public function onEdit_LVERSION($params)
    {
        $lversion = $this->_tree->getNodeInfo($params['id']);
        $this->result['data']['basic'] = $lversion['basic'];
        $this->result['data']['Name'] = $lversion['params']['Name'];
        $this->result['data']['StartPage'] = $this->getStartPages($params['id'], $lversion['params']['StartPage']);
        $this->result['data']['default404Page'] = $lversion['params']['default404Page'];
    }

    public function onSaveEdited_LVERSION($params)
    {

        if ($this->_tree->reInitTreeObj($params['data']['id'], $params['data']['basic'], $params['data'], '_LVERSION')) {
            $this->initSlotz($params['data']['id'], $params['modules']);
            $this->pushMessage('lversion-saved');
        }
    }

    public function onSave_LVERSION($params)
    {
        if ($this->_tree->initTreeObj($params['data']['ancestor'], $params['data']['basic'], '_LVERSION',
            $params['data'])
        ) {
            $this->pushMessage('lversion-saved');
        } else {
            return new badResult('lversion-not-saved');
        }
    }

    public function onCreate_LVERSION($params)
    {
        $this->result['data']['ancestor'] = $this->getStartPages(1);
    }
}

?>