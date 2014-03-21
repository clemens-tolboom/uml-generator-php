<?php

namespace ExampleNameSpace;

use \UseNamespace\UseClass;

class ExampleClass extends SimpleExampleClass implements Examples, Things
{
    use ExampleTrait;
    use OtherTrait;

    private $privateVar;
    protected $protectedVar;
    public $publicVar;
    static $staticVar = "staticValue";

    function __construct($exampleargument)
    {
        $this->exampleproperty = $exampleargument;
    }

    public function publicFunction()
    {
    }

    function publicByDefaultFunction()
    {
    }

    static function staticFunction()
    {
    }

    public static function publicStaticFunction()
    {
    }

    private static function privateStaticFunction()
    {
    }

    protected  static function protectedStaticFunction()
    {
    }

    private function privateFunction()
    {
    }

    protected function protectedFunction()
    {
    }

}
