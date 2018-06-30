<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

trait _XPEROLE
{

    public function checkDuplicateRoles($params)
    {

        $item=$this->_commonObj->_xpeRoles->selectStruct('*')->where(array('@obj_type','=','_XPEROLE'),array('Alias','=',$params['field']))->singleResult()->run();

        if(!empty($item)&&($params['currentId']!=$item['id'])) {
            $this->result['isDuplicate'] = true;
        }else{
            $this->result['isDuplicate'] = false;
        }

    }

    public function fieldsBuilder($item, $context)
    {
        $ancestor = $this->treeGroups->getNode($item['ancestor']);


        if ($item['obj_type'] == '_SCHEMEITEM') {

            $this->comparingItems[] = array('id' => $item['id'],
                'field' => $ancestor['basic'] . '.' . $item['basic'],
                'label' => array
                (
                    'ru' => $item['params']['Alias']
                ),
                'value_separator' => ',',
                'type' => $item['params']['ValueType'],
                'optgroup' => $ancestor['basic'],
                'validation' => array(
                    'allow_empty_value' => false
                )
            );
        } else {

            $this->optgroups[$item['basic']] = array('ru' => $item['params']['Alias']);

        }
    }


    public function onSave_XPEROLE($params)
    {

        $id = $this->_commonObj->_xpeRoles->initTreeObj((int)$params['parent'], '%SAMEASID%', '_XPEROLE', $params['data']);

        if (!empty($params['affectors'])) {

            foreach ($params['affectors'] as $item) {
                if (!empty($item)) {
                    $this->_commonObj->_xpeRoles->initTreeObj($id, '%SAMEASID%', '_AFFECTOR', $item['params']);
                }

            }
        }

        $this->pushMessage('items-saved');
        return new okResult();
    }


    public function onCreate_XPEROLE($params)
    {

        $this->getInitialSchemeData();

    }

    public function onEdit_XPEROLE($params)
    {


        $data = $this->_commonObj->_xpeRoles->getNodeInfo($params['id']);

        $this->result['data'] = $data['params'];

        $childs = $this->_commonObj->_xpeRoles->selectStruct('*')->selectParams('*')->format('keyval', 'id')->childs($params['id'], 1)->run();

        if (!empty($childs)) {
            foreach ($childs as $child) {
                $one = array();
                $one['params'] = $child['params'];
                $this->result['affectors'][] = $one;
            }
        }

        $this->getInitialSchemeData();

    }

    private function getInitialSchemeData()
    {

        $this->treeGroups = $this->_tree->selectStruct('*')->selectParams('*')->childs(1, 2)->asTree()->run();

        $this->treeGroups->recursiveStep(1, $this, 'fieldsBuilder');

        $this->result['items'] = $this->comparingItems;

        $this->result['optgroups'] = $this->optgroups;


    }

    public function onSaveEdited_XPEROLE($params)
    {

        $this->_commonObj->_xpeRoles->reInitTreeObj($params['id'], '%SAME%', $params['data']);

        if (!empty($params['affectors'])) {
            $this->_commonObj->_xpeRoles->delete()->childs($params['id'])->run();

            foreach ($params['affectors'] as $item) {
                if (!empty($item)) {
                    $this->_commonObj->_xpeRoles->initTreeObj($params['id'], '%SAMEASID%', '_AFFECTOR', $item['params']);
                }

            }
        }

        $this->pushMessage('items-saved');
        return new okResult();

    }
}

?>