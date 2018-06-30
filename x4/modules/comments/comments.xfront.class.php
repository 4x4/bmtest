<?php

use X4\Classes\XTreeEngine;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XPDO;

class commentsXfront extends commentsFront
{

    public function addCommentRoute($params)
    {

        if ($params['route']) {
            $route = $params['route'];
            $route[0] = strtoupper($route[0]);

            $method = 'addComment' . $route;

            if (method_exists($this, $method)) {
                $this->{$method}($params);
            }
        }

    }


    public function addCommentContent($params)
    {

        $content = xCore::moduleFactory('content.front');

        $marker = $content->_tree->readNodeParam($params['id'], 'Name');

        $this->result["result_code"] = $this->_addComment(array
        (
            'cobjectId' => $params['id'],
            'tread' => $params['tread'],
            'module' => 'content',
            'marker' => $marker

        ), $params['comment']);
    }


    public function addCommentCatalog($params)
    {


        $catalog = xCore::moduleFactory('catalog.front');

        if (empty($params['marker'])) {
            $marker = $catalog->_tree->readNodeParam($params['id'], 'Name');
        } else {
            $marker = $params['marker'];
        }

        $this->result["result_code"] = $this->_addComment(array
        (
            'cobjectId' => $params['id'],
            'tread' => $params['tread'],
            'module' => 'catalog',
            'marker' => $marker

        ), $params['comment']);
    }


    public function addCommentNews($params)
    {
        $news = xCore::moduleFactory('news.front');
        $newsData = $news->_commonObj->selectNewsById($params['id']);
        $marker = $newsData['header'];

        $this->result["result_code"] = $this->_addComment(array
        (
            'cobjectId' => $params['id'],
            'tread' => $params['tread'],
            'module' => 'news',
            'marker' => $marker

        ), $params['comment']);

    }
}