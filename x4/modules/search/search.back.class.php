<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;
use X4\Classes\TableJsonSource;


require_once(xConfig::get('PATH', 'EXT') . '/crawl/phpcrawler.class.php');
require_once(xConfig::get('PATH', 'EXT') . '/crawl/phpcrawlerpagerequest.class.php');

class searchCrawler
    extends PHPCrawler
{
    public $pages = array();


    public function handlePageData(&$page_data)
    {

        if ($page_data['insite_status'])
            $page_data['http_status_code'] = $page_data['insite_status'];

        preg_match('/<title>(.*?)<\/title>/', $page_data['source'], $result);

        $title = ($result[1] && $page_data['http_status_code'] == 200) ? $result[1] : '';


        $pattern = '/<\!--<index>-->(.+)<\!--<\/index>-->/is';

        $body = '';


        if ($page_data['http_status_code'] == 200 && preg_match_all($pattern, $page_data['source'], $matches)) {
            while (list(, $bodyElement) = each($matches[1])) {
                $body .= $bodyElement;
            }


            $body = iconv('UTF-8', 'windows-1251', $body);
            $body = strip_tags(preg_replace(array('/\s+/', '#<script[^>]*>.*?</script>#is'), array(' ', ''), $body));
            $index = XSTRING::Words2BaseForm(preg_replace('/\s+/', ' ', $body));
        } else {
            preg_match('/<body>(.+)<\/body>/is', $page_data['source'], $result);

            $body = $result[1];
            $body = iconv('UTF-8', 'windows-1251', $body);
            $body = strip_tags(preg_replace(array('/\s+/', '#<script[^>]*>.*?</script>#is'), array(' ', ''), $body));
            $index = XSTRING::Words2BaseForm(preg_replace('/\s+/', ' ', $body));
        }


        XPDO::insertIN('search_pages_index', array
        (
            'id' => 'null',
            'url' => $page_data['url'],
            'title' => $title,
            'body' => iconv('windows-1251', 'UTF-8', $body),
            'index' => iconv('windows-1251', 'UTF-8', $index),
            'status' => $page_data['http_status_code']
        ));

        $this->pages[] = array
        (
            'url' => $page_data['url'],
            'bytes_recieved' => XFILES::formatSize($page_data['bytes_received']),
            'body' => $title,
            'status' => $page_data['http_status_code']
        );
    }


}

