<?php

use X4\Classes\XTreeEngine;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;
use X4\Classes\XCache;


class filterIndexer
{

    public function __construct($class)
    {
         $this->catalog= xCore::loadCommonClass('catalog');
         $this->catalog->_tree->cacheState(true);
         $this->catalog->_sku->cacheState(true);
    }

    
    public function index()
    {
        $this->findAllSearchForms();
        return new okResult();
    }

    public function fetchProperties($propertySet, $property)
    {

        if ($pset = $this->catalog->findPsetByName($propertySet)) {
            $id = array_search($propertySet, $this->catalog->psetIdToNameStorage);
            if ($this->catalog->psetInfoStorage[$id]['params']['isSKU']) {
                $this->selectSku = true;
                $pset[$property]['isSKU'] = true;
            } else {
                $this->selectObject = true;
            }

            return $pset[$property];
        }
    }


   
    public function findAllSearchForms()
    {
         $itemsProperties=$this->catalog->_propertySetsTree->selectStruct('*')->where(array('type','=','searchForm'))->run();
         
         foreach($itemsProperties as $property)
         {
                $node=$this->catalog->_propertySetsTree->getNodeStruct($property['ancestor']);
                $propertyName=$node['basic'].'.'.$property['basic'];

                $catalogPropLinksCategory=$this->catalog->_tree->selectAll()->where(array($propertyName,'<>',''))->run();

                if(is_array($catalogPropLinksCategory)){
                        foreach ($catalogPropLinksCategory as $category){
                            $this->constructIndex($category,$propertyName);
                        }
                }
         }
         
    }

    private function objectField($field,$fieldName,$fieldPropertyInstance)
    {

        $matrix=array();
        $field['instance'] = $this->catalog->getPropertyType($fieldPropertyInstance['params']['type']);
        $matrix[$fieldName]['field']=$field;

        foreach ($this->objects as $object)
        {
            if (!empty($object['params'][$fieldName]))
            {
                    $field['instance']->prepareFacet($fieldName,$object,$field,$matrix);

            }
        }

        return $matrix;

    }

    private function skuField($field, $fieldName, $fieldPropertyInstance, $objects)
    {
        $matrix=array();
        $field['instance'] = $this->catalog->getPropertyType($fieldPropertyInstance['params']['type']);
        $matrix[$fieldName]['field']=$field;
        $fieldName=$field['params']['property'];

        foreach ($this->relativeSku as $object)
        {
            if (!empty($object['params'][$fieldName]))
            {
                $field['instance']->prepareFacet($fieldName,$object,$field,$matrix);
            }
        }
    }


    private function constructIndex($category,$property)
    {
        //taking all the searchForm fields
        $filterFields = $this->catalog->_searchForms->selectAll()->childs($category['params'][$property])->run();

        //iteration throught fields;
        if (!empty($filterFields)) {

            $this->objects = $this->catalog->_tree->selectAll()->childs($category['id'])->run();
            $ids=XARRAY::asKeyVal($this->objects,'id');
            $this->relativeSku = $this->catalog->findRelativeSku($ids, true,false,true);

            if (!empty($this->objects)) {
                $matrix = array();
                foreach ($filterFields as $field) {

                    $fieldName = $field['params']['propertySet'] . '.' . $field['params']['property'];
                    $fieldPropertyInstance = $this->fetchProperties($field['params']['propertySet'], $field['params']['property']);

                    if($fieldPropertyInstance['isSKU']) {
                        $matrix = $this->skuField($field, $fieldName, $fieldPropertyInstance);
                    }else{
                        $matrix = $this->objectField($field, $fieldName, $fieldPropertyInstance);
                    }
                }
            }

            if (!empty($matrix)) {
                foreach ($matrix as $key => $element)
                {
                    $facet=$element['field']['instance']->buildFacet($element, $field);
                    unset($element['field']['instance']);
                    $facet['field']=$element['field'];
                    XCache::serializedWrite($facet,'facets', $category['id'].$key);
                }

            }

        }
    }

    

  
}

