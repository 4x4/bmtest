<?php
//'catalog.property.currencyIshopProperty:afterHandleTypeFront'
/**
 * options
 *  -storagePath
 */

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;

class popupChanger
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
        $modulePath=xConfig::get('PATH','MODULES').'xpe/ext/affectors/popupChanger/popupChanger.html';
        XRegistry::get('TMS')->addFileSection($modulePath);


        $content=XRegistry::get('ENHANCE')->callModuleAction(array(
            "module"=>"content",
            "params"=>array(
                "_cacheLevel"=>"dynamic",
                "_Cache"=>"1",
                "_Action"=>"showContent",
                "_Priority"=>"1",
                "contentSourceId"=>(int)$this->options['contentSourceId']
            )
        ));

        XRegistry::get('TMS')->addMassReplace('popupChanger',array(
        'content'=>$content
        ));

        XRegistry::get('TPA')->setGlobalField(array('OUTPUT'=> XRegistry::get('TMS')->parseSection('popupChanger')));

       // jsCollector::push('main',HOST.'x4/modules/xpe/ext/affectors/popupChanger/popupChanger.html');
    }

    public function run()
    {
        XRegistry::get('EVM')->on('agregator:end', 'changer', $this);
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
