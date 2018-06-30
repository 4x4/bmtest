<?php

class formsTpl extends xTpl implements xModuleTpl
{
    public function __construct($module)
    {
        parent::__construct($module);
    }

    public function valuesCount($params)
    {
        if (is_string($params[0])) {
            $delimiter = ($params[1]) ? $params[0] : "\n";
            $size = sizeof(explode($delimiter, $params[0]));
        } else if (is_array($params[0])) {
            $size = sizeof($params[0]);
        }

        return ($size > 4) ? 4 : $size;
    }
}

?>
