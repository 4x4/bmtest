<?php

namespace X4\Classes;

class Install
{

    public $pagesTree;
    public $moduleInstalls=array();
    public $installedDomain;
    
    public function __construct()
    {
        $this->pagesTree = new XTreeEngine('pages_container', xRegistry::get('xPDO'));
    }


    public function getCurrentInstalledDomains()
    {
        return $this->pagesTree->selectStruct('*')->selectParams('*')->where(array('@obj_type', '=', '_DOMAIN'))->run();
    }

    
    public function runModuleInstallers()
    {
        $modules = \xCore::discoverModules();
        
        if (!empty($modules)) {
            foreach ($modules as $module) {


                $installClass = \xConfig::get('PATH','MODULES').$module['name'].'/install/'.$module['name'].'.install.php';

                if (file_exists($installClass)) 
                    {
                        \xCore::callCommonInstance($module['name']);
                        include_once $installClass;
                        $classname = $module['name'].'Install';
                        $this->moduleInstalls[$module['name']] = new $classname();
                        $this->moduleInstalls[$module['name']]->run($this->installedDomain);
                    }
            }
        }
    
    }

    public function transformDomains($domains)
    {

        $installedDomains = $this->getCurrentInstalledDomains();
        $installedDomains = \XARRAY::arrToKeyArr($installedDomains, 'basic', 'id');

        foreach ($domains as $src => $dest) {
            if ($id = $installedDomains[$src]) {
                if (\xCore::checkHostDomain($dest)) {
                    $this->pagesTree->setStructData($id, 'basic', $dest);
                    $this->installedDomain=$dest;
                } else {
                    $notAccessedDomain[] = $dest;
                }
            }

            if (isset($notAccessedDomain)) return $notAccessedDomain;

        }
    }
}
