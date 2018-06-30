<?php
use X4\Classes\XPDO;
use X4\Classes\XRegistry;


class pagesCommon
    extends xModuleCommon implements xCommonInterface
{
    public $_useTree = true;
    public $nativeLangVersion = false;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->_tree->setLevels(14);
        $this->_tree->setCacheParams('tree', 3600);

        $this->_tree->setObject('_ROOT', array
        (
            'StartPage',
            'Active',
            'Name',
            'Gacode'
        ));


        $this->_tree->setObject('_DOMAIN', array
        (
            'StartPage',
            'Active',
            'TemplateFolder',
            'Name'


        ), array('_ROOT'));


        $this->_tree->setObject('_LVERSION', array
        (
            'StartPage',
            'Active',
            'Name',
            'default404Page'

        ), array('_DOMAIN'));

        $this->_tree->setObject('_LINK', array
        (
            'Link',
            'Active',
            'Comment',
            'DisableMapLink',
            'Visible',
            'Name',
            'connectedPageId'

        ), array('_GROUP', '_LVERSION'));

        $this->_tree->setObject('_GROUP', array
        (
            'Name',
            'Active',
            'StartPage',
            'Icon',
            'Comment',
            'Visible',
            'Template',
            'DisableGlobalLink',
            'DisableAccess',
            'DisablePath',
            'DisableMapLink'
        ), array('_GROUP', '_LVERSION'));

        $this->_tree->setObject('_PAGE', array
        (
            'Name',
            'Active',
            'AuthRedirId',
            'NoAuthRedirId',
            'Visible',
            'Template',
            'Keywords',
            'Title',
            'Icon',
            'Comment',
            'DisableGlobalLink',
            'DisableMapLink',
            'DisablePath',
            'DisableCache',
            'Meta',
            'Description',
            'DoNotSuppressTitle',
            'DisableAccess'
        ), array('_GROUP', '_LVERSION'));


        $this->_tree->setObject('_SLOT', array('Active'), array('_PAGE', '_LVERSION', '_GROUP', '_DOMAIN', '_ROOT'));
        $this->_tree->setObject('_MODULE', null, array('_SLOT'));

    }


    public function getPageSlotz($id)
    {
        return $this->_tree->GetChildsParam($page_id, $this->_tree->getObject('_SLOT'), true, array('obj_type' => array('_SLOT')));
    }


    public function getModuleByAction($pageId, $action)
    {

        $action = $this->_tree->selectStruct('*')->selectParams('*')->childs($pageId)->where(array('_Action', '=', $action))->run();
        if (isset($action[0])) return $action[0];

    }


    public function getPagesByModuleServerSelector($action, $selected = null)
    {
        $destinationPages = $this->getPagesByModuleServer($action);

        if (isset($destinationPages)) {

            foreach ($destinationPages as $pages) {
                $dst[$pages['id']] = $pages['paramPathValue'];
            }

            return XHTML::arrayToXoadSelectOptions($dst, $selected, true);
        }

    }


    public function getPagesByModuleServer($action)
    {
        if (!is_array($action)) {
            $action = array($action);
        }

        if ($modules = $this->_tree->selectStruct('*')->where(array('_Action', '=', $action))->run()) {

            foreach ($modules as $module) {
                array_pop($module['path']);
                $ids[] = array_pop($module['path']);

            }

            $pages = $this->_tree->selectStruct(array('id'))->getParamPath('Name', '/', true)->where(array('@id', '=', $ids))->run();

            return $pages;
        }
    }

    public function getObjectDomainById($id)
    {
        if (!is_array($id)) {
            $node = $this->_tree->getNodeInfo($id);

            if ($node['obj_type'] == '_DOMAIN') {
                return $node;
            }

        } else {
            $node = $id;
        }

        return $this->_tree->selectStruct('*')->selectParams('*')->where(array('@id', '=', $node['path'][1]))->run();


    }


    public function findLinksByExternalId($ExternalLinkId)
    {
        if ($s = $this->_tree->Search(array('ExternalLinkId' => $ExternalLinkId), false, array('obj_type' => '_LINK'))) {
            return $s[0];
        }

    }

    public function createNewRoute($from, $to, $redirect = 0)
    {

        $insert['id'] = 'NULL';
        $insert['from'] = $from;
        $insert['to'] = $to;
        $insert['is301'] = $redirect;

        if ($pdoResult = XRegistry::get('XPDO')->query("SELECT id FROM `routes` WHERE `from`='" . $from . "'")) {
            $res = $pdoResult->fetch();
        }

        if (!$res) {
            return XPDO::insertIN('routes', $insert);
        } else {
            $res = current($res);
            $this->result['routes'] = XPDO::updateIN('routes', (int)$res['id'], $insert);
        }

    }

    public function createPagePath($id, $excludeHost = false, $action = '', $useNames = false)
    {
        $bp = $this->_tree->selectStruct(array('id'))->getBasicPath()->where(array('@id', '=', $id))->run();

        if ($action) $action = '/~' . $action . '/';

        if ($bp['basicPathValue'])
            if (!$excludeHost) {
                $path = $this->linkCreator($bp['basicPath']) . $action;
            } else {
                if ($useNames) {

                    $namePath = $this->_tree->selectParams(array('Name'))->where(array('@id', '=', $bp['path']))->format('paramsparams', 'id', 'Name')->run();
                    $path = implode('/', $namePath);

                } else {

                    $path = $this->linkCreator($bp['basicPath'], null, $excludeHost) . $action;
                }
            }

        return $path;

    }

    public function linkCreator($basicPath, $domain = null, $excludeHost = false)
    {
        static $resolvedDomains;

        
        if (!$this->nativeLangVersion) {
            reset($basicPath);
            $domainKey = key($basicPath);
            $startPage = $this->_tree->readNodeParam($domainKey, 'StartPage');
            $startPage = $this->_tree->getNodeStruct($startPage);
            $resolvedDomains[$domainKey] = $startPage['basic'];;
            $this->nativeLangVersion = $startPage['basic'];

        }

        if (!$domain) {
            $domain = xConfig::get('GLOBAL', 'DOMAIN');
        }

        if (!$excludeHost) {
            $link = PROTOCOL . '://' . $domain;
        }

    
        $lang = array_slice($basicPath, 1, 1);


        if ($this->nativeLangVersion != $lang[0]) {
            $link .= '/' . $lang[0];
        }

        if ($basicPath) {
            $bPath = array_slice($basicPath, 2);
            $link .= '/' . implode('/', $bPath);
        }

        return $link;
    }

    public function boostTree($params)
    {
        if ($this->_config['boostTree']) {
            $this->_tree->startBooster();
            $this->_tree->boostById(1);

        }

    }

    public function defineFrontActions()
    {

        $this->defineAction('showLevelMenu');
        $this->defineAction('showPath');
        $this->defineAction('showUserMenu');
    }


}