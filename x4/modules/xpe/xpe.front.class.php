<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;


class xpeFront
    extends xModule
{
    public $person;
    public $_xpeRoles;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->_xpeRoles = $this->_commonObj->_xpeRoles;

    }

    public function userEngage()
    {

        if (empty($_COOKIE['xpeId'])) {
            $uId = person::generateId();
            setcookie("xpeId", $uId, time() + (20 * 365 * 24 * 60 * 60), "/", NULL);
        } else {
            $uId = $_COOKIE['xpeId'];
        }

        if (!$this->person) {
            $this->person = new person($uId, $this);
        }
    }


}

?>
