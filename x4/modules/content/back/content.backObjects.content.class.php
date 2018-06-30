<?php

trait _CONTENT
{
    public function onCreate_CONTENT($params)
    {
        $this->result['data']['Template'] = $this->getTemplateListSubs();
    }

    public function onSaveEdited_CONTENT($params)
    {
        $data = $params['data'];

        if (!$basic = $data['basic']) {
            $basic = "%SAME%";
        } else {
            unset($data['basic']);
        }

        $this->_tree->reInitTreeObj($params['id'], $basic, $data);

        $dynamicFieldsForm = $params['dynamicFieldsForm'];

        $this->_tree->delete()->childs($params['id'])->run();

        if ($dynamicFieldsForm['__root']) {
            foreach ($dynamicFieldsForm['__root'] as $key => $val) {
                $this->_tree->initTreeObj($params['id'], $key, '_FIELD', array(
                    '__value' => $val
                ));
            }
        }

        unset($dynamicFieldsForm['__root']);

        if (isset($dynamicFieldsForm)) {
            foreach ($dynamicFieldsForm as $key => $param) {
                $k = 0;
                foreach ($param as $pkey => $pnode) {

                    $pExploded = explode('__', $pkey);
                    $position[$pExploded[1]][] = array($pExploded[0], $pnode);
                }

                $param = array();
                $k = 0;

                foreach ($position as $posNum) {
                    $k++;

                    foreach ($posNum as $posData) {
                        $param[$posData[0] . '__' . $k] = $posData[1];
                    }

                }

                $this->_tree->initTreeObj($params['id'], $key, '_FIELD', $param);
            }

        }
        return new okResult('saved');

    }


    public function onEdit_CONTENT($params)
    {

        $this->result['content'] = $this->_tree->getNodeInfo($params['id']);

        $this->result['content']['params']['tpl'] = $this->result['content']['params']['Template'];

        if ($fields = $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->childs($params['id'], 2)->format('valval', 'basic', 'params')->run()) {
            foreach ($fields as $key => $field) {

                if (isset($field['__value'])) {
                    $this->result['staticBlocks']['__root.' . $key] = $field['__value'];

                } else {

                    $max = 0;
                    foreach ($field as $groupItem => $value) {
                        $enum = explode('__', $groupItem);
                        if ($enum[1] > $max) $max = $enum[1];

                        $this->result['dynamicBlocks'][$key . '.' . $groupItem] = $value;
                    }

                    $this->result['replics'][$key] = $max;

                }

            }

        }
        if ($templatesList = $this->getTemplatesList($this->_moduleName, false, true)) {
            $this->result['content']['params']['Template'] = XHTML::arrayToXoadSelectOptions($templatesList, $this->result['content']['params']['Template']);
        }

    }


    public function onSave_CONTENT($params)
    {

        $data = $params['data'];

        if (!$basic = $data['basic']) {
            $basic = "%SAMEASID%";
        } else {
            unset($data['basic']);
        }


        if ($id = $this->_tree->initTreeObj($data['ancestorId'], $basic, '_CONTENT', $data)) {

            $dynamicFieldsForm = $params['dynamicFieldsForm'];

            if ($dynamicFieldsForm['__root']) {
                foreach ($dynamicFieldsForm['__root'] as $key => $val) {
                    $this->_tree->initTreeObj($id, $key, '_FIELD', array(
                        '__value' => $val
                    ));
                }
            }

            unset($dynamicFieldsForm['__root']);

            foreach ($dynamicFieldsForm as $key => $param) {

                $k = 0;
                foreach ($param as $pkey => $pnode) {

                    $pExploded = explode('__', $pkey);
                    $position[$pExploded[1]][] = array($pExploded[0], $pnode);
                }

                $param = array();
                $k = 0;

                foreach ($position as $posNum) {
                    $k++;

                    foreach ($posNum as $posData) {
                        $param[$posData[0] . '__' . $k] = $posData[1];
                    }

                }

                $this->_tree->initTreeObj($params['id'], $key, '_FIELD', $param);
            }

            return new okResult('saved');
        }

    }

}

?>