<?php

namespace X4\Classes;

class MultiSectionHL
{

    var $screenedFields = array();
    var $Extended = array();
    var $Fields = array();
    var $Replacement = array();
    var $MFReplacement = array();
    var $MFFields = array();
    var $maindata = array();
    var $sectionNests = array();
    var $blockedparseSection = array();
    var $packOutput = false;
    var $fastReplace = array();
    var $MainFields = array();
    var $callFunc = array();
    var $sectionOverride = false;
    var $potentialKeys = array();
    var $noprsVals;
    var $timemark = 0;
    var $aifs;
    var $innerSectionNests;
    var $currentFile;
    var $ifstateBlock;
    var $currentRA;


    public $templatesAlias = array();

    public function __construct()
    {
        XNameSpaceHolder::addMethodsToNS('TMS', array('_each'), $this);
        XNameSpaceHolder::addMethodsToNS('TMS', array('send', 'load', 'parse'), $this);
        $this->timemark = \Common::generateHash(\Common::getmicrotime());
    }


    public function parse($params, $data)
    {
        return $this->send($params, $data);
    }

    public function send($params, $data)
    {

        if ($data['TMS']) $TMS = $data['TMS']; else $TMS = $this;
        if ($TMS->isSectionDefined($params['section'])) {
            $TMS->addMassReplace($params['section'], $params['values']);
            return $TMS->parseSection($params['section']);

        } else {
            trigger_error('trying to send to non-existing section - | ' . $params['section'] . '|', E_USER_WARNING);
        }
    }

    public function isSectionDefined($section)
    {
        if (in_array($section, array_keys($this->maindata))) {
            return true;
        }
    }

    public function addMassReplace($section, $arr)
    {
        if (isset($arr) && is_array($arr)) {
            foreach ($arr as $key => $val) {
                $this->addReplace($section, $key, $val);
            }
        }
    }

    public function addReplace($section, $addF, $addR)
    {
        $this->Fields[$section][$addF] = $addR;
    }


    public function parseSection($section)
    {

        return $this->Fields[$section];

    }


    public function getAllCurrentReplacements($section)
    {
        $repArray = array();
        $gsrRepArray = array();

        if (isset($this->potentialKeys[$section])) {


            foreach ($this->potentialKeys[$section] as $k => $v) {
                $repArray['{F:' . $k . '}'] = $v;
            }
        }

        if ($sr = $this->getSectionReplacements($section, '{F:', '}')) {
            $gsrRepArray = array_combine($sr, $this->Fields[$section]);
        }

        $repArray = array_merge($repArray, $gsrRepArray);

        return $repArray;
    }

    public function getSectionReplacements($section, $s = '', $e = '')
    {
        if (!empty($this->Replacement[$section])) {
            foreach ($this->Replacement[$section] as $repl) {
                $ext[] = $s . substr($repl, 4, strlen($repl) - 6) . $e;
            }
            return $ext;
        }
    }


    public function addMFReplace($addMF, $addMR, $glue = false)
    {
        if ($strop = strpos($addMF, '%->:')) {
            $fkey = array_search($addMF, $this->MFReplacement);
        } else {
            $fkey = array_search('{%MF:' . $addMF . '%}', $this->MFReplacement);
        }
        if ($fkey !== false) {
            if ($glue) {
                $this->MFFields[$fkey] .= $addMR;

            } else {
                $this->MFFields[$fkey] = $addMR;

            }
        }
    }


    public function nullSection($section)
    {
        if ($this->maindata[$section]) {
            $this->maindata[$section] = null;
        }
    }

    public function clearSectionFelds($section, $el = '')
    {
        if ($this->Fields[$section]) {
            $this->Fields[$section] = array_fill(0, count($this->Fields[$section]), $el);
        }
    }

