<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;

class showFormsAction extends xAction
{
    public function run($params)
    {
        if(!empty($params['params'])) {
            $slotFormParams = $params['params'];
            $form = $this->_tree->getNodeInfo((int)$slotFormParams['formsSourceId']);

                if (isset($form) && !$form['params']['Disable']) {
                    if(!$form['params']['Template']) {return false;}

                    $this->loadModuleTemplate($form['params']['Template']);
                    $this->fieldsetsData = $this->getFormData($form, $slotFormParams);

                        if($this->fieldsetsData != false) {
                            $formKey = 'form'.$form['id'];
                            $form['action'] = $params['request']['pageLinkHost'].'/~submitForm';

                            $this->_TMS->addReplace('forms', 'object', $form);

                            if(isset($_SESSION[$formKey]['error'])) {
                                $this->_TMS->addReplace('forms', 'error', $_SESSION[$formKey]['error']);
                                unset($_SESSION[$formKey]['error'], $_SESSION[$formKey]['requestData']);
                            }

                            if($form['params']['use_captcha']) {
                                $captcha = $this->getCaptcha($form);
                                $this->_TMS->addReplace('forms', 'captcha', $captcha);
                            }

                            return $this->_TMS->parseSection('forms');
                        } else {
                          return '';
                        }
                }
        }
    }
}

?>
