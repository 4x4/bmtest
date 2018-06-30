<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class xpeApiJson
    extends xModuleApi
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function pipelineCall($params, $data)
    {
        if (!empty($params['pipeline']) && !empty($params['method'])) {
            pipelineField::loadPipeLine($params['pipeline']);
            $pipelineName=$params['pipeline'];
            $pipeline = new $pipelineName();
            $method = $params['method'] . 'Api';
            return $pipeline->$method($data);

        }
    }

}
