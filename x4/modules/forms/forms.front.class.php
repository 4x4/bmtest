<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class formsFront extends xModule
{
    public $fieldsetsData = array();

    public function __construct()
    {
        parent::__construct(__CLASS__);

            if (xConfig::get('GLOBAL', 'currentMode') == 'front') {
                $this->_tree->cacheState($this->_config['cacheTree']['tree']);
                    if ($this->_config['boostTree']) {
                        $this->_tree->startBooster();
                        $this->_tree->setTreeBoosted();
                    }
            }

        XNameSpaceHolder::addMethodsToNS('forms', array(
            'fieldset'
        ), $this);
    }

    private function returnFieldsets($key)
    {
        if (isset($this->fieldsetsData[$key])) {
            return $this->fieldsetsData[$key];
        }
    }

    public function fieldset($params, $context)
    {
        return $this->returnFieldsets($context['return']);
    }

    public function getFormData($form, $params)
    {
        if(empty($params['fieldStyles'])) {
            return false;
        }

        if(!isset($form['id'])) {
            $form = $this->_tree->getNodeInfo((int)$params['formsSourceId']);
        }

        if (!empty($form['id'])) {
            $this->loadModuleTemplate($params['fieldStyles']);
            $nodes = $this->_tree->selectStruct('*')->selectParams('*')->childs((int)$form['id'])->asTree()->run();
            $fieldsets = array();

            if(!is_numeric($form['basic'])) {
                $group = $form['basic'];
            }

            while (list($k, $node) = each($nodes->tree[$form['id']])) {
                if(isset($group)) {
                    $fieldsets[$group][$node['basic']] = $node;
                } else {
                    $fieldsets[$node['basic']] = $node;
                }

                while (list($key, $value) = each($nodes->tree[$k])) {
                    $nodes->tree[$k][$key]['params']['settings'] = unserialize($value['params']['settings']);

                        if($nodes->tree[$k][$key]['params']['settings']['required']) {
                            if($validationEngineClass = $this->greateValidationEngineClass($nodes->tree[$k][$key]['params']['settings']['restriction'])) {
                                $nodes->tree[$k][$key]['params']['settings']['validationEngineClass'] = $validationEngineClass;
                            }
                        }

                        if(($nodes->tree[$k][$key]['params']['type'] == 'SingleSelect' || $nodes->tree[$k][$key]['params']['type'] == 'MultipleSelect') && !empty($nodes->tree[$k][$key]['params']['settings']['value'])) {
                            $nodes->tree[$k][$key]['params']['options'] = $this->selectOptionsParse($nodes->tree[$k][$key]['params']['settings']['value']);

                        }

                    $nodes->tree[$k][$key]['formId']  = $form['id'];
                    $nodes->tree[$k][$key]['fieldsetId'] = $node['id'];
                    $nodes->tree[$k][$key]['attr'] = array(
                        'id'   => $form['id'].$node['id'].$value['id'],
                        'name' => ($form['params']['Async'] == '1') ? 'form'.$form['id'].'.'.$value['id'] : 'form['.$form['id'].']['.$value['id'].']'
                    );
                    $nodes->tree[$k][$key]['fieldHTML'] = $this->fieldParse($nodes->tree[$k][$key]);
                }

                if(isset($group)) {
                    $fieldsets[$group][$node['basic']]['fields'] = array_values($nodes->tree[$k]);
                } else {
                    $fieldsets[$node['basic']]['fields'] = array_values($nodes->tree[$k]);
                }
            }

            return $fieldsets;
        } else {
            return false;
        }
    }

    private function fieldParse($fieldData)
    {
        if(!empty($fieldData)) {
            $formKey = 'form'.$fieldData['formId'];
            $replaceData = array(
                'id' => $fieldData['id'],
                'formId' => $fieldData['formId'],
                'fieldsetId' => $fieldData['fieldsetId'],
                'Name' => $fieldData['params']['Name'],
                'description' => $fieldData['params']['settings']['description'],
                'required' => $fieldData['params']['settings']['required'],
                'validationEngineClass' => $fieldData['params']['settings']['validationEngineClass'],
                'params' => $fieldData['params'],
                'attr' => $fieldData['attr']
            );

            switch ($fieldData['params']['type']) {
                case 'PlainText':
                    $replaceData['text'] = $fieldData['params']['settings']['text'];
                    break;

                case 'SingleLineText':
                case 'Textarea':
                    $replaceData['placeholder'] = $fieldData['params']['settings']['placeholder'];

                    if(!empty($fieldData['params']['settings']['type'])) {
                        $replaceData['type'] = $fieldData['params']['settings']['type'];
                    }

                    if(isset($_SESSION[$formKey]['requestData'][$fieldData['id']])) {
                        $replaceData['defaultValue'] = $_SESSION[$formKey]['requestData'][$fieldData['id']];
                    } else {
                        $replaceData['defaultValue'] = $fieldData['params']['settings']['value'];
                    }
                    break;

                case 'SingleSelect':
                    if(isset($_SESSION[$formKey]['requestData'][$fieldData['id']])) {
                        foreach($fieldData['params']['options'] as $k => $option)
                        {
                            if($option['value'] == $_SESSION[$formKey]['requestData'][$fieldData['id']]) {
                                $replaceData['params']['options'][$k]['selected'] = 'selected';
                            }
                        }
                    }
                    break;

                case 'MultipleSelect':
                    $replaceData['attr']['name'] .= '[]';

                        if(isset($_SESSION[$formKey]['requestData'][$fieldData['id']])) {
                            foreach($fieldData['params']['options'] as $k => $option)
                            {
                                if(in_array($option['value'], $_SESSION[$formKey]['requestData'][$fieldData['id']])) {
                                    $replaceData['params']['options'][$k]['selected'] = 'selected';
                                }
                            }
                        }
                    break;

                case 'SingleCheckbox':
                    $replaceData['defaultValue'] = $fieldData['params']['settings']['value'];

                        if(isset($_SESSION[$formKey]['requestData'][$fieldData['id']])) {
                            $replaceData['checked'] = 1;
                        }
                    break;
            }

            $this->_TMS->addMassReplace($fieldData['params']['type'], $replaceData);
            return $this->_TMS->parseSection($fieldData['params']['type']);

        } else {
            return '';
        }
    }

    private function selectOptionsParse($optionsValue)
    {
        $optionsValue = explode("\n", $optionsValue);
        $options = array();

        foreach ($optionsValue as $option) {
            $optData = explode(':', $option);
            array_push($options, array(
                'value' => $optData[0],
                'name' => ($optData[1]) ? $optData[1] : $optData[0]
            ));
        }

        unset($optionsValue);

        return $options;
    }

    private function greateValidationEngineClass($restriction)
    {
        $validate = array('required');

        if(!empty($restriction)) {
            switch ($restriction) {
                case 'lettersonly':
                case 'onlyLetterSp':
                    array_push($validate,'custom[onlyLetterSp]');
                    break;

                case 'onlyNumberSp':
                    array_push($validate,'custom[onlyNumberSp]');
                    break;

                case 'alphanumeric':
                case 'onlyLetterNumber':
                    array_push($validate,'custom[onlyLetterNumber]');
                    break;

                case 'letterswithbasicpunc':
                    array_push($validate,'custom[lettersWithBasicPunc]');
                    break;

                case 'onlyLetterCyrillicSp':
                    array_push($validate,'custom[onlyLetterCyrillicSp]');
                    break;

                case 'phone':
                    array_push($validate,'custom[phone]');
                    break;

                case 'email':
                    array_push($validate,'custom[email]');
                    break;

                case 'url':
                    array_push($validate,'custom[url]');
                    break;
            }
        }

        return 'validate['.implode(',',$validate).']';
    }

    public function submitForm($params)
    {
        if(!empty($params['request']['requestData']['form'])) {
            if(!isset($params['request']['requestData']['form']['id']) || is_numeric($params['request']['requestData']['form']['id']) == false || $params['request']['requestData']['form']['id'] == false) {
                $form = $this->_tree->getNodeInfo((int)$params['params']['formsSourceId']);
            } else {
                $form = $this->_tree->getNodeInfo((int)$params['request']['requestData']['form']['id']);
            }

            $formKey = 'form'.$form['id'];

            if(!$form['params']['submitTemplate']) {
                return $this->_TMS->parseSection('failed_template');
            }
            if(empty($params['request']['requestData']['form'][$form['id']])) {
                return $this->_TMS->parseSection('failed_data');
            }
            if(!isset($_SESSION[$formKey])) {
                $_SESSION[$formKey] = array();
            }

            $this->loadModuleTemplate($form['params']['submitTemplate']);

            if(isset($params['request']['requestData']['form'][$form['id']]['captcha'])) {
                if(!$this->validateCaptcha($form, $params)) {
                    $_SESSION[$formKey]['error'] = $this->_TMS->parseSection('captcha');
                    $_SESSION[$formKey]['requestData'] = $params['request']['requestData']['form'][$form['id']];
                    $this->redirectToFormPage($params);
                } else {
                    unset($params['request']['requestData']['form'][$form['id']]['captcha']);
                }
            }

            $timeout = $this->timeout($form);

            if(gettype($timeout) == 'boolean' && $timeout == true) {
                if($this->sendMessage($form,$params['request']['requestData']['form'][$form['id']])) {
                    $this->_TMS->addReplace('success','message_after',$form['params']['message_after']);
                    unset($params['request']['requestData']['form'],$form);
                    return $this->_TMS->parseSection('success');
                } else {

                    $_SESSION[$formKey]['error'] = $this->_TMS->parseSection('failed');
                    $_SESSION[$formKey]['requestData'] = $params['request']['requestData']['form'][$form['id']];
                    $this->redirectToFormPage($params);
                }
            } else if(gettype($timeout) == 'double') {
                $params['error'][$formKey] = array('error_type' => 'timeout', 'time_left' => $timeout);
                $this->_TMS->addReplace('timeout', 'sec', $timeout);
                $_SESSION[$formKey]['error'] = $this->_TMS->parseSection('timeout');
                $_SESSION[$formKey]['requestData'] = $params['request']['requestData']['form'][$form['id']];
                $this->redirectToFormPage($params);
            }
        } else {
            $this->redirectToFormPage($params);
        }
    }

    private function constructMessage($form, $fieldsData)
    {
        $nodeChilds = $this->_tree->selectStruct('*')->selectParams('*')->childs((int)$form['id'])->asTree()->run();
        $message = array();
        $messageText = '';

        $this->messageDataTree = array(
            'Name' => $form['params']['Subject'],
            'groups' => array()
        );

        XRegistry::get('EVM')->fire($this->_moduleName . '.onConstructMessage', array('values'=>$fieldsData,'fields' =>$nodeChilds->tree[$form['id']]));

        while (list($k, $node) = each($nodeChilds->tree[$form['id']])) {
            $rows = '';
            $groupKey = $node['id'];

            if(!isset($this->messageDataTree['groups'][$groupKey])) {
                $this->messageDataTree['groups'][$groupKey] = array(
                    'Name' => $node['params']['Name'],
                    'fields' => array()
                );
            }

            while (list($key, $value) = each($nodeChilds->tree[$k])) {
                if (isset($fieldsData[$value['id']])) {
                    if(is_array($fieldsData[$value['id']])) {
                        $val = implode(', ', $fieldsData[$value['id']]);
                    } else if(gettype($fieldsData[$value['id']]) == 'boolean' && $fieldsData[$value['id']] == true) {
                        $value['params']['settings'] = unserialize($value['params']['settings']);
                        $val = $value['params']['settings']['value'];
                    } else {
                        $val = $fieldsData[$value['id']];
                    }

                    array_push($this->messageDataTree['groups'][$groupKey]['fields'], array(
                        'Name' => $value['params']['Name'],
                        'value' => $val
                    ));

                    $this->_TMS->addMassReplace('row', array(
                        'name' => $value['params']['Name'],
                        'value' => $val
                    ));
                    $rows .= $this->_TMS->parseSection('row');
                }
            }

            $this->_TMS->addMassReplace('group', array(
                'Name' => $node['params']['Name'],
                'rows' => $rows
            ));
            $messageText .= $this->_TMS->parseSection('group');
        }

        $this->_TMS->addMassReplace('message', array(
            'Name' => $form['params']['Name'],
            'charset' => $form['params']['Charset'],
            'subject' => $form['params']['Subject'],
            'text' => $messageText
        ));

        $this->_TMS->addMassReplace('saved_message', array(
            'Name' => $form['params']['Name'],
            'subject' => $form['params']['Subject'],
            'text' => $messageText
        ));

        $message['to_email'] = $this->_TMS->parseSection('message');
        $message['to_save'] = $this->_TMS->parseSection('saved_message');

        return $message;
    }

    public function sendMessage($form, $fieldsData)
    {
        $message = $this->constructMessage($form, $fieldsData);
        $emails = explode(',',$form['params']['Emails']);
        $send = array();

        if($form['params']['save_to_server']) {
            $this->saveMessageOnServer($form, $message['to_save']);
            $this->saveMessageDataInTree($form);
        }

        $m = xCore::incModuleFactory('Mail');
        $m->From(xConfig::get('GLOBAL', 'admin_email'));
        $m->Content_type('text/html');
        $m->Subject($form['params']['Subject']);
        $m->Body($message['to_email'], $form['params']['Charset']);
        $m->Priority(2);

        foreach($emails as $to) {
            $to = trim($to);
            $m->To($to);

            if($m->Send()) {
                $send[] = $to;
            }

            $m->sendto=array();
        }

        return (count($send) > 0) ? true : false;
    }

    private function saveMessageOnServer($form, $message)
    {
        XPDO::insertIN('forms_messages', array(
                'id' => 'null',
                'form_id' => (int)$form['id'],
                'Name' => $form['params']['Name'],
                'date' => date("d.m.Y H:i:s"),
                'message' => $message,
                'status' => 0,
                'archive' => 0)
        );
    }

    private function saveMessageDataInTree($form)
    {
        if(!empty($this->messageDataTree['groups'])) {
            //1th leavel
            if(empty($form['id'])) {
                $basic = '%SAMEASID%';
            } else if($form['id'] > 0) {
                $basic = $form['id'];

                if($messageGroup = $this->_commonObj->_treeMessages->selectStruct(array('id'))->where(array('@ancestor','=',1),array('@basic','=',$basic))->run()) {
                    $messageGroup = $messageGroup[0]['id'];
                } else {
                    $messageGroup = false;
                }
            } else {
                $basic = '%SAMEASID%';
            }

            $messageGroupData = array(
                'Name' => $form['params']['Name'],
                'description' => $form['params']['comment']
            );

            if($messageGroup == false || !isset($messageGroup)) {
                $messageGroupData['id'] = $this->_commonObj->_treeMessages->initTreeObj(1, $basic, '_MESSAGEGROUP', $messageGroupData);
            } else {
                $this->_commonObj->_treeMessages->reInitTreeObj((int)$messageGroup, $basic, $messageGroupData);
                $messageGroupData['id'] = $messageGroup;
            }

            if($messageGroupData['id']) {
                //2th leavel
                $messageId = $this->_commonObj->_treeMessages->initTreeObj($messageGroupData['id'], '%SAMEASID%', '_MESSAGE', array(
                    'Name' => $this->messageDataTree['Name']
                ));

                if($messageId) {
                    //3th leavel
                    while (list($key, $group) = each($this->messageDataTree['groups'])) {
                        $groupId = $this->_commonObj->_treeMessages->initTreeObj($messageId, '%SAMEASID%', '_MESSAGEFIELDSET', array(
                            'Name' => $group['Name']
                        ));

                        if($groupId) {
                            //4th leavel
                            while (list($k, $field) = each($group['fields'])) {
                                $fieldId = $this->_commonObj->_treeMessages->initTreeObj($groupId, '%SAMEASID%', '_MESSAGEFIELD', array(
                                    'Name' => $field['Name'],
                                    'value' => $field['value']
                                ));
                            }
                        }
                    }
                }
            }

            unset($this->messageDataTree);
        }
    }

    public function getCaptcha($form)
    {
        if (!empty($form['id'])) {
            $formKey = 'form'.$form['id'];
            $_SESSION['captcha_settings'] = explode('-', $form['params']['captcha_settings']);
            $this->_TMS->addMassReplace('captcha', array(
                'formId' => $form['id'],
                'length' => $_SESSION['captcha_settings'][0],
                'attr'   => array(
                    'id'   => 'captcha_form'.$form['id'],
                    'name' => ($form['params']['Async'] == '1') ? $formKey.'.captcha' : 'form['.$form['id'].'][captcha]'
                )
            ));
            return $this->_TMS->parseSection('captcha');
        } else {
            return '';
        }
    }

    public function validateCaptcha($form, $params)
    {
        $formKey = 'form'.$form['id'];

        if(!empty($form['id']) && !empty($_SESSION['captcha'][$formKey])) {
            $captcha = $_SESSION['captcha'][$formKey];
        } else {
            $captcha = $_SESSION['captcha'];
        }

        if(isset($params['request']['requestData']['form'][$form['id']]['captcha']) && $captcha == $params['request']['requestData']['form'][$form['id']]['captcha']) {
            $this->result['captcha'] = true;
            return true;
        } else if(isset($params['formData'][$formKey]['captcha']) && $captcha == $params['formData'][$formKey]['captcha']) {
            $this->result['captcha'] = true;
            return true;
        } else if(isset($params['captcha']) && $captcha == $params['captcha']) {
            $this->result['captcha'] = true;
            return true;
        } else {
            $this->result['captcha'] = false;
            return false;
        }
    }

    public function timeout($form)
    {
        $formKey = 'form'.$form['id'];
        $user_ip = getenv('REMOTE_ADDR');

        if(isset($_SESSION[$formKey]['timeout']) && array_key_exists($user_ip, $_SESSION[$formKey]['timeout']))
        {
            $mtime = (int) $form['params']['Timeout'];
            $curtime = ceil(Common::getmicrotime());
            $time_left = (int) $mtime - ($curtime - $_SESSION[$formKey]['timeout'][$user_ip]);

            if($time_left <= 0)
            {
                $_SESSION[$formKey]['timeout'][$user_ip] = ceil(Common::getmicrotime());
                return true;
            } else {
                return $time_left;
            }
        }
        else
        {
            $_SESSION[$formKey]['timeout'][$user_ip] = ceil(Common::getmicrotime());
            return true;
        }
    }



    private function redirectToFormPage($params)
    {
        if(isset($params['request']['pageLinkHost'])) {
            header('Location: ' . $params['request']['pageLinkHost']);
        } else {
            header('Location: '.HOST);
        }
        exit;
    }
}

?>
