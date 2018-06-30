<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

trait _PAYSYSTEM
{

    public function onEdit_PAYSYSTEM($params)
    {

        $data = $this->_commonObj->getPaysystemData($params['id']);

        $statuses = $this->_commonObj->getStatusesList(true);

        $statuses = XARRAY::arrToLev($statuses, 'id', 'params', 'Name');

        $data['params']['readyToPayStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['readyToPayStatus']);
        $data['params']['orderPayedStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['orderPayedStatus']);
        $data['params']['cancelStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['cancelStatus']);
        $data['params']['transactionBlockingStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['transactionBlockingStatus']);

        if (!$data['params']['Name']) {
            $data['params']['Name'] = $this->getTemplateAlias(xConfig::get('PATH', 'MODULES') . $this->_moduleName . '/paysystems/' . $params['id'] . '/' . $params['id'] . '.paysystem.html');
        }

        $tpl = xConfig::get('PATH', 'MODULES') . $this->_moduleName . '/paysystems/' . $params['id'] . '/' . $params['id'] . '.paysystem.html';
        $this->_TMS->addFileSection(XRegistry::get('ADM')->tplLangConvert(false, $tpl, $this->_moduleName), true);
        $this->result['tpl'] = $this->_TMS->parseSection($params['id'] . '.paysystem');
        $this->result['data'] = $data['params'];
    }


    public function onSave_PAYSYSTEM($params)
    {


        $ancestor = $this->_commonObj->createTunesBranch('PAYSYSTEM');

        $basic = $params['data']['paysystem'];
        unset($params['data']['paysystem']);

        if ($init = $this->_tree->initTreeObj($ancestor, $basic, '_PAYSYSTEM', $params['data'])) {
            $this->pushMessage('paysystem-saved');
        }

        $excp = $this->_tree->getLastException();

        if ($excp && ($excp[0]->getMessage() == 'non-uniq-ancestor')) {
            $id = $this->_tree->selectStruct('*')->where(array('@ancestor', '=', $ancestor), array('@basic', '=', $basic))->run();

            if ($this->_tree->reInitTreeObj($id[0]['id'], $basic, $params['data'], '_PAYSYSTEM')) {
                $this->pushMessage('paysystem-edited-saved');
            }
        }

    }


    public function loadPaysystemsList($params)
    {


        foreach (new DirectoryIterator(xConfig::get('PATH', 'MODULES') . $this->_moduleName . '/paysystems/') as $fileInfo) {
            if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                $name = $fileInfo->getFilename();
                $path = $fileInfo->getPath();

                $alias = $this->getTemplateAlias($path . '/' . $name . '/' . $name . '.paysystem.html');
                $paysystem = $this->_commonObj->getPaysystemData($name);

                $this->result['data_set']['rows'][$name] = array('data' => array(0 => $name, 1 => $alias, 2 => $paysystem['params']['priority'], 3 => $paysystem['params']['active']));
            }


        }

    }


}


?>