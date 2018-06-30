<?php

use X4\Classes\XTreeEngine;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XPDO;


class commentsCommon
    extends xModuleCommon
    implements xCommonInterface
{
    public $_useTree = true;

    public function __construct()
    {

        parent::__construct(__CLASS__);


        $this->_tree->setObject('_ROOT', array('Name'));
        $this->_tree->setObject('_TREAD', array('Alias', 'active', 'moderation', 'treadSort'), array('_ROOT'));
        $this->_tree->setObject('_COBJECT', array('module', 'marker', 'active', 'closed', 'cobjectId'), array('_TREAD'));


    }


    public function getCobjectByModule($cobjId, $module)
    {
        if ($results = $this->_tree->Search(array('cobjectId' => $cobjId, 'module' => $module), true)) {
            reset($results);
            return current($results);
        }

    }


    public function getCobjectByTread($cobjId, $treadId = null, $treadName = null)
    {
        if (!$treadId && $treadName) {
            $tread = $this->_tree->selectStruct(array('id'))->where(array('@ancestor', '=', 1), array('@basic', '=', $treadName))->run();

        }

        $instance = $this->_tree->selectStruct('*')->selectParams('*');

        if ($treadId) {
            $instance->addwhere(array('@ancestor', '=', $treadId));
        }

        if ($result = $instance->addWhere(array('cobjectId', '=', $cobjId))->singleResult()->run()) {
            return $result;
        }

    }


    public function getComment($id)
    {
        $result = XRegistry::get('XPDO')->query('select * from comments where id=' . $id);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row;

    }


    public function getTreadByName($name)
    {
        if ($name) {
            $value = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@basic', '=', $name))->singleResult()->run();
            return $value;
        }
    }


    public function defineFrontActions()
    {
        $this->defineAction('showLastComments');
        //$this->def_action('show_guestbook',$l['{show_guestbook}'],'ai_show_selected_banner');

    }


}
