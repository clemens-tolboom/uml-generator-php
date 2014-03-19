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
        print_r($this->statements);
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
                    'name' => $statement->name,
                    'scope' => $statement->getType()
                ];
                $params = [];
                foreach($statement->params as $param){
                    $params[] = [
                        'name' => $param->name,
                        'type' => $param->type
                    ];
                }
                $node['parameters'] = $params;

                if($statement instanceof Stmt\ClassMethod){
                    //TODO: Workaround for issue https://github.com/clemens-tolboom/uml-generator-php/issues/4
                    $node['visibility'] = 'public';
                    if($statement->type & Stmt\Class_::MODIFIER_PUBLIC){
                        $node['visibility'] = 'public';
                    }elseif($statement->type & Stmt\Class_::MODIFIER_PROTECTED){
                        $node['visibility'] = 'protected';
                    }elseif($statement->type & Stmt\Class_::MODIFIER_PRIVATE){
                        $node['visibility'] = 'private';
                    }
                    if($statement->type & Stmt\Class_::MODIFIER_STATIC){
                        $node['scope'] = 'classifier';
                    }else{
                        $node['scope'] = 'instance';
                    }

                }

                $ret[] = $node;
            }
        }
        return $ret;
    }
}