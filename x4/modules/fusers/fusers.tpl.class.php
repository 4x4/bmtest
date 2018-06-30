<?php

class fusersTpl extends xTpl implements xModuleTpl
{
    public function __construct($module)
    {
        parent::__construct($module);
    }


    public function getAdditionalFields($params)
    {
        return $this->_config['additionalFields'];
    }

    public function getUser($params)
    {
        return $_SESSION['siteuser'];
    }

    public function isFavorite($params)
    {
        if(!empty($params['obj_id']) && !empty($_SESSION['siteuser']['favorites'])) {
            if (in_array($params['obj_id'], $_SESSION['siteuser']['favorites'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getUserPriceAlias()
    {
        if(empty($_SESSION['userPriceCategoryAlias'])) {
            if(!empty($_SESSION['userPriceCategory'])) {
                $explKey = explode('.', $_SESSION['userPriceCategory']);
                $catalog = xCore::moduleFactory('catalog.front');
                $pset = $catalog->_commonObj->_propertySetsTree->singleResult()->selectParams('*')->where(array('@ancestor','=',1),array('@basic','=',$explKey[0]))->run();

                if($pset) {
                    $property = $catalog->_commonObj->_propertySetsTree->singleResult()->selectParams('*')->where(array('@ancestor','=',$pset['id']),array('@basic','=',$explKey[1]))->run();
                    $_SESSION['userPriceCategoryAlias'] = $property['params']['alias'];
                    return $_SESSION['userPriceCategoryAlias'];
                }
            }
        } else {
            return $_SESSION['userPriceCategoryAlias'];
        }
    }
}
