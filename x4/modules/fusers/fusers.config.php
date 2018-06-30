<?php

xConfig::pushConfig(array(

    'iconClass' => 'i-user2',
    'admSortIndex' => 20,
    'actionable' => 1,
    'additionalFields'=>array(

        array(
            'alias'=>'Тестовое поле',
            'fieldName'=>'test',
            'type'=>'input'
        ),

        array(
            'alias'=>'Тестовое поле чекбокс',
            'fieldName'=>'test2',
            'type'=>'checkbox'
        )

    )
));