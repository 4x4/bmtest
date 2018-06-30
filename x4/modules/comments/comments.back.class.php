<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

require(xConfig::get('PATH', 'MODULES') . 'comments/back/comments.backObjects.tread.class.php');
require(xConfig::get('PATH', 'MODULES') . 'comments/back/comments.backObjects.cobject.class.php');
require(xConfig::get('PATH', 'MODULES') . 'comments/back/comments.backObjects.comment.class.php');

class commentsBack extends xModuleBack
{

    use _TREAD, _COBJECT, _COMMENT;


    public function __construct()
    {

        parent::__construct(__CLASS__);
        $this->_EVM->on($this->_moduleName . '.onSaveReply_COMMENT', 'notifyOnSaveReply', $this);
    }


    public function treeDynamicXLS($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $opt = array
        (
            'imagesIcon' => array('_NEWSGROUP' => 'folder.gif'),
            'gridFormat' => true,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_TREAD'
            ),
            'columns' => array('>Alias' => array())
        );

        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }


}


?>
