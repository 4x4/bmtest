<?php

/**
 * options
 *  -storagePath
 */

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;

class contentChanger
{
    public $options;

    public function __construct()
    {
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function changer($k, $v = null)
    {

        $actionDataStructured = $k['data']['actionDataStructured'];


        if ((int)$actionDataStructured['params']['contentSourceId'] == (int)$this->options['contentSourceId']) {

            $actionDataStructured['params']['contentSourceId'] = $this->options['contentTargetId'];
        }

        return array('action' => $actionDataStructured);
    }

    public function run()
    {
        XRegistry::get('EVM')->on('content.front.onBeforeActionCall', 'changer', $this);
    }


    public function onAfterEdit($params)
    {

        $groupPath = xCore::moduleFactory('content.front')->_tree->selectStruct(array('id'))->getParamPath('Name', '/', true)
            ->where(array('@id', '=',(int)$params['contentSourceId']))->run();

        if(!empty($groupPath)){
            return array('contentSource'=>$groupPath['paramPathValue']);
        }

    }


}

?>
