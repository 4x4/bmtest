<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\TableJsonSource;
use X4\Classes\XPDO;

require(xConfig::get('PATH', 'MODULES') . 'content/back/content.backObjects.content.class.php');
require(xConfig::get('PATH', 'MODULES') . 'content/back/content.backObjects.contentgroup.class.php');

class contentBack extends xModuleBack
{


    use _CONTENT, _CONTENTGROUP;
    public $fields;
    public $fieldGroups;

    public function __construct()
    {
        parent::__construct(__CLASS__);

    }


    public function copyContent($params)
    {

        $ancestor = $params['ancestor'];
        $ancestor = $this->_tree->getNodeInfo($params['ancestor']);
        $params['ancestor'] = $ancestor['ancestor'];

        $this->copyObj($params, $this->_tree);

    }


    /**
     * 2 типа аттрибутов
     * группы и поля
     * группы могут содержать в себе поля, которые можно реплицировать на фронте
     */

    public function fieldBase($params, $group)
    {
        if ($group) {
            $params['type'] = 'GROUP';
        }

        if (!$params['order']) {
            $this->fields[] = $params;

        } else {

            if ($this->fields[$params['order']]) {
                $getSlice = array_slice($this->fields, $params['order'], 0, $params);

            } else {
                $this->fields[$params['order']] = $params;
            }

        }
    }

    public function field($params, $data)
    {
        $params['id'] = $data['return'];
        if (is_array($params['items'])) {
            $group = true;
        }
        $this->fieldBase($params, $group);
    }


    public function parseTemplate($params)
    {

        XNameSpaceHolder::addMethodsToNS('content', array(
            'field',
            'fieldGroup'
        ), $this);

        $this->loadModuleTemplate($params['Template'], 'Front');
        $this->_TMS->parseSection('content');
        $this->result['fields'] = $this->fields;

    }

    public function getTemplateListSubs()
    {
        if ($templatesList = $this->getTemplatesList($this->_moduleName, false, true)) {
            return XHTML::arrayToXoadSelectOptions($templatesList, false, true);
        }

    }


    public function treeDynamicFullXLS($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'imagesIcon' => array(
                '_CONTENTGROUP' => 'folder.gif',
                '_CONTENT' => 'leaf.gif'
            ),
            'gridFormat' => true,
            'showNodesWithObjType' => array(
                '_ROOT',
                '_CONTENTGROUP',
                '_CONTENT'
            ),

            //   'endLeafs'=>array('_CONTENTGROUP'),
            'columns' => array(
                '>Name' => array()
            )
        );
        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }


    public function treeDynamicXLS($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'imagesIcon' => array(
                '_CONTENTGROUP' => 'folder.gif'
            ),
            'gridFormat' => true,
            'showNodesWithObjType' => array(
                '_ROOT',
                '_CONTENTGROUP'

            ),

            'columns' => array(
                '>Name' => array()
            )
        );
        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }


    public function contentsTable($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));


        $opt = array(
            'showNodesWithObjType' => array(
                '_CONTENT'
            ),
            'columns' => array(
                'id' => array(),
                '>__nodeChanged' => array(
                    'onAttribute' => function ($params, $value) {
                        return date('d.m.y h:i:s', $value);

                    }
                ),
                '>Name' => array()
            )
        );

        $source->setOptions($opt);

        $this->result = $source->createView($params['id']);


    }


    public function onAction_contentServer($params)
    {
        if (isset($params['data']['params'])) {
        }

        $actions = $this->_commonObj->getServerActionsFull($params['action']);
        $this->result['actionDataForm']['secondaryAction'] = XHTML::arrayToXoadSelectOptions($actions, false, true);
        $this->result['actionDataForm']['Template'] = $this->getTemplateListSubs();


    }


    public function onAction_showContentsList($params)
    {

        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];

        }

        $pages = xCore::loadCommonClass('pages');

        $categories = $this->_tree->selectStruct(array('id'))->selectParams(array('Name'))->childs(1, 1)->format('valparams', 'id', 'Name')->run();

        $this->result['actionDataForm']['category'] = XHTML::arrayToXoadSelectOptions($categories, false, true);
        $this->result['actionDataForm']['destinationPage'] = $pages->getPagesByModuleServerSelector('contentServer', $selected);
        $this->result['actionDataForm']['listTemplate'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['listTemplate'], array('.contentsList.html'));
    }


    public function onSlotModuleSave_contentServer($params)
    {
        $pages = xCore::loadCommonClass('pages');

        $contentPageLink = $pages->createPagePath($params['data']['pageId'], true);
        $source = $contentPageLink . '/(?!~)(.*?)';
        $destination = $contentPageLink . '/~showContent$1/';
        $pages->createNewRoute($source, $destination);
    }

    public function onAction_showContent($params)
    {

        if (isset($params['data']['params'])) {

            $node = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', $params['data']['params']['contentSourceId']))->run();

            $params['data']['params']['contentSource'] = $node['paramPathValue'];

            $this->result['actionDataForm'] = $params['data']['params'];
        }

        $this->result['actionDataForm']['Template'] = $this->getTemplateListSubs();
    }

}
