<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;


trait _FUSER
{
    private function getIshopPrices()
    {
        $catalog =  xCore::moduleFactory('catalog.back');

        $prices=$catalog->_commonObj->_propertySetsTree->selectAll()->where(array('type','=','currencyIshop'))->run();


        if(!empty($prices)){

            $outPrices=array();

            foreach($prices as $price)
            {
                $parent=$catalog->_commonObj->_propertySetsTree->getNodeStruct($price['ancestor']);
                $outPrices[$parent['basic'].'@'.$price['basic']]=$price['params']['alias'];
            }
        return $outPrices;
        }

    }


    public function onEdit_FUSER($params)
    {
        $data = $this->_tree->getNodeInfo($params['id'],true);
        $data['params']['fuserGroup'] = $this->_tree->readNodeParam($data['ancestor'], 'Name');
        $data['params']['login'] = $data['basic'];
        $additional=$this->_tree->selectParams('*')->childs($params['id'],1)->run();
        $this->result['ishopPrices']=$this->getIshopPrices();
        $this->result['data'] = $data['params'];

        if(!empty($data['params']['accessiblePrices'])){

            foreach($data['params']['accessiblePrices'] as $key=>$price)
            {
                 $key=str_replace('.','@',$key);
                 $this->result['ishopData']['accessiblePrices.'.$key]=$price;
            }
        }

        $this->result['ishopData']['defaultPrice']=str_replace('.','@',$data['params']['defaultPrice']);

        if(!empty($additional[0])) {
            $this->result['additionalFields'] = $additional[0]['params'];
        }
    }


    public function onSaveEdited_FUSER($params)
    {
        if ($params['data']) {

            if(!empty($params['ishopData']['accessiblePrices']))
            {
                 foreach($params['ishopData']['accessiblePrices'] as $key=>$value){
                     $key=str_replace('@','.',$key);
                     $params['data']['accessiblePrices'][$key]=$value;
                 }
            }

            $params['data']['defaultPrice']=str_replace('@','.',$params['ishopData']['defaultPrice']);

            $this->_tree->reInitTreeObj($params['id'], '%SAME%', $params['data']);
            $childs=$this->_tree->selectStruct('*')->childs($params['id'],1)->run();

            if(!empty($childs[0])) {
                $this->_tree->reInitTreeObj($childs[0]['id'], '%SAME%', $params['additionalFields']);
            }else{
                $this->_tree->initTreeObj($params['id'], '%SAMEASID%','_FUSEREXTDATA', $params['additionalFields']);
            }

            xRegistry::get('EVM')->fire($this->_moduleName . '.fuserEditedSaved', array('user' => $params));

            return new okResult('fuser-edited-saved');
        } else {
            return new badResult('fuser-not-saved');
        }
    }


    public function onSave_FUSER($params)
    {
        $this->_tree->setUniqType(2);
        $existed = $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->where(array('@basic', '=', $params['data']['login']))->run();
        $existedEmail = $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->where(array('email', '=', $params['data']['email']))->run();

        if (!$existed && !$existedEmail) {
            if ($id = $this->_tree->initTreeObj($params['data']['fuserGroup'], $params['data']['login'], '_FUSER', $params['data'])) {
              $this->_tree->initTreeObj($id, '%SAMEASID%', '_FUSEREXTDATA', $params['additionalFields']);
              xRegistry::get('EVM')->fire($this->_moduleName . '.fuserSaved', array('user' => $params));
              return new okResult('fuser-saved');
            }
        } else {
            return new badResult('fuser-already-exists');
        }
    }


    public function onCreate_FUSER($params)
    {
        if ($groups = $this->getGroups()) {
            $this->result['data']['fuserGroup'] = XHTML::arrayToXoadSelectOptions($groups, null, true);
        }
    }

    public function changeUserPassword($params)
    {
        $password = md5(strrev($params['data']['newPassword']));

        if ($id = $this->_tree->writeNodeParam($params['data']['id'], 'password', $password)) ;
        {
            return new okResult('new-password-saved');
        }
    }

    public function fusersTable($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));

        $opt = array(
            'showNodesWithObjType' => array(
                '_FUSER'
            ),
            'onPage' => $params['onPage'],
            'columns' => array(
                'id' => array(),
                'basic' => array(),
                '>name' => array(),
                '>surname' => array(),
                '>email' => array(),
                '>active' => array()
            )
        );

        if (!$params['page']) $params['page'] = 1;
        $source->setOptions($opt);

        $this->result = $source->createView($params['id'], $params['page']);
    }

    public function getAdditionalFields($params)
    {
        $this->result['additionalFields']=$this->_config['additionalFields'];

    }
}

?>
