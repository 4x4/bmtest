<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;


class person
{
    public $personData;
    public $affectorsApplied = array();
    public $uId = null;
    public $storageDriver;
    public $logAffectedPath;
    public $storageDriverInitialized=false;
    private $loadedAffectors = array();

    public function __construct($uId, $instance)
    {
        $this->xpe = $instance;
        $this->uId = $uId;
		$this->logAffectedPath=PATH_ .'media/logs/';	
        $this->personData = new personData();
        if(!$this->initateStorageConnection())
        {
            $this->storageDriverInitialized=false;
            return;

        }else{
            $this->storageDriverInitialized=true;
        }

		$this->logAffectedRoles=false;
        $this->loadPersonScheme();
        $this->loadPersonData();
        $data = $this->personData->exportModelData();
        $this->serializePerson();
        $this->getRolesLogic();
        $this->runAffectors();

    }

    public static function loadAffector($affector)
    {
        static $loadedAffectors=array();

        if (empty($loadedAffectors[$affector])) {
            $affectorFile = xConfig::get('PATH', 'MODULES') . 'xpe/ext/affectors/' . $affector . '/' . $affector . '.php';
            require($affectorFile);
            return $loadedAffectors[$affector] = new $affector();

        } else {

            return $loadedAffectors[$affector];
        }
    }

    public function runAffectors()
    {

        if (!empty($this->affectorsApplied)) {
            foreach ($this->affectorsApplied as $affector) {

                $affectorInstance = self::loadAffector($affector['params']['affector']);
                $affectorInstance->setOptions($affector['params']);
                $affectorInstance->run();
            }

        }

    }

    public function serializePerson($isReturn=false)
    {
        $data = $this->personData->exportModelData();
        $output = array();

        foreach ($data['data'] as $groupKey => $element) {
            foreach ($element as $field) {

                $output[$groupKey . '.' . $field['data']['name']] = $field['data']['value'];
            }
        }

        if($isReturn){
            return $output;
        }

        file_put_contents($this->uId . '.json', json_encode($output));
    }

    public static function generateId()
    {
        return uniqid('xpeId');

    }

    private function initateStorageConnection()
    {
        $storage = personStorage::getInstance();
		if(!empty($storage))
		{
			$this->storageDriver = $storage->factorStorage($this->xpe->_config['defaultStorage'], $this->xpe->_config['defaultStorageConfig']);
			$this->personData->setStorage($this->storageDriver);
			return true;
		}else{
			return false;
		}

    }

    public function loadPersonData()
    {
        $this->personData->load($this->uId);
    }

    public function loadPersonScheme()
    {
        $this->treeLocal = $this->xpe->_tree->selectStruct('*')->selectParams('*')->childs(1, 2)->asTree()->run();

        $groups = $this->treeLocal->fetchArray(1);

        if (!empty($groups)) {

            foreach ($groups as $group) {
                $this->personData->personModel->addGroup($group['basic']);

                $items = $this->treeLocal->fetchArray($group['id']);


                foreach ($items as $item) {
                    $itemType = $item['params']['Type'];
                    $field = new $itemType($item['basic'], $item['params']['Alias']);

                    if ($item['params']['Options']) {
                        $field->setOptions($item['params']['Options'],$item['params']['OptionsData']);

                        $field->setStorageLink($this->storageDriver);
                    }

                    if (!empty($item['params']['StorageParamName'])) {
                        $field->setStorageParamName($item['params']['StorageParamName']);

                    } else {

                        $field->setStorageParamName($group['basic'] . '.' . $item['basic']);
                    }


                    $this->personData->personModel->addField($group['basic'], $field);
                }

            }

        }

    }

    private function getPersonField($field)
    {
        $fieldGroup = explode('.', $field);
        return $this->personData->personModel->getFieldFromList($fieldGroup[0], $fieldGroup[1]);
    }

    protected function operator_greater($rule)
    {

        $value = $this->getPersonField($rule['field']);

        if ($value->value > $rule['value']) {
            return true;
        }

        return false;
    }

    protected function operator_less($rule)
    {

        $value = $this->getPersonField($rule['field']);

        if ($value->value < $rule['value']) {
            return true;
        }

        return false;
    }


    protected function operator_contains($rule)
    {

        $value = $this->getPersonField($rule['field']);

        if (strstr($value->value, $rule['value']) !== false) {
            return true;
        }

        return false;
    }

    protected function operator_not_equal($rule)
    {

        $value = $this->getPersonField($rule['field']);

        if ($value->value != $rule['value']) {
            return true;
        }

        return false;
    }

    protected function operator_equal($rule)
    {

        $value = $this->getPersonField($rule['field']);

        if ($value->value == $rule['value']) {
            return true;
        }

        return false;
    }

    protected function condition($rule)
    {

        $operator = 'operator' . '_' . $rule['operator'];

        if (method_exists($this, $operator)) {
            return $this->$operator($rule);
        }

        return false;
    }

    public function conditionChecker($conditions)
    {

        foreach ($conditions['rules'] as $rule) {
            if (!empty($rule['rules'])) {
                $results[] = $this->conditionChecker($rule);

            } else {

                $results[] = $this->condition($rule);
            }
        }


        if ($conditions['condition'] == 'AND') {

            $logic = 1;

            foreach ($results as $res) {
                $logic = $res & $logic;
            }

        } else {

            $logic = 0;

            foreach ($results as $res) {
                $logic = $res | $logic;
            }

        }

        return $logic;

    }

    public function logAffectedRoles($role,$affectors)
    {

        XPDO::insertIN('xpe_statistics', array(
            'uid'=>$this->uId,
            'role_id'=>$role['id'],
            'time'=>time()

        ));

		if($this->logAffectedRoles){
		   $data=date("Y-m-d H:i:s") ."\r\n".print_r($role,true).print_r($affectors,true)."\r\n";
		   $path=$this->logAffectedPath.$this->uId.'.log';
		   file_put_contents($path,$data,FILE_APPEND | 2);
	   }
    }

    public function getRolesLogic()
    {

        $rolesLocal = $this->xpe->_xpeRoles->selectStruct('*')->selectParams('*')->childs(1, 3)->asTree()->run();
        $campaigns = $rolesLocal->fetchArray(1);

        if (!empty($campaigns)) {
            foreach ($campaigns as $rld) {

                $roles = $rolesLocal->fetchArray($rld['id']);

                foreach ($roles as $role) {
                    if (!empty($role['params']['conditions'])) {
                        $conditions = json_decode($role['params']['conditions'], true);
                        if ($this->conditionChecker($conditions)) {

                            $affectors = $rolesLocal->fetchArray($role['id']);

                            $this->logAffectedRoles($role, $affectors);

                            if (!empty($affectors)) {
                                $this->affectorsApplied = $affectors + $this->affectorsApplied;
                            }

                        }
                    }
                }

            }

        }
    }

}
