<?php

use X4\Classes\MultiSection;
use X4\Classes\XRegistry;
use X4\Classes\XNameSpaceHolder;
use X4\Classes\ImageRenderer;
use X4\Classes\TagManager;

class newsFront extends xModule
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        if (xConfig::get('GLOBAL', 'currentMode') == 'front') $this->_tree->cacheState(true);
    }

    public function setSeoData($seo)
    {

        if ($sData = XRegistry::get('EVM')->fire($this->_moduleName . '.setSeoData', array('object' => $seo))) {
            $seo = $sData;
        }
        XRegistry::get('TPA')->setSeoData($seo);

    }

    public function tag($params)
    {

        $template = ($params['params']['TemplateInterval']) ? $params['params']['TemplateInterval'] : $params['params']['Template'];

        if ((int)$params['params']['OnPage'] === 0) {
            $params['params']['OnPage'] = $this->_config['showNewsPerPage'];
        }

        if (empty($_GET['tag'])) {
            $tag = $params['params']['tag'];
        } else {
            $tag = $_GET['tag'];
        }

        $this->loadModuleTemplate($template);


        if (empty($tag)) {
            return $this->_TMS->parseSection('news_by_tag_fail');
        }

        $count = $this->getSimilarNews($tag, $params['params']['OnPage'], 0, false, true);

        if ($count > 0) {
            $newsList = $this->getSimilarNews($tag, $params['params']['OnPage'], 0);

            if ($params['params']['DestinationPage']) {
                $newsServerPage = $this->createPageDestination($params['params']['DestinationPage']);
            } else {
                $pInfo = XRegistry::get('TPA')->getRequestActionInfo();
                $newsServerPage = $pInfo['pageLink'];
            }
            $newsList = $this->newsListTransform($newsList, $newsServerPage);
            Common::parseNavPages($count, $params['params']['OnPage'], $startpage, $newsServerPage, $this->_TMS);
            return $this->renderNews($newsList, $newsServerPage, null, $count, $params['params']['OnPage']);
        } else {
            return $TMS->parseSection('news_by_tag_fail');
        }
    }

    public function getSimilarNews($tags, $rowsNum, $startRow = 0, $newsId = false, $countOnly = false)
    {
        $tagStr = '';

        if ($tags) {
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    $tagStr .= 'tags LIKE \'%"' . trim($tag) . '"%\' OR ';
                }

                $tagStr = substr($tagStr, 0, -3);
            } else {
                $tagStr = 'tags LIKE \'%"' . trim($tags) . '"%\' ';
            }

            if ($newsId !== false) {
                $newsId = ' AND id!=' . $newsId;
            }

            if ($countOnly) {
                $query = "SELECT count(id) as ncount FROM news WHERE $tagStr AND active = 1;";
            } else {
                $query = "SELECT * FROM news WHERE $tagStr AND active = 1 $newsId ORDER BY news_date DESC LIMIT $startRow,$rowsNum";
            }

            $PDO = XRegistry::get('XPDO');
            if (($pdoResult = $PDO->query($query)) && (!$countOnly)) {

                while ($pf = $pdoResult->fetch(PDO::FETCH_ASSOC)) {
                    $news[] = $pf;
                }

                return $news;
            } elseif ($pdoResult && $countOnly) {
                $result = $pdoResult->fetch(PDO::FETCH_ASSOC);
                return $result['ncount'];
            }
        }
    }

    public function newsListTransform($newsList, $newsServerPage)
    {
        if ($authors = XARRAY::asKeyVal($newsList, 'author_id')) {
            $authors = array_unique($authors);
            $authors = array_filter($authors);
            if (!empty($authors)) {
                $fusers = xCore::moduleFactory('fusers.back');
                $fusersList = $fusers->_tree->selectStruct('*')->selectParams('*')->where(array(
                    '@id',
                    '=',
                    $authors
                ))->format('keyval', 'id')->run();

                foreach ($newsList as &$news) {
                    if ($news['author_id']) {
                        $news['author'] = $fusersList[$news['author_id']];
                    }
                }
            }
        }

        if ($tags = XARRAY::asKeyVal($newsList, 'tags')) {
            if (!empty($tags)) {
                $arrMergedTags = array();
                foreach ($tags as $tag) {
                    if (!empty($tag)) {
                        $tagsDecoded = json_decode($tag);
                        $arrMergedTags = array_merge($arrMergedTags, $tagsDecoded);
                    }
                }

                $tags = $this->tagsAgregate($arrMergedTags, $newsServerPage);

                foreach ($newsList as & $news) {
                    if (!empty($news['tags'])) {
                        $decoupled = json_decode($news['tags']);
                        foreach ($decoupled as $item) {
                            if (isset($tags[$item])) {
                                $tagBase[] = $tags[$item];
                            }
                        }

                        $news['tags'] = $tagBase;
                    }
                }
            }
        }

        return $newsList;
    }

    public function tagsAgregate($inputTags, $serverPage)
    {
        $tagMan = new TagManager();
        if (!empty($inputTags)) {
            if (!is_array($inputTags)) $inputTags = json_decode($inputTags);

            if ($tags = $tagMan->getTagById($inputTags)) {
                foreach ($tags as $tag) {
                    $tag['link'] = $serverPage . '/~tag/?tag=' . $tag['id'];
                    $tagsBase[$tag['id']] = $tag;
                }
            }

            return $tagsBase;
        }
    }

    public function renderNews($newsList, $newsServerPage, $categories)
    {
        if (isset($newsList)) {
            foreach ($newsList as & $news) {
                $news['link'] = $newsServerPage . '/~showNews/' . $news['basic'];
            }
        } else {
            $this->_TMS->parseSection('newsIntervalEmpty', true);
        }

        $this->_TMS->addMassReplace('newsInterval', array(
            'newsList' => $newsList,
            'categories' => $categories
        ));

        return $this->_TMS->parseSection('newsInterval');
    }

}