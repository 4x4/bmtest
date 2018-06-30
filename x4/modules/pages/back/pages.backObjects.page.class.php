<?php

trait _PAGE
{
    public function onSaveEdited_PAGE($params)
    {

        if ($this->_tree->reInitTreeObj($params['id'], $params['data']['basic'], $params['data'], '_PAGE')) {
            $this->initSlotz($params['id'], $params['modules']);
            $this->pushMessage('page-edited-saved');
        }
    }

    public function onSave_PAGE($params)
    {
        if ($id = $this->_tree->initTreeObj($params['ancestor'], $params['data']['basic'], '_PAGE', $params['data'])) {


            $this->initSlotz($id, $params['modules']);
            $this->pushMessage('page-saved');
        }
    }

    public function onCreate_PAGE($params)
    {
        if ($params['id']) {
            $this->result['data']['Template'] = $this->getTemplates($params['id']);
            $this->initSlotz($params['id'], $params['modules']);
        }
    }

    public function onEdit_PAGE($params)
    {
        $page = $this->_tree->getNodeInfo($params['id']);
        $pagePath = $this->_tree->selectStruct(array('id'))->getBasicPath('/')->where(array
        (
            '@id',
            '=',
            $params['id']
        ))->run();

        xCore::callCommonInstance('templates');
        $page['params']['Template'] = $this->getTemplates($page, $page['params']['Template']);
        $this->result['data'] = $page['params'];
        $this->result['data']['basic'] = $page['basic'];
        $this->result['data']['id'] = $page['id'];
        $page['path'][] = $page['id'];
        $this->result['data']['path'] = $page['path'];
        $this->result['data']['pageFullPath'] = $pagePath['basicPathValue'];
    }

}

?>