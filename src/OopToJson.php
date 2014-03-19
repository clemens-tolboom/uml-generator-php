<?php
namespace UmlGeneratorPhp;

use PhpParser\Node\Stmt;

class OopToJson
{
    private $phpparser;
    private $statements;

    public function __construct($inputcode)
    {
        $this->phpparser = new \PhpParser\Parser(new \PhpParser\Lexer);
        $this->statements = $this->phpparser->parse($inputcode);
    }

    public function getJson()
    {
        print_r($this->statements);
        $functiontree = $this->parseLevel($this->statements);
        print_r($functiontree);
        return json_encode($functiontree);
    }

    private function parseLevel($statements)
    {
        $ret = [];
        foreach ($statements as $statement) {
            if ($statement instanceof Stmt\Class_) {
                $node = [
                    'type' => 'class',
                    'name' => $statement->name,
                    'extends' => join('\\', $statement->extends->parts),
                    'children' => $this->parseLevel($statement->stmts)
                ];
                $implements = [];
                foreach($statement->implements as $implement){
                    $implements[] = join('\\', $implement->parts);
                }
                $node['implements'] = $implements;
                $ret[] = $node;
            } elseif ($statement instanceof Stmt\TraitUse) {
                foreach($statement->traits as $trait){
                    $node = [
                        'type' => 'traituse',
                        'name' => join('\\', $trait->parts)
                    ];
                    $ret[] = $node;
                }

            } elseif ($statement instanceof Stmt\Property) {
                $node = [
                    'type' => 'attribute',
                    'name' => $statement->props[0]->name
                ];
                $node['visibility'] = 'public';
                if ($statement->type & Stmt\Class_::MODIFIER_PUBLIC) {
                    $node['visibility'] = 'public';
                } elseif ($statement->type & Stmt\Class_::MODIFIER_PROTECTED) {
                    $node['visibility'] = 'protected';
                } elseif ($statement->type & Stmt\Class_::MODIFIER_PRIVATE) {
                    $node['visibility'] = 'private';
                }
                if ($statement->type & Stmt\Class_::MODIFIER_STATIC) {
                    $node['scope'] = 'classifier';
                } else {
                    $node['scope'] = 'instance';
                }
                $ret[] = $node;
            } elseif ($statement instanceof Stmt\ClassMethod) {
                $node = [
                    'type' => 'method',
                    'name' => $statement->name,
                    'scope' => $statement->getType()
                ];
                $params = [];
                foreach ($statement->params as $param) {
                    $params[] = [
                        'name' => $param->name,
                        'type' => $param->type
                    ];
                }
                $node['parameters'] = $params;

                //TODO: Workaround for issue https://github.com/clemens-tolboom/uml-generator-php/issues/4
                $node['visibility'] = 'public';
                if ($statement->type & Stmt\Class_::MODIFIER_PUBLIC) {
                    $node['visibility'] = 'public';
                } elseif ($statement->type & Stmt\Class_::MODIFIER_PROTECTED) {
                    $node['visibility'] = 'protected';
                } elseif ($statement->type & Stmt\Class_::MODIFIER_PRIVATE) {
                    $node['visibility'] = 'private';
                }
                if ($statement->type & Stmt\Class_::MODIFIER_STATIC) {
                    $node['scope'] = 'classifier';
                } else {
                    $node['scope'] = 'instance';
                }

                $ret[] = $node;
            }
        }
        return $ret;
    }
}