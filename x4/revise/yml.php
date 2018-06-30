<?php
error_reporting(0);
preg_match('@^(?:www.)?([^/]+)@i', $_SERVER['HTTP_HOST'], $m);
define('REAL_HTTP_HOST', $m[1]);


require('boot.php');      
session_start();

xConfig::set('GLOBAL','currentMode','front');
xConfig::set('PATH','fullBaseUrlHost', CHOST . $_SERVER['REQUEST_URI']);
xConfig::set('PATH','fullBaseUrl',$_SERVER['REQUEST_URI']);
xConfig::set('PATH','baseUrl',$_SERVER['REQUEST_URI']);

class ymlCatalog
{
        public $shopName = 'avex.by';
        public $companyName = 'OOO "АВЕКС"';
        public $siteURL = 'http://avex.by'; 
        public $category=60902;
        public $linkId=8714;
        public $onPage=10000;
        public $endleafs=5;
        public $currency='BYN';
 
       public function __construct()
       {
           $this->marketParser = new YandexMarket($this->shopName, $this->companyName, $this->siteURL);          
           $this->marketParser->addCurr($this->currency, 1);
           xConfig::set('GLOBAL', 'currentMode','front');
           $this->catalog=xCore::moduleFactory('catalog.front');
           $this->catalog->_tree->cacheState($catalog->_config['cacheTree']['tree']);
           $this->catalog->nativeSelectObjectsFilters=$catalog->_commonObj->nativeSelectObjectsFilters;
           
       }      
       
       
       public function categoriesProcess()
       {   
           $categories=$this->catalog->_tree->selectStruct('*')->selectParams('*')->childs($this->category,$this->endleafs)->where(array('@obj_type','=','_CATGROUP'))->run();
           
           if(!empty($categories)){
               
                foreach($categories as $category){
                    
                  $this->marketParser->addCat($category['params']['Name'],$category['id'],$category['ancestor']);  
                         
                }
           }
           
           
       }
       
     private function  escapeXML($string) 
     {
        return  str_replace(array('&'),array(),$string);
     }
           
       public function skuProcess()
       {
           
           $data=array('f'=>array('ancestor'=>array('ancestor'=>$category,'endleafs'=>$this->endleafs,'objType'=>array('_CATOBJ'))));   
           $objects=$this->catalog->getObjectsByFilterInner($data,$this->linkId,0,$this->onPage);
         
              foreach ($objects as $entry) 
              {
                  foreach($entry['_sku'] as $sku)
                  {                                 
                        $available=0;    
                        if($sku['params']['stock1'] or $sku['params']['stock2'])
                        {
                            $available=1;    
                        }

                        
                        $offer = new OfferYmt($sku['id'],$available);                            
                        
                        $offer->setUrl($entry['_main']['link']);
                        $entry['_main']['Name']=$this->escapeXML($entry['_main']['Name']);
                        
                        $offer->setRequired($sku['params']['regularPrice']['value']['value'],
                         $this->currency, $entry['_main']['ancestor'], $this->escapeXML($entry['tovarbase']['brand']['_main']['Name']),
                         htmlspecialchars($entry['_main']['Name'], ENT_XML1, 'UTF-8'));
                        
                        
                        $description=$this->escapeXML(strip_tags($sku['params']['descr']));
                        $offer->setElem('description',$description);                     
                        $offer->setElem('country',$sku['params']['madeIN']);                     
                        $this->marketParser->addOffer($offer->save());
                  }         
              }
           
       }
    
       public  function out() 
         {         
              $this->categoriesProcess();              
              $this->skuProcess();
              $xml = $this->marketParser->save();
              header('Content-type:application/xml');      
              echo $xml;
        }
 
}
 
 
 $yml=new ymlCatalog();
 $yml->out();



?>