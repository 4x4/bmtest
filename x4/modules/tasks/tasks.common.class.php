<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;


class tasksCommon
    extends xModuleCommon implements xCommonInterface
{


    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function defineFrontActions()
    {
    }


    public function getCurrentTasks()
    {

        $tasks = XPDO::selectIN('*', 'tasks', ' active=1 ');
        if (!empty($tasks)) {
            foreach ($tasks as $task) {
                $period = $task['period'] + $task['last_launch'];
                $isLaunch = ($period) < time();

                if ($isLaunch) {
                    $taskData = explode(':', $task['task_method']);

                    //is plugin
                    if(strstr($task['task_method'],'.')){

                        $instance = xCore::pluginFactory($taskData[0] . '.cron');
                    }else{
                        $instance = xCore::moduleFactory($taskData[0] . '.cron');
                    }

                    $call = new \ReflectionMethod($instance, $taskData[1]);

                    $params = json_decode($task['task_params'], true);

                    $result = $call->invokeArgs($instance, array($params));


                    if ($result) {

                        XPDO::updateIN('tasks', $task['id'], array('last_launch' => time()));

                    }



                }
            }
        }
    }


    public function getClassMethods($class, $moduleName,$plug='')
    {
        $f = new ReflectionClass($class);
        $methods = array();
        foreach ($f->getMethods() as $m) {
            $mClass=$m->class;

            if($plug){
                $mClass=$plug.'.'.$m->class;
            }


            if ($mClass == $moduleName) {
                if ($m->name != '__construct' && strpos($m->name,'__') !== 0) {
                    $methods[] = $m->name;
                }

            }

        }
        return $methods;

    }

    public function getAllCronMethods()
    {

        $modules = xCore::discoverModules();
        $plugins=array();

        $fullMethodsList = array();

        if (!empty($modules)) {

            foreach ($modules as $module) {
                try {
                    $obj = xCore::moduleFactory($module['name'] . '.cron');

                } catch (Exception $e) {
                }

                if (!empty($obj)) {
                    if ($methods = $this->getClassMethods($obj, $module['name'] . 'Cron')) {
                        foreach ($methods as $method) {
                            if (!strstr($method, '__construct')) {

                                $fullMethodsList[] = $module['name'] . ':' . $method;

                            }
                        }

                    }
                }

                if(!empty($module['plugins']))
                {
                    $plugins=$plugins+$module['plugins'];
                }
            }


        }



        if (!empty($plugins)) {

            foreach ($plugins as $plugin) {
                try {
                    $obj = xCore::pluginFactory($plugin['name'] . '.cron');

                } catch (Exception $e) {
                }

                if (!empty($obj)) {
                    $exploded=explode('.',$plugin['name']);

                    if ($methods = $this->getClassMethods($obj, $plugin['name'] . 'Cron',$exploded[0])) {
                        foreach ($methods as $method) {
                            if (!strstr($method, '__construct')) {
                                $fullMethodsList[] = $plugin['name'] . ':' . $method;

                            }
                        }

                    }
                }

            }


        }

        return $fullMethodsList;

    }


}
