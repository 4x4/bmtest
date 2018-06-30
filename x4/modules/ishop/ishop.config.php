<?php

xConfig::pushConfig(array(

    'iconClass' => 'i-cart',
    'actionable' => 1,
    'admSortIndex' => 60,
    'cartStorage' => 'cartSessionStorage',
    'orderTypes'=>array(    
        'default'=>'default order',
        'companyOrder'=>'company order'
    )

));