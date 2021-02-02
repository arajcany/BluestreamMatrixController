<?php

namespace arajcany\Tests;

use arajcany\BlustreamMatrixController\BlustreamMatrix;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * Class BlustreamMatrixTest
 *
 * @property BlustreamMatrix $BlustreamMatrix
 *
 * @package arajcany\Tests
 */
class BlustreamMatrixTest extends TestCase
{
    public $tstHomeDir;
    public $tstTmpDir;
    public $now;
    public $BlustreamMatrix;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->now = date("Y-m-d H:i:s");
        $this->tstHomeDir = str_replace("\\Utility", '', __DIR__) . "\\";
        $this->tstTmpDir = __DIR__ . "\\..\\..\\tmp\\";

        $this->BlustreamMatrix = new BlustreamMatrix();
    }

    public function testSetBlustreamPort()
    {
        $port = 80;
        $this->BlustreamMatrix->setBlustreamPort($port);
        $this->assertEquals('', parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_PORT));

        $port = 443;
        $this->BlustreamMatrix->setBlustreamPort($port);
        $this->assertEquals('', parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_PORT));

        $port = 8080;
        $this->BlustreamMatrix->setBlustreamPort($port);
        $this->assertEquals($port, parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_PORT));
    }

    public function testSetBlustreamHost()
    {
        $host = '127.0.0.1';
        $this->BlustreamMatrix->setBlustreamHost($host);
        $this->assertEquals($host, parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_HOST));

        $host = '192.168.0.20';
        $this->BlustreamMatrix->setBlustreamHost($host);
        $this->assertEquals($host, parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_HOST));

        $host = 'blustream-http-cgi.localhost';
        $this->BlustreamMatrix->setBlustreamHost($host);
        $this->assertEquals($host, parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_HOST));
    }

    public function testSetBlustreamScheme()
    {
        $scheme = 'http';
        $this->BlustreamMatrix->setBlustreamScheme($scheme);
        $this->assertEquals($scheme, parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_SCHEME));

        $scheme = 'https';
        $this->BlustreamMatrix->setBlustreamScheme($scheme);
        $this->assertEquals($scheme, parse_url($this->BlustreamMatrix->getBaseUrl(), PHP_URL_SCHEME));
    }

    public function testGetConfigurationXml()
    {
        $scheme = 'http';
        $host = 'blustream-http-cgi.localhost';
        $port = 80;

        $this->BlustreamMatrix->setBlustreamScheme($scheme);
        $this->BlustreamMatrix->setBlustreamHost($host);
        $this->BlustreamMatrix->setBlustreamPort($port);

        $result = $this->BlustreamMatrix->getConfigurationXml('domdocument');
        $this->assertEquals(true, $result instanceof \DOMDocument);

        $result = $this->BlustreamMatrix->getConfigurationXml('simplexml');
        $this->assertEquals(true, $result instanceof \SimpleXMLElement);

        $result = $this->BlustreamMatrix->getConfigurationXml('array');
        $this->assertIsArray($result);

        $result = $this->BlustreamMatrix->getConfigurationXml();
        $this->assertIsString($result);
    }

}
