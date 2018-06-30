<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;


class contentFront extends xModule
{
    public $articleData = array();

    public function __construct()
    {
        parent::__construct(__CLASS__);
        if (xConfig::get('GLOBAL', 'currentMode') == 'front') {

            $this->_tree->cacheState($this->_config['cacheTree']['tree']);
            if ($this->_config['boostTree']) {
                $this->_tree->startBooster();
                $this->_tree->setTreeBoosted();
            }
        }

        XNameSpaceHolder::addMethodsToNS('content', array(
            'field',
            'fieldGroup'
        ), $this);

    }

    public function contentServer($params)
    {
        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        if (empty($pInfo['requestAction'])) {

            $params['_Action'] = $params['params']['secondaryAction'];
            $params['params'] = $params['secondary'];
            unset($params['secondary']);

            return $this->execute($params, $params['base']['moduleId']);

        }

    }

    private function returnFields($key)
    {

        if (isset($this->articleData[$key])) {
            return $this->articleData[$key];
        }

    }

    public function field($params, $context)
    {
        return $this->returnFields($context['return']);
    }


    public function fieldGroup($params, $context)
    {
        return $this->returnFields($context['return']);
    }


    public function getContentData($id)
    {
        if ($fieldGroups = $this->_tree->selectStruct(array
        (
            'id',
            'basic'
        ))->selectParams('*')->childs($id, 2)->format('valval', 'basic', 'params')->run()
        ) {
            foreach ($fieldGroups as $gkey => $fieldGroup) {
                foreach ($fieldGroup as $key => $field) {
                    $fData = explode('__', $key);

                    if ($fData[1] == 'value') {
                        $articleData[$gkey] = $field;
                    } else {
                        $articleData[$gkey][$fData[1]][$fData[0]] = $field;
                    }
                }
            }

        }


        $sort = $articleData[$gkey];

        if (!empty($sort) && is_array($sort)) {
            ksort($sort);
        }

        $articleData[$gkey] = $sort;

        return $articleData;
    }


    public function setSeoData($object)
    {

        $data = XRegistry::get('EVM')->fire($this->_moduleName . '.setSeoData', array('object' => $object));

        if (!empty($data)) {
            $object = $data;
        }

        XRegistry::get('TPA')->setSeoData($object);

    }

}
