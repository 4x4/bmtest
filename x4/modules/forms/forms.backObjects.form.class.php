<?php

trait _FORM
{
    public function onCreate_FORM($params)
    {
        if ($templatesList = $this->getTemplatesList('forms')) {
            $this->result['data']['Template'] = XHTML::arrayToXoadSelectOptions($templatesList, false, true);
        }
        if ($submitTemplatesList = $this->getTemplatesList('submitForm')) {
            $this->result['data']['submitTemplate'] = XHTML::arrayToXoadSelectOptions($submitTemplatesList, false, true);
        }
    }

    public function onEdit_FORM($params)
    {
        $this->onCreate_FORM();
    }

    public function onSave_FORM($params)
    {
        if (!empty($params['data']['form']) && !empty($params['data']['form']['fieldsets'])) {
            $formData = array(
                'Name' => $params['data']['form']['Name'],
                'Author' => 'admin',
                'Description' => $params['data']['form']['comment'],
                'Disable' => ($params['data']['form']['Disable']) ? '1' : '0',
                //$params['data']['form']['heading']
                //$params['data']['form']['horizontalAlignment']
                'Subject' => $params['data']['form']['subject'],
                'Template' => $params['data']['form']['Template'],
                'submitTemplate' => $params['data']['form']['submitTemplate'],
                'Emails' => $params['data']['form']['email'],
                'Charset' => $params['data']['form']['charset'],
                'save_to_server' => ($params['data']['form']['save_to_server']) ? '1' : '0',
                'use_captcha' => ($params['data']['form']['use_captcha']) ? '1' : '0',
                'captcha_settings' => $params['data']['form']['captcha_settings'],
                'Async' => ($params['data']['form']['async']) ? '1' : '0',
                'Timeout' => $params['data']['form']['timeout'],
                'message_after' => $params['data']['form']['message_after']
            );

            if(!$params['data']['form']['basic'] || $params['data']['form']['basic'] == 'null') {
                $basic = '%SAMEASID%';
            } else if(is_numeric($params['data']['form']['basic']) || is_string($params['data']['form']['basic'])) {
                $basic = trim($params['data']['form']['basic']);
            } else {
                $basic = '%SAMEASID%';
            }

            $formData['id'] = $this->_tree->initTreeObj(1, $basic, '_FORM', $formData);

            if ($formData['id'] && !empty($params['data']['form']['fieldsets'])) {
                while (list($k, $fieldset) = each($params['data']['form']['fieldsets'])) {
                    $fieldsetData = array(
                        'Name' => $fieldset['Name'],
                        'Description' => '',  //$fieldset['Description']
                        'Disable' => ''       //$fieldset['Disable']
                    );

                    if(!$fieldset['basic'] || $fieldset['basic'] == 'null') {
                        $basic = '%SAMEASID%';
                    } else if(is_numeric($fieldset['basic']) || is_string($fieldset['basic'])) {
                        $basic = trim($fieldset['basic']);
                    } else {
                        $basic = '%SAMEASID%';
                    }

                    $fieldsetData['id'] = $this->_tree->initTreeObj($formData['id'], $basic, '_FIELDSET', $fieldsetData);

                    if ($fieldsetData['id'] && !empty($fieldset['fields'])) {
                        while (list($i, $field) = each($fieldset['fields'])) {
                            $fieldData = $field;
                            $fieldDataSettings = $field['settings'];
                            unset($fieldData['id'], $fieldData['ancestor'], $fieldData['sequence'], $fieldData['status']);
                            $fieldData['settings'] = serialize($fieldDataSettings);
                            $this->_tree->initTreeObj($fieldsetData['id'], '%SAMEASID%', '_FIELD', $fieldData);
                        }
                    }
                }

                $this->result['save'] = (int)$formData['id'];
                return true;

            }
        }

        $this->result['save'] = false;
        return false;
    }

