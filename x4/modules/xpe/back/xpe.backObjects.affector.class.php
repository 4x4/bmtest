<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

trait _AFFECTOR
{


    public function getAffectors()
    {

        $affectorFiles = xConfig::get('PATH', 'MODULES') . 'xpe/ext/affectors';
        $fields = $this->filesExtractor($affectorFiles);
        $this->result['data']['affector'] = XHTML::arrayToXoadSelectOptions($fields, '', true);

    }

    public function onCreate_AFFECTOR($params)
    {
        $this->getAffectors();
    }


    public function onEdit_AFFECTOR($params)
    {
        $this->getAffectors();
    }

    public function onEdit_AFFECTOR_after($params){

        $affector=person::loadAffector($params['affector']);
        if(method_exists($affector,'onAfterEdit')) {
            $this->result['affectorParams'] = $affector->onAfterEdit($params);
        }else{

            $this->result['affectorParams'] =null;
        }
    }

}

?>