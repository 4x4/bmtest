<?php

class timerPipeline implements pipeline
{
    public function recieve($fieldContext)
    {

        return time();
    }
}

?>
