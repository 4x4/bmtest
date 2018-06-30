<?php

class utmPipeline implements pipeline
{
    public function recieve($fieldContext)
    {
        return $_GET[$fieldContext->name];
    }

}

?>
