<?php
use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;
use X4\Classes\ImageRenderer;

class newsCommon
    extends xModuleCommon implements xCommonInterface
{

    public $_useTree = true;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->_tree->setObject('_ROOT', array('Name'));
        $this->_tree->setObject('_NEWSGROUP', array
        (
            'Name',
            'commentsTread'
        ), array('_ROOT'));

    }


    public    function defineFrontActions()
    {
        $this->defineAction('showNewsInterval');
        $this->defineAction('showNewsCategories');
        $this->defineAction('showCorrespodingTags');
        $this->defineAction('newsServer', array('serverActions' => array('showNewsCategories', 'tag', 'showNewsInterval', 'showNews')));
    }


    public function selectNewsInterval($category, $startRow = 0, $rowsNum = '', $where = '', $order = 'DESC', $countOnly = false, $hideOldNews = false)
    {


        if (!empty($category)) {

            $category = "and FIND_IN_SET('" . implode(',', $category) . "',`categories`)";
        } else {

            $category = '';
        }


        if ($countOnly) {
            $count = "count(*) as ccount ";
            $rowsNum = '';

        } else {

            $count = "*";
        }

        $limit = ($rowsNum) ? " LIMIT $startRow, $rowsNum" : '';
        $OldNews = $hideOldNews ? 'AND news_end NOT BETWEEN 1 AND ' . time() . '' : '';

        $PDO = XRegistry::get('XPDO');

        $query = "select $count FROM news WHERE news_date<" . time() . " " . $OldNews . "  and active = 1 $category $where ORDER BY news_date $order " . $limit;


        if (($pdoResult = $PDO->query($query)) && (!$countOnly)) {
            while ($pf = $pdoResult->fetch(PDO::FETCH_ASSOC)) {
                $news[] = $pf;
            }

            return $news;

        } elseif ($pdoResult && $countOnly) {

            $result = $pdoResult->fetch(PDO::FETCH_ASSOC);

            return $result['ccount'];
        }
    }

    public function selectNewsById($id)
    {
        $PDO = XRegistry::get('XPDO');

        $query = 'SELECT * FROM `news` WHERE id="' . $id . '"';

        if ($pdoResult = $PDO->query($query)) {
            return $pdoResult->fetch(PDO::FETCH_ASSOC);

        }
    }

    public function selectNews($basic)
    {

        $PDO = XRegistry::get('XPDO');

        $query = 'SELECT * FROM `news` WHERE basic="' . $basic . '"';

        if ($pdoResult = $PDO->query($query)) {

            return $pdoResult->fetch(PDO::FETCH_ASSOC);

        }
    }


    function getCategories()
    {

        return $this->_tree->selectStruct(array('id', 'basic'))->selectParams('*')->childs(1, 1)->run();

    }


}