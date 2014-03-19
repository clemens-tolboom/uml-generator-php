<?php

class ExampleClass
{
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

    private function privateFunction()
    {
    }

    protected function protectedFunction()
    {
    }

}
