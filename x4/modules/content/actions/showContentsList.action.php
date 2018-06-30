<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;


class showContentsListAction
    extends xAction
{
    public function run($params)
    {
        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        $this->loadModuleTemplate($params['params']['listTemplate']);

        $startPage = isset($_GET['page']) ? (int)$_GET['page'] : 0;
        $onpage = (int)$params['params']['onPage'];

        $pages = xCore::loadCommonClass('pages');

        $destinationPage = $pages->createPagePath($params['params']['destinationPage']);

        $allContents = $this->_tree->selectStruct(array('id'))->childs($params['params']['category'], 1)->run();

        if ($allContentsCount = count($allContents)) {
            Common::parseNavPages($allContentsCount, $onpage, $startPage, $destinationPage, $this->_TMS, 'page', true);

            $articles = $this->_tree->selectStruct('*')->selectParams('*')->sortBy('@id', 'DESC', 'SIGNED')->childs($params['params']['category'], 1);
            if ($onpage) $articles->limit($startPage, $onpage);
            $articlesList = $articles->run();


            foreach ($articlesList as $article) {
                $articleData = array('params' => $article['params'], 'link' => $destinationPage . '/' . $link . $article['basic'], 'id' => $article['id'], 'contentData' => $this->getContentData($article['id']));
                $articleList[] = $articleData;

            }

        }

        $category = $this->_tree->getNodeInfo($params['category']);

        $this->seoConfirm($category);

        $this->_TMS->addMassReplace('articlesList', array('category' => $category, 'articlesList' => $articleList));
        return $this->_TMS->parseSection('articlesList', true);


    }
}
