<?php
class mfXfront
    extends mfFront
{
    

    public function __construct() { parent::__construct(null); }


    public function getObjectsByFilter($params)
    {
		
		$catalog= xCore::moduleFactory('catalog.front');
		  
        $objects = $catalog->getObjectsByFilterInner($params['filter'], $params['linkId'], 0, $params['onpage']);

        $this->result['objects']=$objects;

    }


}