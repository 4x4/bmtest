<?php

class cbossPipeline implements pipeline
{
    public function recieve($fieldContext)
    {
        return time();
    }
}

?>
