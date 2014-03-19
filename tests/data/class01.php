<?php
class ExampleClass{
  private $exampleproperty;

  function __construct($exampleargument){
    $this->exampleproperty = $exampleargument;
  }

  function getExampled(){
    return $this->exampleproperty . ' example!';
  }
}