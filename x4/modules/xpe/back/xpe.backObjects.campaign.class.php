<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

trait _CAMPAIGN
{


    public function onSave_CAMPAIGN($params)
    {
        $id = $this->_commonObj->_xpeRoles->initTreeObj(1, '%SAMEASID%', '_CAMPAIGN', $params['data']);
        $this->pushMessage('items-saved');
        return new okResult();
    }


    public function onCreate_CAMPAIGN($params)
    {


    }

    public function onEdit_CAMPAIGN($params)
    {
        $data = $this->_commonObj->_xpeRoles->getNodeInfo($params['id']);
        $this->result['data'] = $data['params'];
    }


    public function onSaveEdited_CAMPAIGN($params)
    {

        $this->_commonObj->_xpeRoles->reInitTreeObj($params['id'], '%SAME%', $params['data']);

        $this->pushMessage('items-saved');
        return new okResult();

    }
}

?>