<?php

class searchFront
    extends xModule
{
    public function __construct()
    {
        parent::__construct(__CLASS__);

    }


    /**
     * Возвращает все словоформы слов поискового запроса
     */
    static function Words2AllForms($text)
    {
        require_once(xConfig::get('PATH', 'EXT') . 'phpMorphy/src/common.php');

        $opts = array
        (
            //            PHPMORPHY_STORAGE_FILE - использует файловые операции (fread, fseek) для доступа к словарям
            //            PHPMORPHY_STORAGE_SHM - загружает словари в общую память (используя расширение PHP shmop)
            //            PHPMORPHY_STORAGE_MEM - загружает словари в память
            'storage' => PHPMORPHY_STORAGE_MEM,
            //            Extend graminfo for getAllFormsWithGramInfo method call
            'with_gramtab' => false,
            'predict_by_suffix' => true,
            'predict_by_db' => true
        );

        $encoding = 'utf8';
        $dir = xConfig::get('PATH', 'EXT') . 'phpMorphy/dicts/';

        //        Создаем объект словаря
        $dict_bundle = new phpMorphy_FilesBundle($dir, 'rus');
        $morphy = new phpMorphy($dict_bundle, $opts);

        //        $codepage = $morphy->getCodepage();
        setlocale(LC_CTYPE, array
        (
            'ru_RU.CP1251',
            'Russian_Russia.1251'
        ));

        $words = preg_split('#\s|[,.:;!?"\'()]#', $text, -1, PREG_SPLIT_NO_EMPTY);

        $bulkWords = array();

        $bulkWords = array();

        foreach ($words as $v) {
            if (strlen($v) > 3) {
                $v = iconv("UTF-8", "windows-1251", $v);
                $bulkWords[] = strtoupper($v);
            }
        }

        return $morphy->getAllForms($bulkWords);
    }

    public function searchServer($params)
    {

        $pInfo = XRegistry::get('TPA')->getRequestActionInfo();

        if (!$pInfo['requestAction']) {
            $params['_Action'] = $params['params']['secondaryAction'];

            if ($params['secondary']) {
                $params['params'] = $params['secondary'];
                unset($params['secondary']);
                return $this->execute($params, $params['base']['moduleId']);
            }

        }
    }


    public function likeSearch($multipleWords, $limit)
    {

        if (!empty($multipleWords)) {
            foreach ($multipleWords as $word) {
                $where[] = "body LIKE '%" . $word . "%'";
                $where[] = "title LIKE '%" . $word . "%'";
            }

            $where = implode(' OR ', $where);


            $q = 'SELECT SQL_CALC_FOUND_ROWS id, url, title, body FROM search_pages_index WHERE ' . $where
                . ' AND STATUS = 200 GROUP BY url LIMIT ' . $limit;

            $pdoResult = XRegistry::get('XPDO')->query($q);

            $tmpResults = $pdoResult->fetchAll(PDO::FETCH_ASSOC);


            if ($pdoResult = XRegistry::get('XPDO')->query('SELECT FOUND_ROWS() as rows')) {
                $result = $pdoResult->fetchAll(PDO::FETCH_ASSOC);
                $tmpCounter = $result[0]['rows'];

            }

            return array('results' => $tmpResults, 'counter' => $tmpCounter);
        }
    }


    public function matchAgainstSearch($word, $words, $limit)
    {
        if (is_array($words)) {


            $query = 'SELECT SQL_CALC_FOUND_ROWS id, url, title, body, MATCH (`title`, `body`,`index`) AGAINST (\'>' . $word . '  <(' . implode(' ', $words) . ')\' IN BOOLEAN MODE) as rel 
                                FROM search_pages_index
                                WHERE MATCH (`title`, `body`,`index`) AGAINST (\'>' . $word . '  <(' . implode(' ', $words) . ')\' IN BOOLEAN MODE)
                                AND status = 200 LIMIT ' . $limit;

            if ($pdoResult = XRegistry::get('XPDO')->query($query)) {
                $tmpResults = $pdoResult->fetchAll(PDO::FETCH_ASSOC);

            }

            if ($pdoResult = XRegistry::get('XPDO')->query('SELECT FOUND_ROWS() as rows')) {
                $result = $pdoResult->fetchAll(PDO::FETCH_ASSOC);
                $tmpCounter = $result[0]['rows'];

            }

            return array('results' => $tmpResults, 'counter' => $tmpCounter);


        }

    }


    public function find($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);

        if ($_GET['word']) {

            if ($words = trim(urldecode($_GET['word']))) {

                $currPage = $_GET['page'] ? (int)$_GET['page'] : 0;
                $start = $currPage ? (int)$currPage : 0;


                $forms = array();
                $wordsForms = searchFront::Words2AllForms($words);


                while (list(, $val) = each($wordsForms)) {
                    if ($val) {
                        foreach ($val as &$item) {
                            $item = iconv('windows-1251', 'UTF-8', $item);
                        }

                        $forms = array_merge($forms, (array)$val);

                    }
                }


                ($params['params']['onPage']) ? $limit = $start . "," . $params['params']['onPage'] : $limit = $start;

                if (!empty($forms)) {
                    $multipleWords = $forms;

                } else {

                    $multipleWords = explode(' ', $words);
                }


                $results = $this->matchAgainstSearch($words, $multipleWords, $limit);


                if (empty($results['results'])) {
                    $results = $this->likeSearch($multipleWords, $limit);
                }


                if (!empty($results['results'])) {

                    $destinationLink = $this->createPageDestination(XRegistry::get('TPA')->currentPageNode['id'], false, 'find');

                    Common::parseNavPages($results['counter'], $params['params']['onPage'],
                        $currPage, $destinationLink . '/~find',
                        $this->_TMS, 'page');

                    $i = $start;


                    foreach ($results['results'] as $result) {
                        $resBody = '';
                        $result['num'] = ++$i;

                        if ($extract = $this->extractSentence($result['body'], $multipleWords)) {
                            $body = $extract;
                        }


                        //$result['body'] = $this->cutWordsRu2($body, 600, '<span class="blue_bg">','</span>');                                
                        $result['body'] = XSTRING::findnCutSymbolPosition($result['body'], " ", 100);

                        $result['title'] = (empty($result['title'])) ? $result['url'] : $result['title'];

                        $this->_TMS->addMassReplace('searchResult', $result);
                        $this->_TMS->parseSection('searchResult', true);

                    }


                    $query = strip_tags(htmlspecialchars($_GET['word']));

                    $this->_TMS->addMassReplace('searchResults', array
                    (
                        'query' => $query,
                        'query_num' => $results['counter'],
                        'start' => $start + 1
                    ));

                    return $this->_TMS->parseSection('searchResults');
                } else {
                    $query = strip_tags(htmlspecialchars($words));
                    $this->_TMS->addMassReplace('searchNothingFound', array('query' => $query));
                    return $this->_TMS->parseSection('searchNothingFound');
                }


            } else {

                $this->_TMS->addMassReplace('searchEmptyInput', array('query' => ''));
                return $this->_TMS->parseSection('searchEmptyInput');
            }
        }
    }

    function utf8_strtolower($string)
    {
        $convert_to = array
        (
            "a",
            "b",
            "c",
            "d",
            "e",
            "f",
            "g",
            "h",
            "i",
            "j",
            "k",
            "l",
            "m",
            "n",
            "o",
            "p",
            "q",
            "r",
            "s",
            "t",
            "u",
            "v",
            "w",
            "x",
            "y",
            "z",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "?",
            "c",
            "e",
            "e",
            "e",
            "e",
            "i",
            "i",
            "i",
            "i",
            "?",
            "n",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "u",
            "u",
            "u",
            "u",
            "y",
            "а",
            "б",
            "в",
            "г",
            "д",
            "е",
            "ё",
            "ж",
            "з",
            "и",
            "й",
            "к",
            "л",
            "м",
            "н",
            "о",
            "п",
            "р",
            "с",
            "т",
            "у",
            "ф",
            "х",
            "ц",
            "ч",
            "ш",
            "щ",
            "ъ",
            "ы",
            "ь",
            "э",
            "ю",
            "я"
        );

        $convert_from = array
        (
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "?",
            "C",
            "E",
            "E",
            "E",
            "E",
            "I",
            "I",
            "I",
            "I",
            "?",
            "N",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "U",
            "U",
            "U",
            "U",
            "Y",
            "А",
            "Б",
            "В",
            "Г",
            "Д",
            "Е",
            "Ё",
            "Ж",
            "З",
            "И",
            "Й",
            "К",
            "Л",
            "М",
            "Н",
            "О",
            "П",
            "Р",
            "С",
            "Т",
            "У",
            "Ф",
            "Х",
            "Ц",
            "Ч",
            "Ш",
            "Щ",
            "Ъ",
            "Ы",
            "Ь",
            "Э",
            "Ю",
            "Я"
        );

        return str_replace($convert_from, $convert_to, $string);
    }

    /**
     * Возвращает первое предложение с подсвеченным словом
     */
    function extractSentence($string = '', $words = array())
    {
        setlocale(LC_CTYPE, 'ru_RU.UTF8', 'ru.UTF8', 'ru_RU.UTF-8', 'ru.UTF-8');

        if (is_string($words))
            $words = explode(' ', $words);

        //unset($words[0]);

        $words = array_values($words);
        $string = (!trim($string) ? "" : $string);
        //debugbreak();
        if (!empty($words) && is_array($words) && !empty($string)) {
            $string = strtolower($string); //mb_strtolower $string= mb_strtolower($string, 'UTF-8');
            //$sentences=preg_split('%[\.\!\?]%', $string);
            $sentences = preg_split('%\s{2,}|\.\s+%', $string);


            foreach ($words as $word) {
                $word = $this->utf8_strtolower($word);
                //$patterns[]    ="/(?<=\s).*?$word.*?(?=\s)/s";
                $patterns[] = "/(?<=\s|^)\S*?$word\S*?(?=\s|,|\.|$)/s";
                $replacements[] = '<span class="blue_bg">\\0</span>';
            }

            if (!$sentences) {
                foreach ($words as $word) {
                    $word = $this->utf8_strtolower($word, 'UTF-8');
                    $word = preg_quote($word);

                    if (preg_match("/$word/s", $string, $matches)) {
                        return preg_replace($patterns, $replacements, $string);
                    }
                }

                return false;
            }
            //debugbreak();
            foreach ($sentences as $item) {
                $item = trim($item);
                $item = $this->utf8_strtolower($item);
                $word = preg_quote($word);

                $matchFlag = false;
                foreach ($words as $i => $word) {
                    $word = $this->utf8_strtolower($word);

//                    $count = null;
//$returnValue = str_replace("$this->utf8_strtolower('стац')", '<span class="highlight">'.$this->utf8_strtolower('стац').'</span>', $this->utf8_strtolower('Кабели предназначены для монтажа низкочастотного стационарного оборудования.'));

                    if (preg_match("/$word/", $item, $matches) && !$matchFlag) {
                        $item = preg_replace($patterns, $replacements, $item);
                        //$item = str_replace($word, '<span class="blue_bg">'.$word.'</span>',$item);
                        $matchFlag = true;
                    }
                }

                if ($matchFlag) {
                    $tmp[] = $item;
                }

            }
            return $tmp;
        }

        return false;
    }

    function extractSentence2($string = '', $words = array())
    {
        setlocale(LC_CTYPE, 'ru_RU.UTF8', 'ru.UTF8', 'ru_RU.UTF-8', 'ru.UTF-8');

        if (is_string($words))
            $words = explode(' ', $words);

        unset($words[0]);
        $words = array_values($words);
        $string = (!trim($string) ? "" : $string);

        if (!empty($words) && is_array($words) && !empty($string)) {
            //$string= strtolower($string); //mb_strtolower $string= mb_strtolower($string, 'UTF-8');
            $sentences = preg_split('%[\.\!\?]%', $string);

            foreach ($words as $word) {
                $word = $this->utf8_strtolower($word);
                $patterns[] = "/($word)/s";
                $replacements[] = '<span class="highlight">\\1</span>';
            }

            if (!$sentences) {
                foreach ($words as $word) {
                    $word = $this->utf8_strtolower($word, 'UTF-8');
                    $word = preg_quote($word);

                    if (preg_match("/$word/s", $string, $matches)) {
                        return preg_replace($patterns, $replacements, $string);
                    }
                }

                return false;
            }

            foreach ($sentences as $item) {
                $item = trim($item);
                $item = $this->utf8_strtolower($item);
                $word = preg_quote($word);

                foreach ($words as $word) {
                    $word = $this->utf8_strtolower($word);

                    if (preg_match("/$word/", $item, $matches)) {
                        $tmp = preg_replace($patterns, $replacements, $item);
                        return preg_replace($patterns, $replacements, $item);
                    }
                }
            }
        }

        return false;
    }

    public function showSearchForm($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);
        $destinationLink = $this->createPageDestination($params['params']['DestinationPage'], false, 'find');
        $this->_TMS->AddReplace('xtr_search', 'action', $destinationLink);
        return $this->_TMS->parseSection('xtr_search');
    }

    function cutWordsRu2($str0, $maxlength, $opentag, $closetag)
    {
        //DebugBreak();       
        $str = (strip_tags(iconv("UTF-8", "WINDOWS-1251", $str0)));
        $v = strlen($str);

        if (strlen($str) <= $maxlength)
            return stripslashes($str0);

        $str = ((iconv("UTF-8", "WINDOWS-1251", $str0)));

        $str = str_replace($opentag, '<', $str);
        $str = str_replace($closetag, '>', $str);

        $strReady = substr($str, 1, $maxlength);
        $i = $maxlength;

        while ((strpos($strReady, '>') == 0) || (substr_count($strReady, '<') != substr_count($strReady, '>'))) {
            $strReady .= $str[$i++];

            if ($i + 1 == strlen($str))
                break;
        }

        while ($str[$i] != ' ' && 1 + $i != strlen($str)) {
            $strReady .= $str[$i++];
        }

        while (strlen($strReady) > 1.2 * $maxlength) {
            $i = 0;

            while ($strReady[$i] != ' ') {
                $i++;
            }

            $strReady = substr($strReady, 0, $i);
        }

        $strReady = str_replace('<', $opentag, $strReady);
        $strReady = str_replace('>', $closetag, $strReady);

        $strReady = iconv("WINDOWS-1251", "UTF-8", $strReady);

        return stripslashes($strReady);
    }
}

