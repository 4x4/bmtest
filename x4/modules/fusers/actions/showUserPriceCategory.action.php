<?php

use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showUserPriceCategoryAction extends xAction
{
    public function __construct()
    {
        parent::__construct('fusers');
    }

    private function setupPriceCategory()
    {
        if(isset($_GET['userPriceCategory']))
        {
            $_SESSION['userPriceCategory']=$_GET['userPriceCategory'];
            XRegistry::get('EVM')->fire($this->_moduleName . '.setupPriceCategory:after', array('userPriceCategory' =>  $_SESSION['userPriceCategory']));
        }
        elseif(empty($_SESSION['userPriceCategory']))
        {
            $_SESSION['userPriceCategory']=$_SESSION['siteuser']['userdata']['defaultPrice'];
        }
    }

    public function run($params)
    {
          if ($_SESSION['siteuser']['authorized'])
          {
              $this->setupPriceCategory();
              $this->loadModuleTemplate($params['params']['Template']);

              if(!empty($_SESSION['siteuser']['userdata']['accessiblePrices']))
              {
                  $catalog = xCore::moduleFactory('catalog.front');
                  $accessiblePrices=json_decode($_SESSION['siteuser']['userdata']['accessiblePrices'],true);
                  foreach($accessiblePrices as $priceKey=>$price)
                  {
                      if($price == false){
                          continue;
                      }

                      $explKey=explode('.',$priceKey);

                      $pset=$catalog->_commonObj->_propertySetsTree->singleResult()->selectParams('*')->where(array('@ancestor','=',1),array('@basic','=',$explKey[0]))->run();

                      if($pset){

                          $property=$catalog->_commonObj->_propertySetsTree->singleResult()->selectParams('*')->where(array('@ancestor','=',$pset['id']),array('@basic','=',$explKey[1]))->run();

                          if($priceKey==$_SESSION['userPriceCategory']) {
                              $selected=true;
                              $_SESSION['userPriceCategoryAlias'] = $property['params']['alias'];
                          } else {
                              $selected=false;
                          }

                         $userPrices[$priceKey]=array('alias'=>$property['params']['alias'],'selected'=>$selected);

                      }
                  }
                  $this->_TMS->addMassReplace('showUserPriceCategory',array('userPrices'=>$userPrices));
                  return $this->_TMS->parseSection('showUserPriceCategory');
              }
          }
    }
}
