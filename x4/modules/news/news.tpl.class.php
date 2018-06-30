<?php

class newsTpl extends xTpl implements xModuleTpl
{

    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function getNewsByTag($params)
    {
        //дописать чтобы была возможность передавать по tagId
        $newsList = $this->getSimilarNews($params['tag'], $params['OnPage'], 0);

        if (isset($params['DestinationPage'])) {
            $newsServerPage = $this->createPageDestination($params['DestinationPage']);

        } else {
            throw new Exception('DestinationPage-is-not-set;');
        }

        $newsList = $this->newsListTransform($newsList, $newsServerPage);

        return $newsList;
    }


}