<?php
use X4\Classes\XRegistry;

class filterloaderBack  extends xPluginBack
{
    public function __construct($name, $module)
    {
        parent::__construct($name, $module);
        $this->_listener->defineFrontActions($this->_module->_commonObj);


    }

    public function analyzeCategory($params)
    {

        $items = $this->_module->_tree->selectAll()->childs($params['kid'])->run();
        $tpl = xTpl::__load('catalog.mf');


        if (!empty($items)) {

            $techInput = array();

            foreach ($items as $item) {
                $data = $tpl->loadExtendedData(array('ean' => $item['params']['tovarbase.ean']));
                $techData = $tpl->getTechData(array('product' => $data));

                foreach ($techData as $outKey=>$element) {

                    foreach ($element['values'] as $val) {

                        $techInput[$outKey][md5($val['key'])] = $val['key'];
                    }

                }
            }

            $this->result['techInput'] = $techInput;

        }


    }


    private function handleBasic($basic)
    {
        if(!empty($basic)) {
            $basic = str_replace(array(' ', ',', '/', '.', ')', '('), array('-', '', '-', '-', '', ''), $basic);
            return mb_strtolower(XCODE::translit($basic));
        }

    }

    public function handlePropertySet($kid,$properties)
    {

        $psetId=$this->_module->_commonObj->_propertySetsTree->initTreeObj(1, $kid, '_PROPERTYSET', array('alias'=>$kid,'Name'=>$kid));

        if(!$psetId) {
            $psetId = $this->_module->_commonObj->_propertySetsTree->lastNonUniqId;
        }

        $this->pset[$kid]=$psetId;

        if (!empty($properties)) {

            foreach ($properties as $property) {
                $id = $this->_module->_commonObj->_propertySetsTree->initTreeObj($psetId, $property['basic'], '_PROPERTY', $property['params']);
            }
        }


    }

    public function handlePropertyGroup($ancestor,$name)
    {
        $id = $this->_module->_commonObj->_propertySetsTreeGroup->initTreeObj(1, $ancestor, '_PROPERTYSETGROUP', array('alias'=>$name));

        if(!$id){
            $id = $this->_module->_commonObj->_propertySetsTreeGroup->lastNonUniqId;
        }else{
            $this->handlePropertyLinks($id,array(33160));
        }

        return $id;

    }


    public function handlePropertyLinks($id,$ids)
    {

            if (!empty($ids)) {
                  foreach ($ids as $externalId) {
                      $this->_module->_commonObj->_propertySetsTreeGroup->initTreeObj($id, $externalId, '_PROPERTYSETLINK');
                  }
              }

            //serialize propertygroup data
            $this->_module->_commonObj->createPropertyGroupSerialized($id);

    }


    public function fetchProperty($params)
    {

        $tpl = xTpl::__load('catalog.mf');
        $items = $this->_module->_tree->selectAll()->childs($params['kid'])->run();

        if (!empty($items)) {

            $techInput = array();

            foreach ($items as $item) {
                $data = $tpl->loadExtendedData(array('ean' => $item['params']['tovarbase.ean']));
                $techData = $tpl->getTechData(array('product' => $data));

                $ancestorName=$this->_module->_tree->readNodeParam($item['ancestor'],'Name');

                $psetGroup=$this->handlePropertyGroup($item['ancestor'],$ancestorName);
                $ids=[];

                foreach ($params['properties'] as $groupId=>$element)
                {



                    if($techData[$groupId])
                    {
                        foreach($techData[$groupId]['values'] as $val)
                        {
                            if(in_array($val['key'],$element))
                            {
                                    $propertyName=$this->handleBasic($val['key']);

                                    $this->handlePropertySet($groupId,
                                        array(array('basic'=>$propertyName,'params'=>array('type'=>'input','alias'=>$val['key'])))
                                    );

                                    $ids[]=$this->pset[$groupId];

                                    $this->_module->_tree->writeNodeParam($item['id'],$groupId.'.'.$propertyName,$val['value']);
                            }

                        }
                    }
                }

                $this->_module->_tree->writeNodeParam($item['id'],'PropertySetGroup',$psetGroup);
                if($ids) {
                    $this->handlePropertyLinks($psetGroup, array_unique($ids));
                }
            }
        }
    }
}
