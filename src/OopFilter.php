<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 18-03-14
 * Time: 16:45
 */
namespace UmlGeneratorPhp;

use PhpParser\Node;
use PhpParser\Node\Stmt;

class OopFilter extends \PhpParser\NodeVisitorAbstract
{
    protected $currentNamespace;

    /**
     * @var prepare for meta data
     *   We need at least the file being processed.
     */
    protected $meta;

    public function setMeta($meta){
        $this->meta = $meta;
    }

    public function getMeta(){
        return isset($this->meta) ? $this->meta : array();
    }

    public function enterNode(Node $statement){
        if($statement instanceof Stmt\Namespace_){
            //TODO: Workaround for two namespaces in some drupal files. (https://drupal.org/node/1858196 and https://drupal.org/node/1957330)
            if($statement->name===null){
                $this->currentNamespace = '';
            }else{
                $this->currentNamespace = join('\\', $statement->name->parts);
            }
        }
    }

    public function leaveNode(Node $statement) {
        if ($statement instanceof Stmt\Class_) {
            return $this->parseClass($statement);
        } elseif ($statement instanceof Stmt\Interface_){
            return $this->parseInterface($statement);
        } elseif ($statement instanceof Stmt\Trait_){
            return $this->parseTrait($statement);
        } elseif ($statement instanceof Stmt\Namespace_){
            return $statement->stmts;
        } elseif ($statement instanceof Stmt\TraitUse) {
            foreach($statement->traits as $trait){
                $node = [
                    'type' => 'traituse',
                    'name' => join('\\', $trait->parts)
                ];
                return [$node];
            }

        } elseif ($statement instanceof Stmt\Property) {
            return $this->parseProperty($statement);
        } elseif ($statement instanceof Stmt\ClassMethod) {
            return $this->parseMethod($statement);
        }
        if($statement instanceof Stmt\PropertyProperty) return;
        if($statement instanceof Node\Name) return;
        return false;
    }

    /**
     * @param Node $statement
     * @return array
     */
    private function parseClass(Node $statement)
    {
        $node = [
            'type' => 'class',
            'namespace' => $this->currentNamespace,
            'meta' => $this->getMeta(),
            'name' => $statement->name,
            'children' => $statement->stmts
        ];
        if (isset($statement->extends->parts)) {
            $node['extends'] = join('\\', $statement->extends->parts);
        }
        $implements = [];
        foreach ($statement->implements as $implement) {
            $implements[] = join('\\', $implement->parts);
        }
        $node['implements'] = $implements;
        return [$node];
    }

    /**
     * @param Node $statement
     * @return array
     */
    private function parseInterface(Node $statement)
    {
        $node = [
            'type' => 'interface',
            'namespace' => $this->currentNamespace,
            'meta' => $this->getMeta(),
            'name' => $statement->name,
            'children' => $statement->stmts
        ];
        if (isset($statement->extends->parts)) {
            $node['extends'] = join('\\', $statement->extends->parts);
        }
        return [$node];
    }

    /**
     * @param Node $statement
     * @return array
     */
    private function parseTrait(Node $statement)
    {
        $node = [
            'type' => 'trait',
            'namespace' => $this->currentNamespace,
            'meta' => $this->getMeta(),
            'name' => $statement->name,
            'children' => $statement->stmts
        ];
        if (isset($statement->extends->parts)) {
            $node['extends'] = join('\\', $statement->extends->parts);
        }
        return [$node];
    }

    /**
     * @param Node $statement
     * @return array
     */
    private function parseProperty(Node $statement)
    {
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
        return [$node];
    }

    /**
     * @param Node $statement
     * @return array
     */
    private function parseMethod(Node $statement)
    {
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

        return [$node];
    }
}