<?php

class showReactMenuAction extends xAction
{

    public function run($params)
    {
        $params['params']['startFromSelected'] = true;
        return $this->dispatchFrontAction('showCatalogMenu', $params);

    }

}
