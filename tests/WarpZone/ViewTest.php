<?php

class ViewTest extends PHPUnit_Framework_TestCase
{
    protected $_view;

    protected function setUp()
    {
        $this->_view = new \WarpZone\View();
        $this->_view->setBaseUrl('/warp-zone');
    }

    public function testUrlExternalUnchanged()
    {
        $this->assertEquals($this->_view->url('http://www.example.org'), 'http://www.example.org');
    }

    public function testUrlExternalUnchangedShort()
    {
        $view = new \WarpZone\View();
        $this->assertEquals($this->_view->url('//cdn.example.org'), '//cdn.example.org');
    }

    public function testUrlLineNoise()
    {
        $view = new \WarpZone\View();
        $this->assertEquals($this->_view->url('#t348hf23+23ß+fi2<1^211´4ß234#+-we,f+'), '/warp-zone/#t348hf23+23ß+fi2<1^211´4ß234#+-we,f+');
    }

    public function testUrlInternalWithSlash()
    {
        $view = new \WarpZone\View();
        $this->assertEquals($this->_view->url('/definition/show/foobar'), '/warp-zone/definition/show/foobar');
    }

    public function testUrlInternalWithoutSlash()
    {
        $view = new \WarpZone\View();
        $this->assertEquals($this->_view->url('definition/show/foobar'), '/warp-zone/definition/show/foobar');
    }
}