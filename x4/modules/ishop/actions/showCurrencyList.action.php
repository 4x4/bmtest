<?php

class showCurrencyListAction extends xAction
{


    public function run($params)
    {

        $this->loadModuleTemplate($params['params']['Template']);

        if ($currenciesList = $this->_commonObj->getCurrenciesList(true)) {
            if (isset($_SESSION['cacheable']['currency']['basic'])) $selected = $_SESSION['cacheable']['currency']['basic'];


            unset($params['request']['getData']['setCurrentCurrency']);

            $z = http_build_query($params['request']['getData']);
            if ($z) $z = '&' . $z;

            foreach ($currenciesList as $key => &$currency) {

                if (!$currency['showOnFront']) {
                    unset($currenciesList[$key]);
                    continue;
                }

                if ($currency['currencyId'] == $selected) $currency['selected'] = true;

                $url = xConfig::get('PATH', 'fullBaseUrl');


                $currency['link'] = CHOST . $params['request']['pageLink'] . $params['request']['requestActionPath'] . '/?setCurrentCurrency=' . $currency['currencyId'] . $z;
            }
            $this->_TMS->addReplace('showCurrencyList', 'currencies', $currenciesList);

            return $this->_TMS->parseSection('showCurrencyList');

        }

        return false;


    }


}
