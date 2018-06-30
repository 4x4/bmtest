<?php

use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;

class showCurrentSelectedFiltersAction extends xAction
{
    public function retrievePropertiesInfo($request)
    {
        if (!empty($request)) {
            foreach ($request as $tree => $reqTree) {
                $fieldsData[$tree] = array();
                foreach ($reqTree as $action => $fields) {
                    $fieldsData[$tree] = array_unique(array_merge($fieldsData[$tree], array_keys($fields)));
                }
            }
            if (!empty($fieldsData['f'])) {
                foreach ($fieldsData['f'] as $field) {
                    $psetEx = explode('.', $field);
                    $psetTokens[$psetEx[0]][] = $psetEx[1];
                }
            }
            if ($plist = $this->_commonObj->_propertySetsTree->selectStruct('*')->selectParams(array(
                'isSKU'
            ))->childs(1, 1)->run()
            ) {
                $globalPsets = array();
                foreach ($plist as $groupNode) {
                    if (isset($psetTokens[$groupNode['basic']])) {
                        if (isset($fPsets[$groupNode['basic']]))
                            $fPsets[$groupNode['basic']] = array();
                        $sets = $this->_commonObj->_propertySetsTree->selectStruct('*')->selectParams('*')->where(array(
                            '@ancestor',
                            '=',
                            $groupNode['id']
                        ), array(
                            '@basic',
                            '=',
                            $psetTokens[$groupNode['basic']]
                        ))->format('keyval', 'basic')->run();
                        if ($sets) {
                            foreach ($sets as $setName => $setVal) {
                                $globalPsets[$groupNode['basic'] . '.' . $setName] = $setVal;
                            }
                        }
                        $fPsets[$groupNode['basic']] = array_merge($fPsets[$groupNode['basic']], $sets);
                    }
                    if (!empty($fieldsData['s']) && $groupNode['params']['isSKU']) {
                        $sets = $this->_commonObj->_propertySetsTree->selectStruct('*')->selectParams('*')->where(array(
                            '@ancestor',
                            '=',
                            $groupNode['id']
                        ), array(
                            '@basic',
                            '=',
                            $fieldsData['s']
                        ))->format('keyval', 'basic')->run();
                        if ($sets)
                            $globalPsets = array_merge($globalPsets, $sets);
                    }
                }
                return $globalPsets;
            }
        }
    }


    public function handleValue($value, $type)
    {
        switch ($type) {
            case 'selector':
                $value = $this->_tree->readNodeParam($value, 'Name');
                break;
        }
        return $value;
    }


    public function run($params)
    {
        $fields = $this->retrievePropertiesInfo($params['request']['requestData']);
        if (!empty($fields)) {
            $this->loadModuleTemplate($params['params']['Template']);
            $tpl = xTpl::__load($this->_moduleName);
            foreach ($params['request']['requestData'] as $tree => $reqTree) {
                foreach ($reqTree as $type => $fieldsMap) {
                    foreach ($fieldsMap as $comparsionField => $comparsionValue) {
                        if (is_array($comparsionValue)) {
                            foreach ($comparsionValue as &$val) {
                                $val = urldecode($val);

                                $link = $tpl->removeFilter(array(
                                    "tree" => $tree,
                                    "filter" => array(
                                        array(
                                            "type" => $type,
                                            'value' => $val,
                                            "property" => $comparsionField
                                        )
                                    )
                                ));


                                $val = array(
                                    'link' => $link,
                                    'value' => $this->handleValue($val, $fields[$comparsionField]['params']['type'])
                                );
                            }
                        } else {
                            $comparsionValue = $this->handleValue($comparsionValue, $fields[$comparsionField]['params']['type']);
                        }
                        if (is_array($comparsionValue))
                            $add = true;
                        else
                            $add = false;
                        $data = array(
                            'link' => $tpl->removeFilter(array(
                                "add" => $add,
                                "tree" => $tree,
                                "filter" => array(
                                    array(
                                        "type" => $type,
                                        "property" => $comparsionField
                                    )
                                )
                            )),
                            'property' => $fields[$comparsionField]['basic'],
                            'propertyName' => $fields[$comparsionField]['params']['alias'],
                            'propertyType' => $fields[$comparsionField]['params']['type'],
                            'value' => $comparsionValue
                        );
                        if ($this->_TMS->isSectionDefined($type . '-' . $data['propertyType'])) {
                            $section = $type . '-' . $data['propertyType'];
                        } else {
                            $section = $type;
                        }
                        $this->_TMS->addMassReplace($section, $data);
                        $html[$type . '.' . $comparsionField] = $this->_TMS->parseSection($section);
                    }
                }
            }
            $this->_TMS->addMassReplace('showCurrentSelectedFilters', 'selectedFilters', $html);
            $this->_TMS->addReplace('showCurrentSelectedFilters', 'selectedFilters', implode("\r\n", $html));
            return $this->_TMS->parseSection('showCurrentSelectedFilters');
        }
    }
}
