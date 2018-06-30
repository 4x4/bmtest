<?php
//'catalog.property.currencyIshopProperty:afterHandleTypeFront'
/**
 * options
 *  -storagePath
 */

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;

class priceChanger
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


        $out['object'] = $k['data']['object'];
        $out['value'] = $k['data']['value'];

        if ($out['object']['obj_type'] == '_SKUOBJ') {

            $net = xCore::moduleFactory('catalog.front')->_tree->getNodeStruct($out['object']['netid']);

            if (!in_array((int)$this->options['showGroupId'], $net['path'])) {

                return $out;
            }
        }

        $out['object']['params']['percent'] = $this->options['percent'];
        $out['object']['params']['regularPrice__discount'] = $out['object']['params']['regularPrice__discount'] - ($out['object']['params']['regularPrice__discount'] / 100) * $this->options['percent'];

        return $out;
    }

    public function run()
    {
        XRegistry::get('EVM')->on('catalog.property.currencyIshopProperty:beforeHandleTypeFront', 'changer', $this);
    }


    public function onAfterEdit($params)
    {

        $groupPath = xCore::moduleFactory('catalog.front')->_tree->selectStruct(array('id'))->getParamPath('Name', '/', true)
            ->where(array('@id', '=',(int)$params['showGroupId']))->run();

        if(!empty($groupPath)){
            return array('showGroup'=>$groupPath['paramPathValue']);
        }

    }

}

?>