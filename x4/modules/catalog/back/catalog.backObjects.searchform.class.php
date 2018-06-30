<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;

trait _SEARCHFORM
{

    public function onEdit_SEARCHFORM($params)
    {
        $this->result['searchFormData'] = $this->_commonObj->_searchForms->getNodeInfo($params['id']);
    }


    public function onSave_SEARCHFORM($params)
    {


        $id = $this->_commonObj->_searchForms->initTreeObj(1, '%SAMEASID%', '_SEARCHFORM', $params['searchFormData']);

        if (!empty($params['comparsions'])) {

            foreach ($params['comparsions'] as $comparsion) {
                $this->_commonObj->_searchForms->initTreeObj($id, '%SAMEASID%', '_SEARCHELEMENT', $comparsion['params']);
            }

        }

        return new okResult();
    }


    public function onSaveEdited_SEARCHFORM($params)
    {


        $this->_commonObj->_searchForms->reInitTreeObj($params['id'], '%SAME%', $params['searchFormData']);

        if (!empty($params['comparsions'])) {

            $this->_commonObj->_searchForms->delete()->childs($params['id'])->run();


            foreach ($params['comparsions'] as $comparsion) {
                if ($comparsion) {
                    $id = $this->_commonObj->_searchForms->initTreeObj($params['id'], '%SAMEASID%', '_SEARCHELEMENT', $comparsion['params']);
                }

            }
        }


        $this->pushMessage('searchform-saved');
    }


    public function deleteSearchForms($params)
    {

        $this->deleteObj($params, $this->_commonObj->_searchForms);
    }

    public function searchFormList($params)
    {


        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_commonObj->_searchForms
        ));

        $opt = array(
            'showNodesWithObjType' => array(
                '_SEARCHFORM'
            ),
            'columns' => array(
                'id' => array(),
                '>Name' => array()
            )
        );


        $source->setOptions($opt);
        $this->result = $source->createView(1);
    }


    public function copySearchForm($params)
    {
        $params['ancestor'] = 1;
        $this->copyObj($params, $this->_commonObj->_searchForms);
    }

    public function searchElementList($params)
    {
        if ($childs = $this->_commonObj->_searchForms->selectStruct(array(
            'id',
        ))->selectParams('*')->format('keyval', 'id')->childs($params['id'], 1)->run()
        ) {


            $this->result['comparsions'] = $childs;

        }
    }


}