class IndexSearch
{
    // Таблица ,в которой происходит поиск
    private $_table;
    // Фраза по которой происходит поиск
    private $_keyword;
    // Поля таблицы , которые будут выведены в результате
    private $_resultFields = array();
    // Поля таблицы , в которых происходит поиск
    private $_searchFields = array();
    // Название поля , по которому происходит объединение результатов от разных типов поиска
    private $_resultMergeField;
    // Ограничение на количество результатов поиска
    private $_resultLimit;
    // Номер результата поиска , начиная с которого возвращается выдача
    private $_beginSegm;
    // Фильтр результатов поиска
    private $_filter = array();
    // Счетчик результатов
    private $_counter = null;
    private $_DB;

    public function __construct($params)
    {
        $this->_table = $params['table'];
        $this->_resultFields = $params['result_fields'];
        $this->_searchFields = $params['search_fields'];
        $this->_resultMergeField = $params['merge_result_by'];
        $this->setKeyword($params['keyword']);
        $this->_beginSegm = $params['start'];
        $this->_resultLimit = $params['onPage'];
        $this->_filter = $params['filter'];
        global $TDB;
        $this->_DB = $TDB;
    }

    public function setKeyword($value)
    {
        $this->_keyword = $value;
        $this->_keyword = trim(mysql_real_escape_string(urldecode($this->_keyword)));
    }

