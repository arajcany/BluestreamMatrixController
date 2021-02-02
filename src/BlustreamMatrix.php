<?php

namespace arajcany\BlustreamMatrixController;

use Cake\Utility\Xml;
use DOMDocument;
use GuzzleHttp\Client;

/**
 * Class BlustreamMatrix
 * HTTP-CGI commands to control a Blustream Matrix Switcher
 *
 * @package arajcany\BlustreamMatrixController
 */
class BlustreamMatrix
{
    private $blustreamScheme;
    private $blustreamHost;
    private $blustreamPort;

    /**
     * BlustreamMatrix constructor.
     */
    public function __construct()
    {
        $this->blustreamScheme = 'http';
        $this->blustreamHost = '127.0.0.1';
        $this->blustreamPort = '80';
    }

    /**
     * @param string $blustreamScheme
     * @return BlustreamMatrix
     */
    public function setBlustreamScheme($blustreamScheme)
    {
        $this->blustreamScheme = $blustreamScheme;
        return $this;
    }

    /**
     * @param mixed $blustreamHost
     * @return BlustreamMatrix
     */
    public function setBlustreamHost($blustreamHost)
    {
        $this->blustreamHost = $blustreamHost;
        return $this;
    }

    /**
     * @param integer $blustreamPort
     * @return BlustreamMatrix
     */
    public function setBlustreamPort($blustreamPort)
    {
        $this->blustreamPort = $blustreamPort;
        return $this;
    }

    /**
     * Get the base URL for the Switcher
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $baseUrl = $this->blustreamScheme . "://" . $this->blustreamHost;

        if ($this->blustreamPort != 80 && $this->blustreamPort != 443) {
            $baseUrl .= ":" . $this->blustreamPort . "/";
        } else {
            $baseUrl .= "/";
        }

        return $baseUrl;
    }


    /**
     * Get the XML from the Switcher in the desired format.
     *
     * @param string $returnFormat Can either be SimpleXmlElement|DOMDocument|array
     * @return array|DOMDocument|false|\Psr\Http\Message\StreamInterface|\SimpleXMLElement|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConfigurationXml($returnFormat = '')
    {
        $url = $this->getBaseUrl() . "cgi-bin/getxml.cgi?xml=mxsta";

        $client = new Client();
        $res = $client->request('GET', $url);

        $xmlAsText = $res->getBody();
        if (strlen($xmlAsText) == 0) {
            $xmlAsText = file_get_contents(ROOT . "\\docs\\http_cgi_example_xml_01.xml");
        }

        if (strtolower($returnFormat) == 'domdocument') {
            $doc = Xml::loadHtml($xmlAsText, ['return' => 'domdocument']);
        } elseif (strtolower($returnFormat) == 'simplexml' || strtolower($returnFormat) == 'simplexmlelement') {
            $doc = Xml::loadHtml($xmlAsText, ['return' => 'simplexml']);
        } elseif (strtolower($returnFormat) == 'array') {
            $doc = Xml::toArray(Xml::build($xmlAsText));
        } else {
            $doc = $xmlAsText;
        }

        return $doc;
    }


}