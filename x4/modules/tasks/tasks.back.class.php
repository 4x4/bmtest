<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;
use X4\Classes\TableJsonSource;

require(xConfig::get('PATH', 'MODULES') . 'tasks/tasks.backObjects.task.class.php');

class tasksBack
    extends xModuleBack
{
    use _TASK;

    public function __construct()
    {
        parent::__construct(__CLASS__);

    }

    public function tasksTable($params)
    {

        $source = Common::classesFactory('TableJsonSource', array());

        $params['onPage'] = 50;


        $opt = array
        (
            'onPage' => $params['onPage'],
            'table' => 'tasks',
            'order' => array
            (
                'id',
                'asc'
            ),
            'idAsNumerator' => 'id',
            'columns' => array
            (
                'id' => array(),
                'task_name' => array(),
                'task_method' => array(),
                'period' => array(),
                'last_launch' => array
                (
                    'onAttribute' => TableJsonSource::$fromTimeStamp,
                    'onAttributeParams' => array('format' => 'd.m.y H:i:s')
                )
            )
        );

        $source->setOptions($opt);


        if (!$params['page']) $params['page'] = 1;


        $this->result = $source->createView($params['id'], $params['page']);

    }

    public function deleteTasks($params)
    {

        if (is_array($params['id'])) {
            $id = implode($params['id'], "','");
            $w = 'id in (\'' . $id . '\')';
        } else {
            $w = 'id="' . $params['id'] . '"';
        }

        $query = 'delete from tasks where ' . $w;

        if ($this->_PDO->query($query)) {
            $this->result['deleted'] = true;
        }
    }


}


?>