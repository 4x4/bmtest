<?php


class ExchangeRateNBRB
{

    // URL, файл в формате XML
    public $exchange_url = 'http://www.nbrb.by/Services/XmlExRates.aspx?ondate=';
    public $xml;

    function __construct($timestamp)
    {
        //DebugBreak();
        $date = date('m/d/Y', $timestamp);
        // интерпретируем XML-файл в объект
        $url = $this->exchange_url;// . $date;


        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 25.0
            )
        ));

        $fp = fopen($url, 'r', false, $context);


        if (!$fp) {
            return;
        } else {
            $contents = '';
            while (!feof($fp)) {
                $contents .= fread($fp, 12192);
            }
        }

        $this->xml = simplexml_load_string($contents);
        //$cnt=$this-> get_content($url);

    }

    function xml2array($xml)
    {
        $arr = array();

        foreach ($xml as $element) {
            $tag = $element->getName();
            $e = get_object_vars($element);

            if (!empty($e)) {
                $arr[$tag] = $element instanceof SimpleXMLElement ? xml2array($element) : $e;
            } else {
                $arr[$tag] = trim($element);
            }
        }

        return $arr;
    }

    function getRates($ratesArray = null)
    {

        if ($this->xml !== FALSE) {
            foreach ($this->xml->Currency as $curel) {
                $curel = $this->xml2array($curel);

                if ($ratesArray) {
                    if (in_array($curel['CharCode'], $ratesArray)) {
                        $ex[$curel['CharCode']] = $curel['Rate'];
                    }
                } else {
                    $ex[$curel['CharCode']] = $curel['Rate'] / $curel['Scale'];
                }
            }

            return $ex;
        }
    }
}


?>