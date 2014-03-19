<?php
namespace UmlGeneratorPhp;

use PhpParser\Node\Stmt;

class JsonGenerator{
    private $phpparser;
    private $statements;

    public function __construct($inputcode){
        $this->phpparser = new \PhpParser\Parser(new \PhpParser\Lexer);
        $this->statements = $this->phpparser->parse($inputcode);
    }

    public function getJson(){
        $functiontree = $this->parseLevel($this->statements);
        print_r($functiontree);
        return json_encode($functiontree);
    }

    private function parseLevel($statements){
        $ret = [];
        foreach($statements as $statement){
            if($statement instanceof Stmt\Class_){
                $node = [
                    'type' => 'class',
                    'name' => $statement->name,
                    'children' => $this->parseLevel($statement->stmts)
                ];
                $ret[] = $node;
            }elseif($statement instanceof Stmt\Function_ || $statement instanceof Stmt\ClassMethod){
                $node = [
                    'type' => $statement instanceof Stmt\Function_ ? 'function' : 'method',
                    'name' => $statement->name
                ];
                $params = [];
                foreach($statement->params as $param){
                    $params[] = [
                        'name' => $param->name,
                        'type' => $param->type
                    ];
                }
                $node['parameters'] = $params;
                $ret[] = $node;
            }
        }
        return $ret;
    }
}