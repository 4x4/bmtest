<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

class staticField extends personDataField implements personDataFieldInterface
{
    public function exportField()
    {
        return array('fieldType' => 'staticField',
            'data' => array(
                'name' => $this->name,
                'alias' => $this->alias,
                'value' => $this->value
            )
        );

    }

    public function importField($data)
    {
        $this->name = $data['name'];
        $this->alias = $data['alias'];
        $this->value = $data['value'];
    }

}
