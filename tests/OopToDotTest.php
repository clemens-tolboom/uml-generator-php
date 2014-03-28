<?php

/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 21-03-14
 * Time: 11:27
 */

namespace UmlGeneratorPhp;

use UmlGeneratorPhp\OopFilter;
use UmlGeneratorPhp\OopToDot;

class OopToDotTest extends \PHPUnit_Framework_TestCase
{
    private $parser;
    private $traverser;

    public function setUp()
    {
        $this->parser = new \PhpParser\Parser(new \PhpParser\Lexer);
        $this->traverser = new \PhpParser\NodeTraverser;
        $filter = new OopFilter;
        $filter->setMeta([
            'file' => '/dummy/path.php'
        ]);
        $this->traverser->addVisitor($filter);
    }

    public function testGenerateHTML()
    {
        $code = file_get_contents(__DIR__ . '/data/class01.php');
        $stmts = $this->parser->parse($code);
        $data = $this->traverser->traverse($stmts);
        //var_dump($data);
        $toDot = new OopToDot();
        $dot = $toDot->getClassDiagram($data);


    }

}
 