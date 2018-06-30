<?php

class mfTpl extends xTpl implements xPluginTpl
{
    var $assembleMatrix;

    public function __construct()
    {
        parent::__construct('catalog.mf');
    }

    public function cutAdvantages($params)
    {
        return array_slice($params['advantages'], 0, 4);
    }

    public function ntobr($params)
    {
        $b=str_replace('\n','<br>',$params['value']);
        return $b;
    }

    public function loadExtendedData($params)
    {
        $path=PATH_.'db/';
        $ean=$params['ean'];

        $eanPart=substr($ean,0,4);
        $path=$path.'/data/EAN/'.$eanPart.'/'.$ean.'.json';

        if(file_exists($path)) {
            $file = file_get_contents($path);
            $file= json_decode($file,true);
            return $file['Product'];
        }
    }

    public function prepareFetchTechValues($productFeatures)
    {
            $features=array();

            foreach($productFeatures as $feature)
            {
                $features[$feature['@attributes']['CategoryFeatureGroup_ID']][]=array(
                    'key'=>$feature['Feature']['Name']['@attributes']['Value'],
                    'value'=>str_replace('\n','<br/>',$feature['@attributes']['Presentation_Value'])

                );

            }

            return  $features;
    }

    public function getTechData($params)
    {
        $product=$params['product'];

        if(is_array($product)&&!empty($product['CategoryFeatureGroup']))
        {
            $features=$this->prepareFetchTechValues($product['ProductFeature']);

            foreach($product['CategoryFeatureGroup'] as $itemFeature)
            {
               $techData[$itemFeature['@attributes']['ID']]['name']=$itemFeature['FeatureGroup']['Name']['@attributes']['Value'];
               $techData[$itemFeature['@attributes']['ID']]['values']=$features[$itemFeature['@attributes']['ID']];
            }

            return $techData;
        }
        return false;


    }

    public function findProductRelated($params)
    {
        $related=$params['ProductRelated'];

        if(!empty($related)) {

            foreach ($related as $rel) {
                $ids[] = $rel['Product']['@attributes']['Prod_id'];
            }

            $items = $this->_module->_tree->selectStruct(array('id'))->where(array('tovarbase.PID', '=', $ids))->format('valval', 'id', 'id')->run();

            $catalogTpl = xTpl::__load('catalog');

            return $objects = $catalogTpl->getConnected(array('id' => $items, 'linkId' => 8714));
        }

        return false;

    }


    public function checkInStock($params)
    {

        if (isset($params['skuList'])) {

            foreach($params['skuList'] as $item){

                if($item['params']['stock']>0){
                    return true;
                }

            }
        }

        return false;

    }

    public function splitByCallType($params)
    {
		
        foreach($params['objects'] as $object)
		{			
			$key=$object['tovarbase']['callType']['selector']['code'];
			$splitted[$key][]=$object;
		}
		
		
		return $splitted;
    }


    public function getFilterList($params)
    {
        $range=array(0,1000,2000,5000,10000,20000,30000,50000,100000,150000,200000,300000,500000,1000000,2000000,3000000,5000000);

        $min=$params['values']['min']['value'];
        $max=$params['values']['max']['value'];

        $outRange=array();
        $z=0;
        foreach($range as $rangeItem) {
            $z++;

            if ($max > $rangeItem) {
                $outRange[] = array(
                    'min' => $rangeItem,
                    'max' => $range[$z + 1]
                );

            }
        }

            return $outRange;



    }
}

     