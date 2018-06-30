<?php

trait _DOMAIN
{

    public function onEdit_DOMAIN($params)
    {
        $domain = $this->_tree->getNodeInfo($params['id']);
        $this->result['data'] = $domain['params'];
        $this->result['data']['TemplateFolder'] = $this->getCommonTemplates($domain['params']['TemplateFolder']);
        $this->result['data']['basic'] = $domain['basic'];
        $this->result['data']['id'] = $domain['id'];
        $this->result['data']['StartPage'] = $this->getStartPages($params['id'], $domain['params']['StartPage']);
    }

    public function onSaveEdited_DOMAIN($params)
    {
        if (xCore::getCurrentLicense() == xCore::getLicenseFromHost($params['data']['basic'])) {
            if ($this->_tree->reInitTreeObj($params['data']['id'], $params['data']['basic'], $params['data'],
                '_DOMAIN')
            ) {
                $this->initSlotz($params['data']['id'], $params['modules']);
                $this->pushMessage('domain-saved');
            }
        } else {
            $this->pushError('this-domain-cannot-be-plugged');
        }
    }

    public function onSave_DOMAIN($params)
    {
        if (xCore::getCurrentLicense() == xCore::getLicenseFromHost($params['data']['basic'])) {
            if ($this->_tree->initTreeObj(1, $params['data']['basic'], '_DOMAIN', $params['data'])) {
                $this->pushMessage('domain-saved');
            }
        } else {
            $this->pushError('this-domain-cannot-be-plugged');
        }
    }

    public function onCreate_DOMAIN($params)
    {
        $this->result['data']['TemplateFolder'] = $this->getCommonTemplates();
    }

}

?>