<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

trait _TASK
{
    public function onCreate_TASK($params)
    {

        $methods = $this->_commonObj->getAllCronMethods();
        if (!empty($methods)) {
            $methods = array_combine($methods, $methods);
            $this->result['data']['task_method'] = XHTML::arrayToXoadSelectOptions($methods, null, true);
        }


    }

    public function onSave_TASK($params)
    {

        $data = $params['data'];
        $data['active'] = 1;
        $data['id'] = 'NULL';
        $data['last_launch']=time();

        if (XPDO::insertIN('tasks', $data)) {
            return new okResult();
        } else {
            return new badResult('TASK-do-not-written');
        }
    }

    public function onEdit_TASK($params)
    {
        if ($data = XPDO::selectIN('*', 'tasks', (int)$params['id'])) {

            $data = $data[0];
            $methods = $this->_commonObj->getAllCronMethods();
            $methods = array_combine($methods, $methods);
            $this->result['data'] = $data;

            $this->result['data']['task_method'] = XHTML::arrayToXoadSelectOptions($methods, $data['task_method'], true);

        }
    }

    public function onSaveEdited_TASK($params)
    {

        $data = $params['data'];
        $data['active'] = 1;
        $data['id'] = $params['id'];


        if (XPDO::updateIN('tasks', (int)$data['id'], $data)) {
            return new okResult();
        } else {
            return new badResult('TASK-do-not-written');
        }

    }

}
