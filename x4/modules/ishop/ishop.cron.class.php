<?php

class ishopCron extends ishopBack
{

    public function __construct()
    {
        parent::__construct();
    }


    public function getCurrentCoursesNBRB($params)
    {
        $this->getCurrentCourses($params);
        $adm = new AdminPanel();
        $adm->clearCache(true);
        return true;
    }

}
