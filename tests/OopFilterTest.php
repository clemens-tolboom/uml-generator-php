<?php
/**
 * Created by PhpStorm.
 * User: Martijn Braam
 * Date: 3/19/14
 * Time: 12:18 PM
 */

namespace UmlGeneratorPhp;


class JsonGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $parser;
    private $traverser;

    public function setUp()
    {
        $this->parser = new \PhpParser\Parser(new \PhpParser\Lexer);
        $this->traverser = new \PhpParser\NodeTraverser;

        $visitor = new OopFilter;
        $this->traverser->addVisitor($visitor);
    }

    function testNamespace()
    {
        $result = $this->traverser->traverse($this->parser->parse($this->getCode('interface')));
        var_dump($result);
        $this->assertEquals('interfaceNamespace', $result[0]['namespace'], "Namespace found");
    }

    function testInterface()
    {
        $stmts = $this->parser->parse($this->getCode('interface'));
        $result = $this->traverser->traverse($stmts);

        $children = $result[0]['children'];
        $this->assertEquals(1, count($children), "1 method found");
        $method = $children[0];
        $this->assertEquals('method', $method['type'], "method");
    }

    function testFunctionScope()
    {
        $scopes = array('', 'public', 'protected', 'private');
        foreach ($scopes as $scope) {
            $stmts = $this->parser->parse($this->getCode('interface', $scope));
            $result = $this->traverser->traverse($stmts);

            $children = $result[0]['children'];

            $method = $children[0];
            $this->assertEquals('method', $method['type'], "method");
            if (empty($scope)) {
                $scope = 'public';
            }
            $this->assertEquals($scope, $method['visibility'], "method");
        }
    }


    /**
     * @param $type
     *   interface, class, trait
     * @param string $scope
     *   public, protected, private
     * @return string
     */
    function getCode($type, $scope = '')
    {
        return "<?php
        namespace {$type}Namespace;

        $type {$type}Name {
          $scope function testFunction();
        }
        ";

    }
}