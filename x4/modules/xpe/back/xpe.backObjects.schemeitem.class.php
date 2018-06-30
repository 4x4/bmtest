<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

trait _SCHEMEITEM
{


    private function getFieldsTypes()
    {
        
      
        $fieldsFiles = xConfig::get('PATH', 'MODULES') . 'xpe/ext/fields';
        $fields = $this->filesExtractor($fieldsFiles);
        $this->result['data']['Type'] = XHTML::arrayToXoadSelectOptions($fields, '', true);

    }

    public function onCreate_SCHEMEITEM($params)
    {
    
        $this->getFieldsTypes();
        $this->getPipelinesOptions();


    }

    public function getstorageParamNames()
    {
    }


    public function optionChangeSFE($params)
    {
        $pipelineName=$params['option'];
        pipelineField::loadPipeLine($pipelineName);
        $pipeline =new $pipelineName();
        if(method_exists($pipeline,'getDataOptions'))
        {
            $this->result['data']['OptionsData']= XHTML::arrayToXoadSelectOptions($pipeline->getDataOptions());

        }else{
            $this->result['data']=false;
        }

    }

    private function getPipelinesOptions()
    {

        $pipelinesFiles = xConfig::get('PATH', 'MODULES') . 'xpe/ext/pipelines';
        $fields = $this->filesExtractor($pipelinesFiles);
        $this->result['data']['Options'] = XHTML::arrayToXoadSelectOptions($fields, '', true);

    }

    public function onSave_SCHEMEITEM($params)
    {


    }

    public function onEdit_SCHEMEITEM($params)
    {
        $this->getFieldsTypes();
        $this->getPipelinesOptions();
    }


    public function onSaveEdited_SCHEMEITEM($params)
    {


    }

}

?>