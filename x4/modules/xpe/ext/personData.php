<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;


class personData
{
    public $personModel;
    public $storage;

    public function __construct()
    {
        $this->personModel = new personDataModel();
    }

    public function setStorage($storage)
    {
        $this->storage = $storage;
    }


    public function importField($data)
    {
        $fieldName = $data['fieldType'];
        $field = new $fieldName();
        $field->importField($data['data']);
        return $field;
    }

    public function importModelData($data)
    {

        $groups = $this->personModel->getGroups();

        foreach ($groups as $groupName => $group) {

            foreach ($group as $fieldName => $field) {

                $this->personModel->setFieldValue($groupName, $fieldName, $data[$field->storageParamName]);

            }
        }
    }


    public function load($uid = null)
    {
        $arrayedItem = $this->storage->getItem($uid);

        // объект присутствует в storage


        if (!empty($arrayedItem)) {
            $this->importModelData($arrayedItem);

        } else {
            // инициируем объект в случае его отсутствия в storage
            $this->personModel->uid = $uid;
            $this->initiatePersonInStorage();
            $this->importModelData(array());
        }
    }


    public function exportModelData()
    {
        if (!empty($this->personModel->groups)) {

            foreach ($this->personModel->groups as $groupKey => $group) {

                $exportGroup[$groupKey] = array();

                foreach ($group as $value) {

                    $exportGroup[$groupKey][] = $value->exportField();

                }

            }
        }

        return array('data' => $exportGroup, 'uid' => $this->personModel->uid);
    }


    public function initiatePersonInStorage()
    {
        $this->storage->setItem($this->personModel->uid, array());
    }


}
