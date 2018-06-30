<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class showNewsAction extends xAction

{
    public function run($params)
    {

        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        if (isset($pInfo['requestActionPath'])) {
            $basic = substr($pInfo['requestActionPath'], 1);
            $news = $this->_commonObj->selectNews($basic);
            $this->loadModuleTemplate($params['params']['Template']);

            $this->setSeoData($news);

            $newsNode = array(
                'params' => array(
                    'Name' => $news['header']
                )
            );

            $pages = XRegistry::get('pagesFront');
            $pages->pushAdditionalBones($newsNode);

            if (!empty($news['tags'])) {
                $news['tags'] = $this->tagsAgregate($news['tags'], $pInfo['pageLinkHost']);
            }

            $this->_TMS->addMassReplace('newsSingle', $news);
            return $this->_TMS->parseSection('newsSingle');
        }

    }
}