class searchBack
    extends xModuleBack
{
    function __construct()
    {
        parent::__construct(__CLASS__);
    }


    public function stopIndexing($data)
    {

        XCache::serializedWrite(array('stopIndexing' => true), $this->_moduleName, 'customData');

    }

    public function indexing($data)
    {


        if (!$data['iterating']) {

            $crawler = new searchCrawler();
            $crawler->setURL(HTTP_HOST);
            $crawler->addReceiveContentType('/text\/html/');
            $crawler->addNonFollowMatch('/.(jpg|gif|png|js|doc|docx|ico|xls|css|pdf)$/i');
            $crawler->disableExtendedLinkInfo(true);
            $crawler->setCookieHandling(true);

            $tunes = xConfig::get('MODULES', 'search');
            $crawler->setTimeLimit($tunes['indexTimeLimit']);

            XRegistry::get('XPDO')->query('TRUNCATE TABLE `search_pages_index`');
            XCache::serializedWrite(array('stopIndexing' => false), $this->_moduleName, 'customData');

            $this->result['iterating'] = true;

        } else {

            if ($crawler = XCache::serializedRead($this->_moduleName, 'crawler')) {
                $crawler->initCrawler(true);
                $pagesIndexed = XCache::serializedRead($this->_moduleName, 'pagesCrawled');
                $customData = XCache::serializedRead($this->_moduleName, 'customData');
            }

        }


        session_write_close();

        $crawler->pages = array();

        if (2 == $crawler->go() && !$customData['stopIndexing']) {
            $this->result['finished'] = false;
            $this->result['iterating'] = true;
            XCache::serializedWrite($crawler, $this->_moduleName, 'crawler');
            XCache::serializedWrite($pagesIndexed = array('pagesIndexed' => ($pagesIndexed['pagesIndexed'] + count($crawler->pages))), $this->_moduleName, 'pagesCrawled');

        } else {
            $this->result['finished'] = true;
            $this->result['search']['report'] = $crawler->getReport();
            $pagesIndexed['pagesIndexed'] += count($crawler->pages);

        }

        $this->result['search']['pages'] = $this->gridformat($crawler->pages, $pagesIndexed['pagesIndexed']);
        $this->result['search']['indexed_pages_count'] = $pagesIndexed['pagesIndexed'];

    }

    public function gridformat($page_array, $idx)
    {
        while (list($k, $v) = each($page_array)) {
            array_unshift($v, $k);
            $idx++;
            $v[0] = $idx;

            $result['rows'][$idx] = array('data' => array_values($v));
        }

        return $result;
    }


    public function indexesTable($params)
    {

        $source = Common::classesFactory('TableJsonSource', array());

        $params['onPage'] = $this->_config['onPageBackendList'];

        $opt = array
        (
            'onPage' => $params['onPage'],
            'table' => 'search_pages_index',
            'order' => array
            (
                'id',
                'asc'
            ),
            'where' => $where,
            'idAsNumerator' => 'id',
            'columns' => array
            (
                'id' => array(),
                'url' => array(),
                'title' => array(),
                'body' => array('onAttribute' => TableJsonSource::$cutWords,
                    'onAttributeParams' => array('count' => '100')
                ),
                'index' => array('onAttribute' => TableJsonSource::$cutWords,
                    'onAttributeParams' => array('count' => '50')
                ),
                'status' => array()
            )
        );

        $source->setOptions($opt);
        if (!$params['page']) $params['page'] = 1;

        $this->result = $source->createView(1, $params['page']);

    }


    public function generateSitemap()
    {
        $pages = XPDO::selectIN('url', 'search_pages_index', "status=200", "group by url");

        if (isset($pages) && !empty($pages)) {

            $doc = new DOMDocument('1.0', 'utf-8');
            $urlset = $doc->createElement('urlset');
            $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $doc->appendChild($urlset);

            while (list($key, $item) = each($pages)) {
                $query = "SELECT * FROM `sitemap_rules` WHERE '" . $item['url'] . "' REGEXP `url` ORDER BY rate DESC";
                $rules = array
                (
                    'priority' => 0.5,
                    'changefreq' => 'weekly'
                );

                if ($pdoResult = XRegistry::get('XPDO')->query($query)) {
                    //   $rules = $pdoResult->fetchAll(PDO::FETCH_ASSOC);                            

                } else {
                    $rules = array
                    (
                        'priority' => 0.5,
                        'changefreq' => 'weekly'
                    );
                }

                if (!$rules['ignore']) {
                    $url = $doc->createElement('url');
                    $urlset->appendChild($url);
                    $loc = $doc->createElement('loc', $item['url']);
                    $url->appendChild($loc);
                    $priority = $doc->createElement('priority', $rules['priority']);
                    $changefreq = $doc->createElement('changefreq', $rules['changefreq']);
                    $url->appendChild($priority);
                    $url->appendChild($changefreq);
                }
            }

            if ($fh = fopen(PATH_ . xConfig::get('PATH', 'SITEMAP'), 'w')) {

                $data = $doc->saveXML();

                if (false !== fwrite($fh, $data, strlen($data))) {
                    return new okResult('sitemap-generated-success');

                } else {
                    return new badResult('sitemap-generated-failed');
                }

                fclose($fh);
            }


        }
    }


    function addSitemapRule($params)
    {
        global $TDB;
        $params['id'] = 'NULL';
        $res = $TDB->get_results("SELECT id FROM `sitemap_tunes` WHERE `from`='" . $params['from'] . "'");

        if (!$res) {
            if ($maxRate = $TDB->get_results('SELECT MAX(rate) FROM `sitemap_rules`')) {
                $maxRate = 1 + $maxRate[1]['MAX(rate)'];
            } else {
                $maxRate = 1;
            }

            $params['rate'] = $maxRate;

            if ($this->result['rules'] = $TDB->InsertIN('sitemap_rules', $params)) {
                x3_message::push($this->_common_obj->translate('saved'), $this->_module_name);
                return true;
            } else {
                x3_error::push($this->_common_obj->translate('save_error'), $this->_module_name);
            }
        } else {
            $res = current($res);
            $params['id'] = $res['id'];

            if ($params['ignore']) {
                $params['priority'] = 0;
                $params['changefreq'] = "monthly";
            }

            if ($this->result['rules'] = $TDB->InsertIN('sitemap_rules', $params)) {
                x3_message::push($this->_common_obj->translate('saved'), $this->_module_name);
            } else {
                x3_error::push($this->_common_obj->translate('save_error'), $this->_module_name);
            }
        }
    }

    function delete_sm_rule($params)
    {
        global $TDB;

        if ($params['id'] = (int)$params['id']) {
            $res = $TDB->get_results("DELETE FROM `sitemap_rules` WHERE `id`='" . $params['id'] . "'");

            if ($TDB->rows_affected) {
                x3_message::push($this->_common_obj->translate('deleted'), $this->_module_name);
            } else {
                x3_error::push($this->_common_obj->translate('save_error'), $this->_module_name);
            }
        }

        return $res;
    }

    function save_sm_rule($params)
    {
        global $TDB;

        if (!in_array($params['part'], array
        (
            'url',
            'priority',
            'changefreq',
            'ignore'
        ))
        ) {
            x3_error::push($this->_common_obj->translate('save_error'), $this->_module_name);
            return false;
        }

        $TDB->get_results("UPDATE `sitemap_rules` SET `" . $params['part'] . "`='" . $params['text'] . "' WHERE `id`='"
            . $params['id'] . "'");

        if ($TDB->rows_affected) {
            x3_message::push($this->_common_obj->translate('saved'), $this->_module_name);
        } else {
            if ($TDB->result) {
                x3_message::push($this->_common_obj->translate('no_changes'), $this->_module_name);
            } else {
                x3_error::push($this->_common_obj->translate('save_error'), $this->_module_name);
            }
        }
    }

    function update_rate($params)
    {
        global $TDB;
        $active = (int)$params['active'];
        $after = (int)$params['after'];
        $activeRate = $TDB->SelectIN('rate', 'sitemap_rules', 'id=' . $active);
        $activeRate = $activeRate[1]['rate'];
        $afterRate = $TDB->SelectIN('rate', 'sitemap_rules', 'id=' . $after);
        $afterRate = $afterRate[1]['rate'];
        $TDB->query("UPDATE `sitemap_rules` SET rate = rate + 1 WHERE rate > $afterRate");
        $TDB->query("UPDATE `sitemap_rules` SET rate = $afterRate + 1 WHERE id = $active");
    }

    function sitemap_rules_table()
    {
        global $_CONFIG;
        $TTS = Common::inc_module_factory('TTableSource');
        $options['startRow'] = 0;
        $options['table'] = 'sitemap_rules';
        $options['where'] = ' 1 ORDER BY rate ';
        $options['rows_per_page'] = 30;
        $options['gridFormat'] = 1;

        $options['columns'] = array('*');

        //obj_tree является прямым потомком Tree поэтому его использование обосновано
        $this->result['data_set'] = null;

        $options['sequence'] = array
        (
            'id',
            'url',
            'priority',
            'changefreq',
            'ignore'
        );

        $TTS->setOptions($options);
        $this->result['data_set'] = $TTS->CreateView();
    }

    function convert_row_to_rule($params)
    {
        global $TDB;

        switch ($params['operation']) {
            case 'convert2rule_p05weekly':
                $priority = 0.5;

                $changefreq = 'weekly';
                $ignore = false;
                $strict_path = true;
                break;

            case 'convert2rule_p1dayly':
                $priority = 1;

                $changefreq = 'daily';
                $ignore = false;
                $strict_path = true;
                break;

            case 'convert2rule_p01monthly':
                $priority = 0.1;

                $changefreq = 'monthly';
                $ignore = false;
                $strict_path = true;
                break;

            case 'convert2rule_m05weekly':
                $priority = 0.5;

                $changefreq = 'weekly';
                $ignore = false;
                $strict_path = false;
                break;

            case 'convert2rule_m1dayly':
                $priority = 1;

                $changefreq = 'daily';
                $ignore = false;
                $strict_path = false;
                break;

            case 'convert2rule_m01monthly':
                $priority = 0.1;

                $changefreq = 'monthly';
                $ignore = false;
                $strict_path = false;
                break;

            case 'convert2rule_pignore':
                $priority = 0.5;

                $changefreq = 'weekly';
                $ignore = true;
                $strict_path = true;
                break;

            case 'convert2rule_mignore':
                $priority = 0.5;

                $changefreq = 'weekly';
                $ignore = true;
                $strict_path = false;
                break;

            default:
                x3_error::push($this->_common_obj->translate('save_error'), $this->_module_name);

                return false;
        }

        if (($id = (int)$params['id']) && ($url = XARRAY::askeyval(
                $TDB->get_results(
                    "SELECT url FROM `search_pages_index` WHERE id=$id "),
                'url'))
        ) {
            $url = str_replace(CHOST, '', current($url));

            if ($strict_path) {
                $url = '^' . $url . "$";
            }

            if ($this->add_sitemap_rule(array
            (
                'url' => $url,
                'priority' => $priority,
                'changefreq' => $changefreq,
                'ignore' => $ignore
            ))
            ) {
          //      x3_message::push($this->_common_obj->translate('saved'), $this->_module_name);
                return true;
            }
        }

        //x3_error::push($this->_common_obj->translate('save_error'), $this->_module_name);
    }


    public function onAction_showSearchForm($params)
    {
        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
        }

        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName,
            $params['data']['params']['Template'],
            array('.showSearchForm.html'));


        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('searchServer');
    }


    public function onAction_searchServer($params)
    {
        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
        }

        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.searchServer.html'));
    }

}