    private function _getResultFieldsQueryPart()
    {
        return implode(', ', $this->_resultFields);
    }

    private function _getLimitQueryPart()
    {
        if (!empty($this->_beginSegm) && !empty($this->_resultLimit)) {
            return 'LIMIT ' . $this->_beginSegm . ',' . $this->_resultLimit;
        }

        return '';
    }

    private function _getFilterQueryPart()
    {
        if (empty($this->_filter)) {
            return '';
        } else {
            return 'AND status = 200';
        }
    }

    private function _getKeywordParts()
    {
        $parts = explode(' ', $this->_keyword);

        if (empty($parts)) {
            $parts[] = $this->_keyword;
        }

        return $parts;
    }

    private function _getKeywordsForMatchAgainstSearch()
    {
        $wordforms = $this->_wordsToWordsForms($this->_keyword);
        $keywordParts = $this->_getKeywordParts();
        //            foreach ($keywordParts as $key => $keywordPart) {
        //                if ( mb_stristr($wordforms, $keywordPart,false,'utf-8') ) {
        //                    unset($keywordParts[$key]);
        //                } 
        //            }
        return $wordforms; //.' '.mb_strtoupper( implode(' ',$keywordParts), 'utf-8' );
    }

    private function _matchAgainstSearch()
    {
        $fieldsDecorated = array();

        foreach ($this->_searchFields as $field) {
            $fieldsDecorated[] = '`' . $field . '`';
        }

        $keyword = $this->_getKeywordsForMatchAgainstSearch();

        $query = 'SELECT SQL_CALC_FOUND_ROWS ' . $this->_getResultFieldsQueryPart() . ' FROM ' . $this->_table
            . ' WHERE MATCH (' . implode(
                ', ',
                $fieldsDecorated) . ')' . ' AGAINST (\'' . $keyword . '\')'
            . $this->_getFilterQueryPart() . ' ' . $this->_getLimitQueryPart();

        return $this->_DB->get_results($query);
    }

