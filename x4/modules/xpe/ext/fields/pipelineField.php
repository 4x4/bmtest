<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

interface pipeline
{
    public function recieve($fieldContext);

}


class pipelineField extends personDataField implements personDataFieldInterface
{

    private $pipeline;
    static $loadedPipelines;
    public $optionsData;

    public function setOptions($value,$optionsData=null)
    {
        $this->setSource($value);
        $this->optionsData=$optionsData;
    }

    public static function loadPipeLine($pipelineName)
    {

        if (empty(self::$loadedPipelines[$pipelineName])) {

            $pipelineFile = xConfig::get('PATH', 'MODULES') . 'xpe/ext/pipelines/' . $pipelineName . '.php';

            if (file_exists($pipelineFile)) {
                require($pipelineFile);

            } else {

                throw new Exception('pipeline-is-not-exists ' . $pipelineFile);
            }

            self::$loadedPipelines[$pipelineName] = $pipelineName;
        }

    }

    public function setSource($pipelineName)
    {
        self::loadPipeLine($pipelineName);
        $this->pipeline = new $pipelineName();

    }


    public function importField($data)
    {
        $this->name = $data['name'];
        $this->alias = $data['alias'];
        $this->setSource($data['pipelineType']);
        $this->setValue();

    }

    public function exportField()
    {
        return array('fieldType' => 'pipelineField',
            'data' => array(
                'name' => $this->name,
                'alias' => $this->alias,
                'pipelineType' => str_replace('Pipeline', '', get_class($this->pipeline)),
                'value' => $this->value
            ));


    }

    public function setValue($value = null)
    {
        return $this->value = $this->pipeline->recieve($this);
    }

}


?>