    public function delSection($section, $is_prefix = null)
    {
        if (is_array($section)) {
            foreach ($section as $sec_item) {
                if ($this->maindata[$sec_item]) {
                    unset($this->maindata[$sec_item]);
                    unset($this->Replacement[$sec_item]);
                    unset($this->Fields[$sec_item]);
                }
            }
        } elseif ($is_prefix) {
            $existing_section = array_keys($this->maindata);
            foreach ($existing_section as $existing_item) {
                if (strpos($existing_item, $section) === 0) {
                    unset($this->maindata[$existing_item]);
                    unset($this->Replacement[$existing_item]);
                    unset($this->Fields[$existing_item]);
                    unset($this->fastReplace[$existing_item]);
                }
            }
            foreach ($this->MFReplacement as $num => $value) {
                if (strpos($this->MFReplacement[$num], '{%->:' . $section) === 0) {
                    unset($this->MFReplacement[$num]);
                    unset($this->MFFields[$num]);
                }
            }
        } else {
            if ($this->maindata[$section]) {
                unset($this->maindata[$section]);
                unset($this->Replacement[$section]);
                unset($this->Fields[$section]);
                unset($this->Extended[$section]);
            }
        }
    }

    public function returnData()
    {
        return $this->maindata;
    }

    public function getMFSectionReplacements()
    {

        if (isset($this->MFReplacement)) {
            $ext = [];
            foreach ($this->MFReplacement as $repl) {
                preg_match("/{%->:(.*?)%}/", $repl, $match);
                $ext[] = $match[1];
            }
            return $ext;
        }
    }

    public function getFastReplace()
    {
        $fr = array_keys($this->fastReplace);

        if (!empty($fr)) {
            foreach ($fr as $v) {
                $f[] = str_replace('@', '', $v);
            }
            return $f;
        }

        return null;
    }

    public function killField($section, $Repl)
    {
        $fkey = $this->findReplacement($section, $Repl);
        if ($fkey !== false) {
            $this->Fields[$section][$fkey] = "";
        }
    }

    public function killMFields($FastSection)
    {
        $fkey = array_search('{%->:' . $FastSection . '%}', $this->MFReplacement);
        if ($fkey !== false) {
            $this->MFFields[$fkey] = '';
        }
    }

    public function addMFMassReplace($arr)
    {
        if ($arr) {
            foreach ($arr as $key => $val) {
                $this->addMFReplace($key, $val);
            }
        }
    }

    public function setSectionOverride($state)
    {
        $this->sectionOverride = $state;
    }

    public function generateSection($text, $sectionName)
    {
        $this->addFileSection('{%section:' . $sectionName . "%}\r\n" . $text . "\r\n{%endsection:" . $sectionName . '%}', true);
    }

    public function addFileSection($filename, $astext = false)
    {
        if (is_array($filename)) {
            $this->processIncluded($filename);
        } elseif (file_exists($filename)) {
            $this->currentFile = $filename;
            $this->processIncluded(file($filename));

            if (isset($this->templatesAlias[$filename])) return $this->templatesAlias[$filename];

        } elseif ($astext) {
            $this->processIncluded(explode("\n", $filename));

        } else {

            throw new Exception('Template not found -> ' . $filename);
        }
    }

    public function processIncluded($lines)
    {

    }

    public function createSection($sectionInfo)
    {
        if ((!$this->isSectionDefined($sectionInfo[1])) or ($this->sectionOverride)) {
            $sectionName = $sectionInfo[1];

            if (isset($sectionInfo[2]) && ($sectionInfo[2] == '->%}')) {
                $this->fastReplace[$sectionName] = $sectionName;


            } elseif (isset($sectionInfo[3])) {
                $this->fastReplace[$sectionName] = $sectionInfo[3];

            }
            $this->maindata[$sectionName] = '';

            return true;
        } else {
            unset($this->aifs[$sectionInfo[1]]);
        }
    }

    public function processSectionVars($sectionName)
    {

    }

    public function parseFuncValues($text, $sectionName, $ifsState = false)
    {


    }

    public function addToSection($sectionName, $text)
    {
        if ($this->isSectionDefined($sectionName)) {
            $this->maindata[$sectionName] .= $text;

        }
    }


    public function load($params)
    {

        if (!isset($params['prefix']) or !$defaultPath = \xConfig::get('PATH', $params['prefix'])) {
            $defaultPath = \xConfig::get('PATH', 'TEMPLATES');
        }

        if ($adm = XRegistry::get('ADM')) {

            $cnt = $adm->tplLangConvert(null, $defaultPath . $params['path'], $params['module']);

        } else {
            $cnt = file_get_contents($defaultPath . $params['path']);
        }

        $this->addFileSection($cnt, true);
    }

    public function addMassFileSection($fileSections)
    {
        if (is_array($fileSections)) {
            foreach ($fileSections as $fileSection) {
                $this->addFileSection($fileSection);
            }
        }
    }


}