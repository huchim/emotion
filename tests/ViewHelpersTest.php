<?php
include "CustomBootstrap.php";

use \PHPUnit\Framework\TestCase;
use \Emotion\HttpContext;
use \Emotion\Exceptions\ExceptionCodes;
use \Emotion\Views\ViewHelpers;
use \Emotion\Contracts\IReadOnlyAppState;

class ViewHelpersTest extends TestCase
{
    /**
     * Undocumented variable
     *
     * @var \Emotion\Contracts\IReadOnlyAppState
     */
    private static $appState = null;
    private static $app = null;

    public function setUp() {
        if (!defined("APP_DEBUG")) define("APP_DEBUG", true);

        self::$app = new \Emotion\App2();
        self::$app->setDirectoryBase("tests/");
        self::$app->loadDefaultConfiguration();     
        self::$app->addMvc();
        self::$appState = self::$app->getReadOnlyState();
    }

    public function testContent() {
        HttpContext::server("REQUEST_URI", "/Home");        
        $this->v = new ViewHelpers(self::$appState);

        $actual = $this->v->content2("~/public/demo.js");
        $expected = "http://localhost/public/demo.js";
        $this->assertEquals($expected, $actual);
    }

    public function testUrl() {
        HttpContext::server("REQUEST_URI", "/Home");        
        $this->v = new ViewHelpers(self::$appState);

        $actual = $this->v->url("default", "Index", "Home", "?a=1");
        $expected = "/Home/Index/?a=1";
        $this->assertEquals($expected, $actual);
    }

}