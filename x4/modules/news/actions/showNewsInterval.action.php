<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class showNewsIntervalAction extends xAction
{
    public $_props;
    
    public function build($params)
    {
        

        $this->_props['newsOnPage']=$newsOnPage = isset($params['params']['OnPage']) ? (int)$params['params']['OnPage'] : $this->_config['showNewsPerPage'];

        $categories = json_decode($params['params']['Categories'], true);

        if ($params['params']['HideOldNews']) {
            $hideOldNews = true;
        }
        
       $this->_props['startPage']= $startItems = $startPage = isset($_GET['page']) ? $_GET['page'] : 0;

        if (is_array($categories)){

            $categoriesInfo = $this->_tree->selectStruct('*')->selectParams('*')->where(array(
                '@id',
                '=',
                $categories
            ))->format('keyval', 'id')->run();

            $newsServerPage = $this->createPageDestination($params['params']['DestinationPage']);

            if ($newsList = $this->_commonObj->selectNewsInterval($categories, $startItems, $newsOnPage, $where, 'desc', false, $hideOldNews)) {

                $this->_props['newList'] = $this->newsListTransform($newsList, $newsServerPage);
                $this->_props['count'] = $this->_commonObj->selectNewsInterval($categories, $startItems, $newsOnPage, $where, 'asc', true, $hideOldNews);                
                $this->_props['newsServerPage']=$newsServerPage;
                $this->_props['categoriesInfo']=$categoriesInfo;

            } 
        }

    
        
    }
    
    public function run($params)
    {

            $this->loadModuleTemplate($params['params']['Template']);
        
            if (!empty($this->_props['newList']))
            {            
                Common::parseNavPages($this->_props['count'], $this->_props['newsOnPage'], $this->_props['newsOnPage'], $this->_props['newsServerPage'], $this->_TMS);
                return $this->renderNews($this->_props['newList'],  $this->_props['newsServerPage'],  $this->_props['categoriesInfo']);

            } else {

                XRegistry::get('TPA')->showError404Page();

            }
    }

    
    public function runHeadless($params)
    {

            
        
            if (!empty($this->_props['newList']))
            {            
                $this->_props['paginator']=Common::parseNavPagesHeadless($this->_props['count'], $this->_props['newsOnPage'], $this->_props['newsOnPage'], $this->_props['newsServerPage']);
                return $this->_props;

            }            
            
            return array('emptyNews'=>true);
    }
    
}