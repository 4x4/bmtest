<?php

trait _SEARCHELEMENT
{


    public function onEdit_SEARCHELEMENT($params)
    {

        $this->getPsetListInitialData($params);
    }


    public function onCreate_SEARCHELEMENT($params)
    {

        $this->getPsetListInitialData();

    }

}