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
    private $visitor;

    public function setUp()
    {
        $this->parser = new \PhpParser\Parser(new \PhpParser\Lexer);
        $this->traverser = new \PhpParser\NodeTraverser;

        $this->visitor = new OopFilter;
        $this->visitor->setMeta([
            'file' => 'dummy/path.php'
        ]);
        $this->traverser->addVisitor($this->visitor);
    }

    function testNamespace()
    {
        $this->visitor->clearIndex();
        $entities = array('interface', 'class', 'trait');
        foreach($entities as $entity){
            $result = $this->traverser->traverse($this->parser->parse($this->getCode($entity)));
            $this->assertEquals('\\' . $entity . 'Namespace', $result[0]['namespace'], "Namespace found");
        }
    }

    function testInterface()
    {
        $this->visitor->clearIndex();
        $stmts = $this->parser->parse($this->getCode('interface'));
        $result = $this->traverser->traverse($stmts);

        $children = $result[0]['children'];
        $methods = array_values(array_filter($children, [$this, 'isMethod']));
        $this->assertEquals(1, count($methods), "1 method found");
        $method = $methods[0];
        $this->assertEquals('method', $method['type'], "method");
    }

    function testFunctionScope()
    {
        $scopes = array('', 'public', 'protected', 'private', 'static');
        foreach ($scopes as $scope) {
            $this->visitor->clearIndex();
            $stmts = $this->parser->parse($this->getCode('interface', $scope));
            $result = $this->traverser->traverse($stmts);

            $children = $result[0]['children'];
            $methods = array_values(array_filter($children, [$this, 'isMethod']));
            $method = $methods[0];
            $this->assertEquals('method', $method['type'], "method");

            if (empty($scope)) {
                $scope = 'public';
            }
            if ($method['scope'] == 'classifier'){
                $method['visibility'] = 'static';
            }
            $this->assertEquals($scope, $method['visibility'], "method");
        }
    }

    function testAttributeScope()
    {
        $scopes = array('', 'public', 'protected', 'private', 'static');
        foreach ($scopes as $scope) {
            $this->visitor->clearIndex();
            $stmts = $this->parser->parse($this->getCode('class', $scope));
            $result = $this->traverser->traverse($stmts);

            $children = $result[0]['children'];
            $attributes = array_values(array_filter($children, [$this, 'isAttribute']));
            $attribute = $attributes[0];
            $this->assertEquals('attribute', $attribute['type'], "attribute");
            if (empty($scope)) {
                $scope = 'public';
            }
            if ($attribute['scope'] == 'classifier'){
                $attribute['visibility'] = 'static';
            }
            $this->assertEquals($scope, $attribute['visibility'], "attribute");
        }
    }

    function testConstant()
    {
        $this->visitor->clearIndex();
        $result = $this->traverser->traverse($this->parser->parse($this->getCode('class')));
        $children = $result[0]['children'];
        $constants = array_values(array_filter($children, [$this, 'isConstant']));
        $this->assertEquals(1, count($constants), "1 constant found");
        $constant = $constants[0];
        $this->assertEquals('constant', $constant['type'], "Constant found");
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
        $code = "<?php
        namespace {$type}Namespace;

        $type {$type}Name {
            const TESTCONST='TEST';
        ";
        if($type != 'interface'){
            $prefix = $scope == '' ? 'var' : $scope;
            $code .= "$prefix \${$scope}Attribute;" . PHP_EOL;
        }
        $code .= "$scope function testFunction();" . PHP_EOL;
        $code .= "}
        ";
        return $code;
    }

    function isMethod($node){
        return $node['type'] == 'method';
    }

    function isAttribute($node){
        return $node['type'] == 'attribute';
    }

    function isConstant($node){
        return $node['type'] == 'constant';
    }
}