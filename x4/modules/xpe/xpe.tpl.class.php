<?php

class xpeTpl
    extends xTpl
    implements xModuleTpl
{
    public function __construct($module)
    {
        parent::__construct($module);
    }


    public function getCurrentPerson($params)
    {		
        $data=$this->person->personData->exportModelData();
        return $data['data'];
    }


}

?>