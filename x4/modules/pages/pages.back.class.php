<?php

use X4\Classes\XPDO;
use X4\Classes\XRegistry;

require(xConfig::get('PATH', 'MODULES') . 'pages/back/pages.backObjects.page.class.php');
require(xConfig::get('PATH', 'MODULES') . 'pages/back/pages.backObjects.group.class.php');
require(xConfig::get('PATH', 'MODULES') . 'pages/back/pages.backObjects.lversion.class.php');
require(xConfig::get('PATH', 'MODULES') . 'pages/back/pages.backObjects.link.class.php');
require(xConfig::get('PATH', 'MODULES') . 'pages/back/pages.backObjects.domain.class.php');


class pagesBack
    extends xModuleBack
{

    use _PAGE, _GROUP, _LVERSION, _DOMAIN, _LINK;

    function __construct()
    {
        parent::__construct(__CLASS__);
    }
    //изменить шаблон страницы


    public function getSlotz($params)
    {

        if (!$templatePath = $params['tplName']) {
            $templatePath = $this->_tree->readNodeParam($params['id'], 'Template');
        }

        $domain = $this->_commonObj->getObjectDomainById($params['id']);
        xCore::callCommonInstance('templates');
        $templates = templatesCommon::getInstance();
        $templateSource = $templates->getTpl($templatePath, $domain['params']['TemplateFolder']);

        if ($slotz = $templateSource['slotz']) {
            foreach ($slotz as $slot) {
                $this->result['slotz'][] = array
                (
                    'basic' => $slot,
                    'alias' => $slot
                );
            }
        }
    }

    /**
     * получить слоты для всех шаблонов определенных для данного хоста
     * $params['id'] - id объекта
     */
    public function getSlotzAll($params)
    {
        $domain = $this->_commonObj->getObjectDomainById($params['id']);
        xCore::callCommonInstance('templates');
        $templates = templatesCommon::getInstance();

        if ($slotz = $templates->getSlotzForDomain($domain['params']['TemplateFolder'])) {
            foreach ($slotz as $slot) {
                $this->result['slotz'][] = array
                (
                    'basic' => $slot,
                    'alias' => $slot
                );
            }
        }
    }

    /**
     * получить модули для заданного объекта
     * $params['id'] - id объекта
     */
    public function getModules($params)
    {

        if ($modules = $this->_tree->selectStruct('*')->selectParams('*')->childs($params['id'], 2)->getDisabled()->asTree()->run()) {
            $slotPack = array();

            while (list($key, $slot) = $modules->fetch($params['id'])) {
                if ($mods = $modules->fetchArray($key)) {
                    foreach ($mods as $mod) {
                        if (isset($mod['params']['_Type'])) {

                            $moduleInstance = xCore::moduleFactory($mod['params']['_Type'] . '.back');

                            $action = $moduleInstance->_commonObj->getAction($mod['params']['_Action']);

                            if (method_exists($moduleInstance, 'actionExtra')) {
                                $mod['params'] = $moduleInstance->actionExtra($mod['params']);
                            }
                            (!$mod['disabled']) ? $mod['params']['Active'] = 1 : $mod['params']['Active'] = 0;

                            $mod['params']['frontActionName'] = $action['frontName'];

                            $slotPack[$slot['basic']][] = array
                            (
                                'params' => XARRAY::convertDotsToArray($mod['params']),
                                'icon' => $moduleInstance->_config['iconClass'],
                                'slotBasic' => $slot['basic'],
                                'id' => $mod['id']
                            );
                        }
                    }
                }
            }

            $this->result['modules'] = $slotPack;
        }
    }

    /*TODO*/
    function getAccess($params)
    {
        Common::call_common_instance('fusers');
        $fusers = fusers_module_common::getInstance();
        $this->result['access']['DisableAccess'] = $this->_tree->ReadNodeParam($params['id'], 'DisableAccess');

        if ($this->result['access']['LinkId'] = $this->_tree->ReadNodeParam($params['id'], 'AuthRedirId')) {
            $this->result['access']['Link'] = $this->_common_obj->create_page_path($this->result['access']['LinkId'],
                true,
                '',
                true);
        }

        $usercp = $this->_tree->ReadNodeParam($params['id'], 'NoAuthRedirId');
        $this->result['access']['NoAuthRedirId'] = XHTML::arrayToXoadSelectOptions(
            XARRAY::arr_to_lev(
                $this->_common_obj->get_page_module_servers('user_panel'),
                'id',
                'params',
                'Name'),
            $usercp,
            true);

        if ($groups = $fusers->obj_tree->GetChildsParam(1, array('Name'))) {
            if ($results = $fusers->get_node_rights($params['id'], 'pages')) {
                foreach ($results as $r) {
                    //read
                    if ($r['params']['Rights'] == 1) {
                        $groups[$r['ancestor']]['r'] = 1;
                    }
                }
            }

            $this->result['access_groups'] = $groups;
        }
    }

    /*TODO*/
    function set_access($params)
    {
        Common::call_common_instance('fusers');
        $fusers = fusers_module_common::getInstance();

        if ($params['access']) {
            unset($params['access']['Name']);
            $params['access']['AuthRedirId'] = $params['access']['LinkId'];
            $this->reinit_page($params['id'], '%SAME%', $params['access']);
        }

        if ($params['groups']) {
            if ($results = $fusers->get_node_rights($params['id'], $this->_module_name)) {
                foreach ($results as $k => $v) {
                    $fusers->obj_tree->DelNode($k);
                }
            }

            foreach ($params['groups'] as $gr_key => $gr_) {
                if ($gr_)
                    $fusers->init_scheme_item(str_replace('_', '', $gr_key), array
                    (
                        'Module' => 'pages',
                        'Node' => $params['id'],
                        'Rights' => 1
                    ));
            }
        }
    }

   public function getTemplates($node, $selected = null)
    {
      
        xCore::callCommonInstance('templates');
        $templates = templatesCommon::getInstance();
        $domain = $this->_commonObj->getObjectDomainById($node);

        
        if ($templatesList = $templates->getTemplatesForDomain($domain['params']['TemplateFolder'])) {
            foreach ($templatesList as $template) {
                if (!$template['name']) {
                    $template['name'] = $template['path'];
                } else {
                    $template['name'] = $template['path'] . '(' . $template['name'] . ')';
                }

                $template['path'] = basename($template['path']);
                $tplOptions[$template['path']] = $template['name'];
            }

            return XHTML::arrayToXoadSelectOptions($tplOptions, $selected, true);
        }
    }

    public function onObjectSituationChanged($params)
    {
        $this->result['data']['Template'] = $this->getTemplates($params['id']);
    }


    public function initSlotz($id, $modules)
    {
        if (is_array($modules)) {
            $this->_tree->delete()->childs($id, 1)->where(array
            (
                '@obj_type',
                '=',
                '_SLOT'
            ))->run();

            $this->_tree->delete()->childs($id, 2)->where(array
            (
                '@obj_type',
                '=',
                '_MODULE'
            ))->run();

            foreach ($modules as $slot => $slotz) {


                $slotId = $this->_tree->initTreeObj($id, $slot, '_SLOT');

                if (is_array($slotz)) {
                    foreach ($slotz as $module) {
                        if (isset($module['params']['__secondary'])) {
                            $secondary = XARRAY::convertArrayToDots('__secondary', $module['params']['__secondary']);
                            $module['params'] = array_merge($secondary, $module['params']);
                            unset($module['params']['__secondary']);
                        }

                        if ($module['params']['Active']) {
                            $disabled = 0;
                        } else {
                            $disabled = 1;
                        }
                        $this->_tree->initTreeObj($slotId, '%SAMEASID%', '_MODULE', $module['params'], 0, $disabled);


                        $this->_EVM->fire('pages.back:slotModuleInitiated', array
                        (
                            'slotId' => $slotId,
                            'module' => $module,
                            'pageId' => $id
                        ));
                    }
                }
            }
        }
    }


    private function getCommonTemplates($selected=false)
    {
        xCore::callCommonInstance('templates');

        if ($dirs = XFILES::directoryList(xConfig::get('PATH', 'TEMPLATES'))) {
            foreach ($dirs as $dir) {
                $templateFolders[basename($dir)] = basename($dir);
            }

            return XHTML::arrayToXoadSelectOptions($templateFolders, $selected, true);
        }
    }

    private function getStartPages($id, $selected = null)
    {
        if ($childs = $this->_tree->selectStruct(array('id'))->selectParams(array('Name'))->childs($id, 1)->where(array
        (
            '@obj_type',
            '=',
            array
            (
                '_PAGE',
                '_GROUP',
                '_LVERSION'
            )
        ))->format('valparams', 'id', 'Name')->run()
        ) {
            return XHTML::arrayToXoadSelectOptions($childs, $selected, true);
        }
    }


    public function treeDynamicXLS($params)
    {

        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $opt = array
        (
            'imagesIcon' => array
            (
                '_DOMAIN' => 'folderDomain.png',
                '_LVERSION' => 'folderLang.png',
                '_GROUP' => 'folder.gif'
            ),
            'zeroLead' => true,
            'gridFormat' => true,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_GROUP',
                '_DOMAIN',
                '_LINK',
                '_LVERSION',
                '_PAGE'
            ),
            'endLeafs' => array('_PAGE', '_LINK'),
            'columns' => array
            (
                '>Name' => array(),
                'basic' => array('name' => 'objType')
            )
        );

        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }

    public function treeDynamicXLSGroupsOnly($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $opt = array
        (
            'imagesIcon' => array
            (
                '_DOMAIN' => 'folderDomain.png',
                '_LVERSION' => 'folderLang.png',
                '_GROUP' => 'folder.gif'
            ),
            'gridFormat' => true,
            'zeroLead' => true,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_DOMAIN',
                '_LVERSION',
                '_GROUP'
            ),
            'columns' => array
            (
                '>Name' => array(),
                'basic' => array('name' => 'objType')
            )
        );

        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }

    /***
     * Стандартный обработчик поиска
     *
     * @param mixed $params приходит ключ по которому необходимо произвести поиск
     */

    public function onSearchInModule($params)
    {
        $params['word'] = urldecode($params['word']);

        $resultBasic = $this->_tree->selectParams(array('Name'))->selectStruct(array
        (
            'id',
            'basic',
            'obj_type'
        ))->where(array
        (
            '@basic',
            'LIKE',
            '%' . $params['word'] . '%'
        ))->format('keyval', 'id')->run();

        $resultName = $this->_tree->selectParams(array('Name'))->selectStruct(array
        (
            'id',
            'basic',
            'obj_type'
        ))->where(array
        (
            'Name',
            'LIKE',
            '%' . $params['word'] . '%'
        ))->format('keyval', 'id')->run();

        XARRAY::arrayMergePlus($resultBasic, $resultName, true);
        $this->result['searchResult'] = Common::gridFormatFromTree($resultBasic, array
        (
            'id',
            'obj_type',
            'Name',
            'basic'
        ));

    }

    public function getModule($params)
    {
        $this->result['module'] = $this->_tree->getNodeInfo($params['id']);
    }

    public function onAction_showLevelMenu($params)
    {
        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];

            $dtc = $this->_tree->selectStruct(
                array('id'))->getParamPath('Name')->where(array
            (
                '@id',
                '=',
                $params['data']['params']['showGroupId']
            ))->run();

            $this->result['actionDataForm']['showGroup'] = $dtc['paramPathValue'];
        }

        $this->result['actionDataForm']['menuTemplate']
            = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['menuTemplate'],
            array('.showLevelMenu.html'));
    }

    public function onAction_showPath($params)
    {
        $this->result['actionDataForm']['Template']
            = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'],
            array('.showPath.html'));
    }


    public function _copy($params)
    {
        $this->_common_obj->_copy($this, $params, array
        (
            '_GROUP',
            '_PAGE',
            '_SLOT',
            '_LINK',
            '_MODULE'
        ));
    }


    /*fed*/
    function create_module($params)
    {
        $id = $this->init_module($params['slot_id'], $params['params']);
        $module = $this->_common_obj->render_module($id);
        $this->result['moduleHtml'] =
            '<map alias="' . $params['params']['alias'] . '" mtype="' . $params['params']['type']
            . '" class="__module" id="_m' . $id . '">' . $module . '</map>';
    }

    function save_module($params)
    {
        if ($this->reinit_module($params['id'], $params['params'])) {
            $this->result['moduleHtml'] = $this->_common_obj->render_module($params['id']);
        }
    }

    public function createNewRoute($params)
    {

        $this->result['routes'] = $this->_commonObj->createNewRoute($params['from'], $params['to'], (int)$params['is301']);
        return new okResult();
    }

    public function saveRoutePart($params)
    {
        $updateParams[$params['part']] = $params['text'];

        if (XPDO::updateIN('routes', (int)$params['id'], $updateParams)) {
            return new okResult();
        } else {
            return new badResult();
        }
    }

    public function deleteRoute($params)
    {

        $this->_PDO->query('delete from routes where id="' . $params['id'] . '"');

    }

    public function route301Switch($params)
    {

        if (XPDO::updateIN('routes', (int)$params['id'], array('is301' => (int)$params['state']))) {
            return new okResult('route_saved');
        } else {
            return new badResult('save_error');
        }
    }

    public function routesTable()
    {
        $source = Common::classesFactory('TableJsonSource', array());

        $opt = array
        (
            'onPage' => 100,
            'table' => 'routes',
            'order' => array('id'),
            'where' => ' 1',
            'idAsNumerator' => 'id',
            'gridFormat' => 1,
            'columns' => array
            (
                'id' => array(),
                'from' => array(),
                'to' => array(),
                'is301' => array(),
            )
        );

        $source->setOptions($opt);
        $this->result = $source->createView();
    }


    public function getWidgetCacheStat($params)
    {

        $dir = xConfig::get('PATH', 'CACHE');
        if ($list = XFILES::directoryList($dir)) {

            foreach ($list as $listItem) {
                $stats[] = array('folder' => basename($listItem), 'size' => XFILES::getDirSize($dir.$listItem) / 1048576);
            }

        }

        $this->result['data'] = $stats;

    }


}


