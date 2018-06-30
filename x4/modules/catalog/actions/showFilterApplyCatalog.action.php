<?php

use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showFilterApplyCatalogAction extends xAction
{


    public function run($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);

        $section = 'showFilterApplyCatalog';

        $filter['applyFilterOnSku'] = $params['params']['applyFilterOnSku'];

        if (isset($params['params']['filter'])) {

            $filter['filterPack'] = json_decode($params['params']['filter'], true);

            if ($filter['filterPack']['onpage']) {
                $filter['onpage'] = $filter['filterPack']['onpage'];
                unset($filter['filterPack']['onpage']);

            }

            $filter['serverPageDestination'] = $this->createPageDestination($params['params']['DestinationPage']);
            $pages = xCore::loadCommonClass('pages');
            if ($module = $pages->getModuleByAction($params['params']['DestinationPage'], 'showCatalogServer')) {
                $filter['showBasicPointId'] = $module['params']['showBasicPointId'];
            }


        } else {

            return;
        }


        $catObjects = $this->selectObjects($filter);

        if ($catObjects['count'] > 0) {

            $this->_TMS->addMassReplace($section,
                array(
                    'count' => $catObjects['count'],
                    'objects' => $catObjects['objects']
                )
            );
        } else {
            $this->_TMS->parseSection('catalogEmpty', true);

        }

        return $this->_TMS->parseSection($section);

    }

}