    private function _likeSearch()
    {
        $whereParts = array();

        foreach ($this->_searchFields as $field) {
            $whereParts[] = $field . ' LIKE ' . "'%$this->_keyword%'";
        }

        if (count($whereParts) > 1) {
            $whereQueryPart = implode(' OR ', $whereParts);
        } else {
            $whereQueryPart = $whereParts;
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS ' . $this->_getResultFieldsQueryPart() . ' FROM ' . $this->_table . ' WHERE '
            . $whereQueryPart . ' ' . $this->_getFilterQueryPart() . ' ' . $this->_getLimitQueryPart();
        return $this->_DB->get_results($query);
    }

    private function _rlikeSearch()
    {
        $whereParts = array();

        foreach ($this->_searchFields as $field) {
            $whereParts[] = $field . ' RLIKE ' . "'[[:<:]]$this->_keyword" . "[[:>:]]'";
        }

        if (count($whereParts) > 1) {
            $whereQueryPart = implode(' OR ', $whereParts);
        } else {
            $whereQueryPart = $whereParts;
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS ' . $this->_getResultFieldsQueryPart() . ' FROM ' . $this->_table . ' WHERE '
            . $whereQueryPart . ' ' . $this->_getFilterQueryPart() . ' ' . $this->_getLimitQueryPart();
        return $this->_DB->get_results($query);
    }

    public function getCounter()
    {
        return $this->_counter;
    }

    public function search($keyword = null)
    {
        if (is_string($keyword)) {
            $this->setKeyword($keyword);
        }

        return $result = $this->_search();

        if (empty($result)) {
            $this->_keyword = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $this->_keyword);
            $result = $this->_search();
        }
        //  return $this->_getRankedSearchResult($result);
    }

    private function _search()
    {

        /*           $result = $this->_matchAgainstSearch();
                     $this->_counter = count($result);*/

        $result = $this->_mergeResults($result, $this->_rlikeSearch());
        $this->_counter = count($result);

        $result = $this->_mergeResults($result, $this->_likeSearch());
        $this->_counter = count($result);

        if ($result) {
            foreach ($result as $res) {
                $resu[] = $res['id'];
            }
        }

        return $resu;
    }

    private function _mergeResults($old, $new)
    {
        if (empty($new)) {
            return $old;
        }

        if (empty($old)) {
            return $new;
        }

        foreach ($old as $oldEl) {
            foreach ($new as $newElKey => $newEl) {
                if ($newEl[$this->_resultMergeField] == $oldEl[$this->_resultMergeField]) {
                    unset($new[$newElKey]);
                }
            }
        }

        return array_merge($old, $new);
    }

    /**
     * Возвращает все словоформы слов поискового запроса
     */

    private function _getRankedSearchResult($searchResult)
    {
        if (empty($searchResult)) {
            return $searchResult;
        }

        $resultRanks = $this->_getResultRanks($searchResult);
        arsort($resultRanks);

        $rankedResults = array();

        $i = 0;

        foreach ($resultRanks as $resultNumber => $rank) {
            $rankedResults[$i][$this->_resultMergeField] = $resultNumber;
            $i++;
        }

        return $rankedResults;
    }

    private function _getResultRanks($results)
    {
        $resultRanks = array();

        $phrases = $this->_getResultIndexPhrases($results);
        $totalRank = null;

        foreach ($phrases as $phraseData) {
            $rank = 0;
            /* for one field . need  modification for multiple. */
            $keywordWords = explode(' ', $this->_resultFields);

            foreach ($keywordWords as $word) {
                similar_text($word, $phraseData[$this->_searchFields[0]], $rank);
                $totalRank += $rank;
            }

            $resultRanks[(string)$phraseData[$this->_resultMergeField]] = $totalRank;
        }

        return $resultRanks;
    }

    private function _getResultIndexPhrases($results)
    {
        $phrasesIds = array();

        foreach ($results as $resultSet) {
            $phrasesIds[] = $resultSet[$this->_resultMergeField];
        }

        $where = $this->_resultMergeField . ' in(' . implode(',', $phrasesIds) . ')';
        $phrases = $this->_DB->SelectIN($select = '*', $this->_table, $where);
        return $phrases;
    }
}
