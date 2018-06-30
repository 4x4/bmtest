<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

interface personDataFieldInterface
{
    public function exportField();

    public function importField($data);
}

abstract class personDataField
{
    public $name;
    public $alias;
    public $value;
    public $options;
    public $storageParamName;
    public $storageLink;

    public function __construct($name = null, $alias = null, $value = null)
    {

        $this->name = $name;
        $this->alias = $alias;
        $this->value = $value;

    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function setStorageLink($storageLink, $name = null)
    {
        $this->storageLink = $storageLink;
        $this->storageParamName = $name;
    }

    public function setStorageParamName($name)
    {
        $this->storageParamName = $name;
    }

    public function setValue($value = null)
    {
        $this->value = $value;
    }


}


