<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

trait _TUNES

{
    public function onEdit_TUNES($params)
    {
        $ancestor = $this->_commonObj->createTunesBranch('TUNES');
        $basic = 'tunesObject';

        $data = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@ancestor', '=', $ancestor), array('@basic', '=', $basic))->singleResult()->run();

        if ($statuses = $this->_commonObj->getStatusesList(true)) {
            $statuses = XARRAY::arrToLev($statuses, 'id', 'params', 'Name');
            $data['params']['defaultOrderStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['defaultOrderStatus']);
            $data['params']['editedStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['editedStatus']);
            $data['params']['notFinshedStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['notFinshedStatus']);
            $data['params']['payedStatus'] = XHTML::arrayToXoadSelectOptions($statuses, $data['params']['payedStatus']);
        }

          
            
        
        
        $pages = xCore::loadCommonClass('pages');

        $data['params']['notFinishedOrdersUrl'] = $pages->getPagesByModuleServerSelector('showBasket', $data['params']['notFinishedOrdersUrl']);


        $this->result['data'] = $data['params'];

    }


    public function onSave_TUNES($params)
    {


        $ancestor = $this->_commonObj->createTunesBranch('TUNES');
        $basic = 'tunesObject';

        $this->_tree->capture=true;

        if ($init = $this->_tree->initTreeObj($ancestor, $basic, '_TUNES', $params['data'])) {
            $this->pushMessage('tunes-saved');
        }

        $excp = $this->_tree->getLastException();

        if ($excp && ($excp[0]->getMessage() == 'non-uniq-ancestor')) {
            $id = $this->_tree->selectStruct('*')->where(array('@ancestor', '=', $ancestor), array('@basic', '=', $basic))->run();

            if ($this->_tree->reInitTreeObj($id[0]['id'], $basic, $params['data'], '_TUNES')) {
                $this->pushMessage('tunes-saved');
            }
        }

    }


}

?>
