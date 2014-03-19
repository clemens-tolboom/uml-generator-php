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
        $this->assertEquals('[]', $json);
    }
}
 