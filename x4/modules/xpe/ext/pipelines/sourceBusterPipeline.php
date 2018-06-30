<?php

class sourceBusterPipeline implements pipeline
{

    public function recieve($fieldContext)
    {
        $field=$fieldContext->optionsData;
        $fieldExploded=explode('@',$field);
        if(!empty($fieldExploded[1])) {
            return $_SESSION['sb'][$fieldExploded[0]][$fieldExploded[1]];
        }
    }

    public function getDataOptions()
    {
        return array(
            'current@typ'=>'current.typ',
            'current@src'=>'current.src',
            'current@mdm'=>'current.mdm',
            'current@cmp'=>'current.cmp',
            'current@cnt'=>'current.cnt',
            'current@trm'=>'current.trm',
            'current_add@fd'=>'current_add.fd',
            'current_add@ep'=>'current_add.ep',
            'current_add@rf'=>'current_add.rf',
            'first@typ'=>'first.typ',
            'first@src'=>'first.src',
            'first@mdm'=>'first.mdm',
            'first@cmp'=>'first.cmp',
            'first@cnt'=>'first.cnt',
            'first@trm'=>'first.trm',
            'first_add@fd'=>'first_add.fd',
            'first_add@ep'=>'first_add.ep',
            'first_add@rf'=>'first_add.rf',
            'session@pgs'=>'session.pgs',
            'session@cpg'=>'session.cpg',
            'udata@vst'=>'udata.vst',
            'udata@uip'=>'udata.uip',
            'udata@uag'=>'udata.uag',
            'promo@code'=>'promo.code'
        );
    }
}

?>
