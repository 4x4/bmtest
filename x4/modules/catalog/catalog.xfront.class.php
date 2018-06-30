<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;


class catalogXfront extends catalogFront
{


    public function getRelativeSku($params)
    {

        if (!empty($params['propsToFilter'])) {
            foreach ($params['propsToFilter'] as $key => $param) {
                if ($param == '') {

                    unset($params['propsToFilter'][$key]);
                }
            }
        }

        $this->result['relativeSku'] = $this->getRelativeSkuByProps($params['id'], $params['propsToFilter']);
    }


    public function buildUrlTransformation($params)
    {

        $this->result['url'] = $this->_commonObj->buildUrlTransformation($params['url']);

    }


    public function buildUrlReverseTransformation($params)
    {

        $this->result['url'] = $this->_commonObj->buildUrlReverseTransformation($params['url']);

    }


}
