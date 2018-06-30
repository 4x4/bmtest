<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class showCorrespodingTagsAction extends xAction

{
    public function run($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);

        $PDO = XRegistry::get('XPDO');

        $categories = $this->serverParams['params']['Categories'];

        $category = '';


        if (!empty($category)) {
            $category = "and FIND_IN_SET('" . implode(',', $categories) . "',`categories`)";
        }

        $query = "select tags FROM news WHERE news_date<" . time() . " and active = 1 $category group by tags";


        if ($pdoResult = $PDO->query($query)) {
            $tagsMatrix = array();

            while ($pf = $pdoResult->fetch(PDO::FETCH_ASSOC)) {
                if ($tag = $pf['tags']) {
                    $tagsMatrix = array_merge($tagsMatrix, json_decode($pf['tags']));

                }

            }

            if (!empty($tagsMatrix)) {
                $newsServerPage = $this->createPageDestination($params['params']['DestinationPage']);
                $this->_TMS->addMassReplace('showCorrespodingTags', array('tags' => $this->tagsAgregate($tagsMatrix, $newsServerPage)));
                return $this->_TMS->parseSection('showCorrespodingTags');
            }

        }
    }
}

?>

