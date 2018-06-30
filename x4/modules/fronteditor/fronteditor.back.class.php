<?php

class fronteditorBack
    extends xModuleBack
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->pages = xCore::moduleFactory('pages.back');
    }

    public function getModuleParams($params)
    {

        $module = $this->pages->_tree->getNodeInfo($params['id']);

        foreach ($module['params'] as $paramName => $param) {

            if (stristr($paramName, 'Template')) {
                $module['templates'][$paramName]['fullPathBase'] = base64_encode(xConfig::get('PATH', 'TEMPLATES') . '_modules/' . $module['params']['_Type'] . '/' . $param);
                $module['templates'][$paramName]['name'] = $param;
            }

        }

        $this->result['module'] = $module;

    }
}
