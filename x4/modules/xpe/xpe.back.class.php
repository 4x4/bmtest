<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;
use X4\Classes\TableJsonSource;

require(xConfig::get('PATH', 'MODULES') . 'xpe/back/xpe.backObjects.xperole.class.php');
require(xConfig::get('PATH', 'MODULES') . 'xpe/back/xpe.backObjects.schemeitem.class.php');
require(xConfig::get('PATH', 'MODULES') . 'xpe/back/xpe.backObjects.schemegroup.class.php');
require(xConfig::get('PATH', 'MODULES') . 'xpe/back/xpe.backObjects.affector.class.php');
require(xConfig::get('PATH', 'MODULES') . 'xpe/back/xpe.backObjects.campaign.class.php');

class xpeBack
    extends xModuleBack
{
    use _XPEROLE, _SCHEMEITEM, _SCHEMEGROUP, _AFFECTOR,_CAMPAIGN;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }


    private function filesExtractor($dir)
    {
        $fields = array();

        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            $fileName = $fileInfo->getBasename('.php');
            $fields[$fileName] = $this->_commonObj->translateWord($fileName);
        }

        return $fields;


    }

    public function deleteCampaigns($params){

        $this->deleteObj($params, $this->_commonObj->_xpeRoles);
    }

    public function campaignDynamicXLS($params){

        $source = Common::classesFactory('TreeJsonSource', array($this->_commonObj->_xpeRoles));

        $opt = array
        (
            'imagesIcon' => array
            (
                '_CAMPAIGN' => 'folderLang.png',
                '_XPEROLE' => 'leaf.gif'
            ),
            'gridFormat' => true,
            'zeroLead' => true,
            'sortby'=>array('@id','desc'),
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_CAMPAIGN',
                '_XPEROLE'
            ),
            'columns' => array
            (
                '>Alias' => array(),
                'basic' => array('name' => 'objType'),
                '>start' => array(),
                '>end' => array()
            )
        );

        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);

    }


    public function changeAncestorGrid($params)
    {
        if (is_array($params['id'])) {
            $ex = $params['id'];
        } elseif (strpos($params['id'], ',') !== false) {
            $ex = explode(',', $params['id']);
        }

        $params['tree']=$this->_commonObj->_xpeRoles;

        if (is_array($ex)) {
            foreach ($ex as $e) {
                $params['id'] = $e;
                $this->changeAncestorGridProcess($params);
            }
        } else {
            $this->changeAncestorGridProcess($params);
        }
    }

    public function getRolesStats($params)
    {

        $query = "SELECT role_id,count(*) as visits FROM xpe_statistics group by role_id ";
        $PDO = XRegistry::get('XPDO');
        if (($pdoResult = $PDO->query($query))) {

            while ($pf = $pdoResult->fetch(PDO::FETCH_ASSOC))
            {
                $pf['role']=$this->_commonObj->_xpeRoles->readNodeParam($pf['role_id'],'Alias');
                $data[] = $pf;
            }

        $this->result['stats']=$data;
        }
    }

    public function schemeGroupsTable($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));


        $opt = array(
            'showNodesWithObjType' => array(
                '_SCHEMEGROUP'
            ),
            'columns' => array(
                'id' => array(),
                'basic' => array(),
                '>Alias' => array()
            )
        );

        $source->setOptions($opt);
        $this->result = $source->createView(1);


    }


    public function deleteXpeRole($params)
    {
        $this->deleteObj($params, $this->_commonObj->_xpeRoles);
    }


    public function xpeRolesTable($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_commonObj->_xpeRoles
        ));


        $opt = array(
            'showNodesWithObjType' => array(
                '_XPEROLE'
            ),
            'columns' => array(
                'id' => array(),
                '>Alias' => array()
            )
        );

        $source->setOptions($opt);
        $this->result = $source->createView(1);


    }


}
