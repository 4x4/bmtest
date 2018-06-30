<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\TableJsonSource;
use X4\Classes\TreeJsonSource;
use X4\Classes\XPDO;
use X4\Classes\XCache;

require(xConfig::get('PATH', 'MODULES') . 'forms/forms.backObjects.form.class.php');

class formsBack extends xModuleBack
{
    use _FORM;

    public $fieldsets;
    public $fieldGroups;

    function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function formsTable($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $options = array(
            'showNodesWithObjType' => array('_FORM'),
            'columns' => array(
                'id' => array(),
                '>__nodeChanged' => array('onAttribute' => function ($params, $value) {
                    return date('d.m.y h:i', $value);
                }),
                '>Name' => array(),
                '>Author' => array(),
                '>Description' => array(),
                '>Disable' => array()
            )
        );

        $source->setOptions($options);
        $this->result = $source->createView($params['id']);
    }

    /**
     * 2 типа аттрибутов
     * группы и поля
     * группы могут содержать в себе поля, которые можно реплицировать на фронте
     */
    public function fieldsetBase($params, $group)
    {
        if ($group) {
            $params['type'] = 'GROUP';
        }

        if (!$params['order']) {
            $this->fieldsets[] = $params;

        } else {

            if ($this->fieldsets[$params['order']]) {
                $getSlice = array_slice($this->fieldsets, $params['order'], 0, $params);
            } else {
                $this->fieldsets[$params['order']] = $params;
            }

        }
    }

    public function fieldset($params, $data)
    {
        $params['id'] = $data['return'];
        if (is_array($params['items'])) {
            $group = true;
        }
        $this->fieldsetBase($params, $group);
    }

    public function parseTemplate($params)
    {
        xNameSpaceHolder::addMethodsToNS('forms', array('fieldset'), $this);
        $this->loadModuleTemplate($params['Template'], 'Front');
        $this->_TMS->parseSection('forms');
        $this->result['fieldsets'] = $this->fieldsets;
    }

    public function getForm($params)
    {
        if ($params['id']) {
            $form = $this->_tree->getNodeInfo((int)$params['id']);

            if ($form['id']) {
                $nodes = $this->_tree->selectStruct('*')->selectParams('*')->childs((int)$form['id'])->asTree()->run();   //sortby('@rate' ,'asc')->
                $fieldsets = array();

                while (list($k, $node) = each($nodes->tree[$form['id']])) {
                    $fieldsets[$k] = $node;

                    while (list($key, $value) = each($nodes->tree[$k])) {
                        $nodes->tree[$k][$key]['params']['settings'] = unserialize($value['params']['settings']);
                    }

                    $fieldsets[$k]['fields'] = array_values($nodes->tree[$k]);
                }

                $this->result['form'] = array('data' => $form, 'fieldsets' => $fieldsets);
                return true;
            }
        }

        $this->result['form'] = false;
        return false;
    }


    public function switchForm($params){}

    public function delete_FORM($params)
    {
        if (isset($params['id']) && $params['id'] > 0) {
            $form = $this->_tree->readNodeParam($params['id'], 'Name');
            $this->_tree->childs($params['id'])->delete()->run();
            $this->_tree->childs(1, 1)->where(array('@id', '=', $params['id']))->delete()->run();
            $this->result['del'] = $form;
            return;
        }
    }

    public function loadMessages($parameters)
    {
        $this->result['messages'] = xPDO::selectIN('*', 'forms_messages', '', 'ORDER BY `id` DESC');
    }

    public function messagesTable($params)
    {
        $source = Common::classesFactory('TableJsonSource', array());

        if (!$params['page']) $params['page'] = 1;

        $opt = array(
            'onResultSet' => function($set){
                if (isset($set)) {
                    $data = array();

                    foreach ($set as $setItem) {
                        $setItem['message'] = strip_tags(trim($setItem['message']));
                        $setItem['message'] = substr($setItem['message'],0,400);
                        $data[] = array('data' => array_values($setItem), 'id' => $setItem['id']);
                    }

                    return $data;
                }
            },
            'vanillaFormat' => 1,
            'table' => 'forms_messages',
            'order' => array('id', 'desc'),
            'idAsNumerator' => 'id',
            'onPage' => $params['onPage'],
            'columns' => array(
                'id' => array(),
                'form_id' => array(),
                'Name' => array(),
                'date' => array(),
                'message' => array(),
                'status' => array(),
                'archive' => array()
            )
        );

        $source->setOptions($opt);
        unset($this->result['data']);

        $this->result = $source->createView(1, $params['page']);
    }

    public function openSelectedMessage($params)
    {
        if(isset($params['id']) && $params['id'] > 0) {
            $msg = xPDO::selectIN('*', 'forms_messages', 'id=' . $params['id']);

            if(!empty($msg[0])) {
                $this->result['message'] = $msg[0];
                unset($msg);
            }
        }
    }

    public function deleteMessage($params)
    {
        if (is_array($params['id']) && !empty($params['id'])) {
            if(xPDO::deleteIN('forms_messages', $params['id'])) {
                $this->result['del'] = true;
            } else {
                $this->result['del'] = false;
            }
        } else {
            $this->result['del'] = false;
        }
    }

    public function setReadStatus($params)
    {
        if(isset($params['id']) && $params['id'] > 0) {
            if(xPDO::UpdateIN('forms_messages', (int)$params['id'], array('status' => (int)$params['state']))) {
                $this->result['read'] = true;
            }
        }
    }


    public function treeDynamicFullXLS($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));
        $opt = array(
            'imagesIcon' => array(
                //'_FORMSGROUP' => 'folder.gif',
                '_FORM' => 'leaf.gif'
            ),
            'gridFormat' => true,
            'showNodesWithObjType' => array(
                '_ROOT',
                //'_FORMSGROUP',
                '_FORM'
            ),
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
                //'_FORMSGROUP' => 'folder.gif'
                '_FORM' => 'leaf.gif'
            ),
            'gridFormat' => true,
            'showNodesWithObjType' => array(
                '_ROOT',
                //'_FORMSGROUP'
                '_FORM'

            ),

            'columns' => array(
                '>Name' => array()
            )
        );
        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }

    public function onAction_showForms($params)
    {
        if (isset($params['data']['params'])) {
            $node = $this->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array('@id', '=', $params['data']['params']['formsSourceId']))->run();
            $params['data']['params']['formsSource'] = $node['paramPathValue'];
            $this->result['actionDataForm'] = $params['data']['params'];
        }

        $this->result['actionDataForm']['fieldStyles'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['fieldStyles'], array('.fieldStyles.html'));
    }
}

?>
