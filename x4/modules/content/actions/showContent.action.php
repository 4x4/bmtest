<?php
use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;


class showContentAction
    extends xAction
{
    public function run($params)
    {

        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        if (isset($pInfo['requestActionPath']) && !$params['params']['contentSourceId']) {
            $basic = substr($pInfo['requestActionPath'], 1);

            if (!$node = $this->_tree->selectStruct('*')->where(array
            (
                '@basic',
                '=',
                $basic
            ))->singleResult()->run()
            ) {
                XRegistry::get('TPA')->showError404Page();
            }

            $params['params']['contentSourceId'] = $node['id'];


        }

        $node = $this->_tree->getNodeInfo($params['params']['contentSourceId']);


        if (isset($node)) {
            $template = ($params['params']['Template']) ? $params['params']['Template'] : $node['params']['Template'];


            $this->setSeoData($node['params']);

            if (!$node['params']['__viewedTimes']) $node['params']['__viewedTimes'] = 0;

            $this->_tree->writeNodeParam($node['id'], '__viewedTimes', ++$node['params']['__viewedTimes']);

            $this->loadModuleTemplate($template);

            $this->articleData = $this->getContentData($params['params']['contentSourceId']);


            if (!empty($basic)) {

                $pages = XRegistry::get('pagesFront');
                $pathElement = $node;
                $pathElement['link'] = '';
                $pages->pushAdditionalBones($pathElement);
            }


            $ext = $this->_TMS->addReplace('content', 'object', $node);
            $ext = $this->_TMS->parseSection('content');

            return $ext;
        }
    }
}    
