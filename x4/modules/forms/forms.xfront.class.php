<?php

class formsXfront extends formsFront
{
    public function sendFormData($params)
    {
        $formKey = 'form'.$params['formId'];

        if(isset($params['formId']) && (int)$params['formId'] > 0 && !empty($params['formData'][$formKey])) {
            $form = $this->_tree->getNodeInfo((int)$params['formId']);

            if(!$form['params']['submitTemplate']) {
                $this->result['error'] = 2;
                return false;
            }

            $this->loadModuleTemplate($form['params']['submitTemplate']);

            if(isset($params['formData'][$formKey]['captcha'])) {
                if(!$this->validateCaptcha($form, $params)) {
                    $this->result['error'] = $this->_TMS->parseSection('captcha');
                    return false;
                } else {
                    unset($params['formData'][$formKey]['captcha']);
                }
            }

            $timeout = $this->timeout($form);

            if(gettype($timeout) == 'boolean' && $timeout == true) {
                if($this->sendMessage($form,$params['formData'][$formKey])) {
                    $this->result['success'] = $this->_TMS->addReplace('success','message_after',$form['params']['message_after']);
                    unset($params,$form);
                    return true;
                } else {
                    unset($params,$form);
                    $this->result['error'] = $this->_TMS->parseSection('failed');
                    return false;
                }
            } else if(gettype($timeout) == 'double') {
                $this->_TMS->addReplace('timeout', 'sec', $timeout);
                $this->result['error'] = $this->_TMS->parseSection('timeout');
                return false;
            }

        } else {
            $this->result['error'] = 1;
            return false;
        }
    }

    public function checkCaptcha($params)
    {
        if(empty($params['form']) && empty($params['params'])) {
            $this->result['captcha'] = false;
            return false;
        } else {
            return $this->validateCaptcha($params['form'],$params['params']);
        }
    }
}

?>
