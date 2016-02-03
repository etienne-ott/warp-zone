<?php

class AbstractControllerTest extends PHPUnit_Framework_TestCase
{
    public function testActionUnknown()
    {
        try {
            $foo = new Foo(new \WarpZone\View());
            $foo->action('bar', array());
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), "Unknown action bar in controller WarpZone\Controller\AbstractController");
            return;
        }
        $this->fail();
    }

    public function testActionKnown()
    {
        try {
            $foo = new Foo(new \WarpZone\View());
            $foo->action('baz', array());
        } catch (Exception $e) {
            $this->fail();
        }
    }
}

class Foo extends \WarpZone\Controller\AbstractController {
    public function bazAction() {}
}