<?php
/**
 * Created by PhpStorm.
 * User: Martijn Braam
 * Date: 3/19/14
 * Time: 12:18 PM
 */

namespace UmlGeneratorPhp;


class JsonGeneratorTest extends \PHPUnit_Framework_TestCase {
    private $jsongenerator;

    public function setUp(){
        $this->jsongenerator = new JsonGenerator(file_get_contents('./tests/data/class01.php'));
    }

    public function testGetJson(){
        $json = $this->jsongenerator->getJson();
        $output = json_decode($json, true);
        $expected = json_decode('[{"type":"class","name":"ExampleClass","children":[{"type":"method","name":"__construct","scope":"instance","parameters":[{"name":"exampleargument","type":null}],"visibility":"public"},{"type":"method","name":"publicFunction","scope":"instance","parameters":[],"visibility":"public"},{"type":"method","name":"publicByDefaultFunction","scope":"instance","parameters":[],"visibility":"public"},{"type":"method","name":"staticFunction","scope":"classifier","parameters":[],"visibility":"public"},{"type":"method","name":"publicStaticFunction","scope":"classifier","parameters":[],"visibility":"public"},{"type":"method","name":"privateStaticFunction","scope":"classifier","parameters":[],"visibility":"private"},{"type":"method","name":"protectedStaticFunction","scope":"classifier","parameters":[],"visibility":"protected"},{"type":"method","name":"privateFunction","scope":"instance","parameters":[],"visibility":"private"},{"type":"method","name":"protectedFunction","scope":"instance","parameters":[],"visibility":"protected"}]}]',true);
        $this->assertEquals($expected, $output);
    }
}
 