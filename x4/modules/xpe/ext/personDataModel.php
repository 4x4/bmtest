<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;


class personDataModel
{
    public $groups;
    public $uid;
    public $fieldNameList;

    public function __construct($uid = null)
    {
        if ($uid) $this->uid = $uid;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    public function addGroup($group)
    {
        $this->groups[$group] = [];
    }

    public function addField($group, personDataField $field)
    {
        if (isset($this->groups[$group])) {
            $this->groups[$group][$field->name] = $field;

        }
    }

    public function getFieldFromList($group, $field)
    {
        return $this->groups[$group][$field];
    }

    public function setFieldValue($group, $field, $value)
    {
        if (isset($this->groups[$group][$field])) {
            $fieldSource = $this->groups[$group][$field];
            $fieldSource->setValue($value);
        }

    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getGroupItems($group)
    {
        if (isset($this->groups[$group])) {
            return $this->groups[$group];
        }
    }


}
