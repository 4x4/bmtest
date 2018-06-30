<?php

use X4\Classes\XTreeEngine;
use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

class formsCommon extends xModuleCommon implements xCommonInterface
{
    public $_useTree = true;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->_tree->setObject('_ROOT', array('Name'));

        $this->_tree->setObject('_FORM', array
        (
            //'Category',
            'Name',
            'Title',
            'Disable',
            'Subject',
            'Template',
            'submitTemplate',
            'Emails',
            //'ContactNameAuthor',
            'Charset',
            'save_to_server',
            'use_captcha',
            'captcha_settings',
            'Async',
            'Timeout',
            'Description',
            'Author',
            'message_after'
        ), array('_ROOT'));

        $this->_tree->setObject('_FIELDSET', array(
            //'Category',
            'Name', //aka = 'Name' & 'Legend'
            'Disable',
            //'Form'
            'Description' //aka = 'Title'
        ), array('_FORM'));

        $this->_tree->setObject('_FIELD', null, array('_FORM', '_FIELDSET'));


        $this->_treeMessages = new XTreeEngine('forms_messages', XRegistry::get('XPDO'));
        $this->_treeMessages->setLevels(5);
        $this->_treeMessages->setUniqType(1);
        $this->_treeMessages->setObject('_ROOT', array('Name'));
        $this->_treeMessages->setObject('_MESSAGEGROUP', null, array('_ROOT'));
        $this->_treeMessages->setObject('_MESSAGE', null, array('_MESSAGEGROUP'));
        $this->_treeMessages->setObject('_MESSAGEFIELDSET', null, array('_MESSAGE'));
        $this->_treeMessages->setObject('_MESSAGEFIELD', null, array('_MESSAGEFIELDSET'));
    }


    function defineFrontActions()
    {
        $this->defineAction('showForms', array('serverActions' => array('submitForm'))); //One Form
        //$this->defineAction('showGroupsList');
        //$this->defineAction('formsServer', array('serverActions' => array('showForms')));
    }
}

