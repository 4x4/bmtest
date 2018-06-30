<?php

class xpeXfront extends xpeFront
{

    public function setSbSession($params)
    {
        $_SESSION['sb']=$params['data'];
    }

}

?>