    public function onSaveEdited_FORM($params)
    {
        if ($params['data']['form']['id'] && !empty($params['data']['form']['fieldsets'])) {
            $new_fieldsets = array();
            $formData = array(
                'Name' => $params['data']['form']['Name'],
                'Author' => 'admin',
                'Description' => $params['data']['form']['comment'],
                'Disable' => ($params['data']['form']['Disable']) ? '1' : '0',
                //$params['data']['form']['heading']
                //$params['data']['form']['horizontalAlignment']
                'Subject' => $params['data']['form']['subject'],
                'Template' => $params['data']['form']['Template'],
                'submitTemplate' => $params['data']['form']['submitTemplate'],
                'Emails' => $params['data']['form']['email'],
                'Charset' => $params['data']['form']['charset'],
                'save_to_server' => ($params['data']['form']['save_to_server']) ? '1' : '0',
                'use_captcha' => ($params['data']['form']['use_captcha']) ? '1' : '0',
                'captcha_settings' => $params['data']['form']['captcha_settings'],
                'Async' => ($params['data']['form']['async']) ? '1' : '0',
                'Timeout' => $params['data']['form']['timeout'],
                'message_after' => $params['data']['form']['message_after']
            );

            if (!$params['data']['form']['basic'] || $params['data']['form']['basic'] == 'null') {
                $params['data']['form']['basic'] = '%SAME%';
            }

            $this->_tree->reInitTreeObj((int)$params['data']['form']['id'], $params['data']['form']['basic'], $formData);

            if ($params['data']['form']['id'] && !empty($params['data']['form']['fieldsets'])) {
                while (list($k, $fieldset) = each($params['data']['form']['fieldsets'])) {
                    $fieldsetData = array(
                        'Name' => $fieldset['Name'],
                        'Description' => '',  //$fieldset['Description']
                        'Disable' => ''       //$fieldset['Disable']
                    );

                    if ($fieldset['id'] > 0) {
                        if (!$fieldset['basic'] || $fieldset['basic'] == 'null') {
                            $fieldset['basic'] = '%SAME%';
                        }

                        $this->_tree->reInitTreeObj($fieldset['id'], $fieldset['basic'], $fieldsetData);
                    } else {
                        $new_fieldsets[$k] = $fieldset;
                    }

                    if ($fieldset['id'] > 0 && !empty($fieldset['fields'])) {
                        while (list($i, $field) = each($fieldset['fields'])) {
                            if ($field['id'] > 0) {
                                if (!$field['basic'] || $field['basic'] == 'null') {
                                    $field['basic'] = '%SAME%';
                                }

                                if ($field['status'] == 'D') {
                                    $this->_tree->childs($fieldset['id'], 3)->where(array('@id', '=', $field['id']))->delete()->run();
                                } else {
                                    //status U - Update OR status null
                                    $fieldData = $field;
                                    $fieldDataSettings = $field['settings'];
                                    $fieldData['settings'] = serialize($fieldDataSettings);
                                    $sequence = ($fieldData['sequence']) ? $fieldData['sequence'] : 0;
                                    unset($fieldData['id'], $fieldData['basic'], $fieldData['sequence'], $fieldData['ancestor'], $fieldData['status']);
                                    $this->_tree->reInitTreeObj($field['id'], $field['basic'], $fieldData);
                                    $this->_tree->setStructData($field['id'], 'rate', $sequence);

                                    if ($field['ancestor'] != $fieldset['id']) {
                                        $this->_tree->setStructData($field['id'], 'x3', $fieldset['id']);
                                    }
                                }
                            } else {
                                $fieldData = $field;
                                $fieldDataSettings = $field['settings'];
                                $fieldData['settings'] = serialize($fieldDataSettings);
                                $sequence = ($fieldData['sequence']) ? $fieldData['sequence'] : 0;
                                unset($fieldData['id'], $fieldData['ancestor'], $fieldData['sequence'], $fieldData['status']);
                                $field['id'] = $this->_tree->initTreeObj($fieldset['id'], '%SAMEASID%', '_FIELD', $fieldData);

                                if ($field['id']) {
                                    $this->_tree->setStructData($field['id'], 'rate', $sequence);
                                }
                            }
                        }
                    }
                }
            }

            if ($params['data']['form']['id'] && !empty($new_fieldsets)) {
                $this->_tree->childs($params['data']['form']['id'])->delete()->run();

                while (list($k, $fieldset) = each($new_fieldsets)) {
                    $fieldsetData = array(
                        'Name' => $fieldset['Name'],
                        'Description' => '',  //$fieldset['Description']
                        'Disable' => ''       //$fieldset['Disable']
                    );

                    if(!$fieldset['basic'] || $fieldset['basic'] == 'null') {
                        $basic = '%SAMEASID%';
                    } else if(is_numeric($fieldset['basic']) || is_string($fieldset['basic'])) {
                        $basic = trim($fieldset['basic']);
                    } else {
                        $basic = '%SAMEASID%';
                    }

                    $fieldsetData['id'] = $this->_tree->initTreeObj($params['data']['form']['id'], $basic, '_FIELDSET', $fieldsetData);

                    if ($fieldsetData['id'] && !empty($fieldset['fields'])) {
                        while (list($i, $field) = each($fieldset['fields'])) {
                            $fieldData = $field;
                            $fieldDataSettings = $field['settings'];
                            unset($fieldData['id'], $fieldData['ancestor'], $fieldData['sequence'], $fieldData['status']);
                            $fieldData['settings'] = serialize($fieldDataSettings);
                            $this->_tree->initTreeObj($fieldsetData['id'], '%SAMEASID%', '_FIELD', $fieldData);
                        }
                    }
                }
            }

            $this->result['save'] = (int)$params['data']['form']['id'];
            return true;
        }

        $this->result['save'] = false;
        return false;
    }
}

?>
