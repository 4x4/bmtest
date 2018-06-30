<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

trait _CURRENCY

{
    public function currencyList($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $opt = array
        (
            'showNodesWithObjType' => array('_CURRENCY'),
            'columns' => array
            (
                'id' => array(),
                '>Name' => array(),
                'basic' => array(),
                '>rate' => array()
            )
        );

        $source->setOptions($opt);
        $id = $this->_commonObj->getBranchId('CURRENCY');
        $this->result = $source->createView($id);
    }

    public function onEdit_CURRENCY($params)
    {
        $node = $this->_tree->getNodeInfo($params['id']);
        $node['params']['currencyId'] = $node['basic'];
        $this->result['data'] = $node['params'];
    }

    public function onSave_CURRENCY($params)
    {
        $ancestor = $this->_commonObj->createTunesBranch('CURRENCY');

        $basic = $params['data']['currencyId'];
        unset($params['data']['currencyId']);


        if ($id = $this->_tree->initTreeObj($ancestor, $basic, '_CURRENCY', $params['data'])) {

            if ($params['data']['isMain']) {
                $this->changeMainCurrency($id);
            }

            $this->pushMessage('currency-saved');
        }
    }

    public function onSaveEdited_CURRENCY($params)
    {
        $basic = $params['data']['currencyId'];
        unset($params['data']['currencyId']);


        if ($this->_tree->reInitTreeObj($params['id'], $basic, $params['data'], '_CURRENCY')) {


            if ($params['data']['isMain']) {
                $this->changeMainCurrency($params['id']);
            }


            $this->pushMessage('currency-edited-saved');
        }
    }


    public function changeMainCurrency($currencyId)
    {
        $currencies = $this->_commonObj->getCurrenciesList(true);


        if (!empty($currencies)) {
            $mainCurrency = $currencies[$currencyId];

            foreach ($currencies as $currId => $currency) {
                if ($currId == $currencyId) {
                    $isMain = 1;

                } else {

                    $isMain = '';
                }

                $rate = $currency['rate'] / $mainCurrency['rate'];


                $currency['newRate'] = $rate;
                $currency['isMain'] = $isMain;
                $newCurrMatrix[$currId] = $currency;

                $this->_tree->writeNodeParams($currId, array('isMain' => $isMain, 'rate' => $rate));
            }


            $this->_EVM->fire('ishop:afterCurrencyChange', array('instance' => $this, 'currMatrix' => $newCurrMatrix));

            //$this->_tree->update(array('isMain'=>''))->where(array('@id','=',$currIds))->run();

        }

    }


    public function deleteCurrency($params)
    {
        $this->deleteObj($params, $this->_tree);
    }


}

?>