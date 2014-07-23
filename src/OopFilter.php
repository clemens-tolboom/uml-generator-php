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
    protected $index = [];
    protected $useIndex = [];

    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    public function getMeta()
    {
        return isset($this->meta) ? $this->meta : array();
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function clearIndex()
    {
        $this->index = [];
    }

    public function beforeTraverse(array $nodes)
    {
        $this->useIndex = array();
        $this->currentNamespace = null;
    }

    public function enterNode(Node $statement)
    {
        if ($statement instanceof Stmt\Namespace_) {
            //TODO: Workaround for two namespaces in some drupal files. (https://drupal.org/node/1858196 and https://drupal.org/node/1957330)
            if ($statement->name === null) {
                $this->currentNamespace = '\\';
            } else {
                $this->currentNamespace = '\\' . join('\\', $statement->name->parts);
            }
        } elseif ($statement instanceof Stmt\UseUse) {
            $this->parseUse($statement);
        }
    }

    public function leaveNode(Node $statement)
    {
        if ($statement instanceof Stmt\Class_) {
            return $this->parseClass($statement);
        } elseif ($statement instanceof Stmt\Interface_) {
            return $this->parseInterface($statement);
        } elseif ($statement instanceof Stmt\Trait_) {
            return $this->parseTrait($statement);
        } elseif ($statement instanceof Stmt\Namespace_) {
            return $statement->stmts;
        } elseif ($statement instanceof Stmt\TraitUse) {
            return $this->parseTraitUse($statement);
        } elseif ($statement instanceof Stmt\Property) {
            return $this->parseProperty($statement);
        } elseif ($statement instanceof Stmt\ClassMethod) {
            return $this->parseMethod($statement);
        } elseif ($statement instanceof Stmt\ClassConst) {
            return $this->parseConstant($statement);
        }
        if ($statement instanceof Stmt\PropertyProperty) return;
        if ($statement instanceof Node\Name) return;
        if ($statement instanceof Node\Const_) return;
        if ($statement instanceof Node\Scalar) return;
        if ($statement instanceof Node\Param) return;
        return false;
    }

    /**
     * @param Node $statement
     * @return array
     */
    private function parseClass(Node $statement)
    {
        $class_children = $statement->stmts;
        $children = array();
        $traits = array();
        foreach ($class_children as $child) {
            if (!is_array($child)) {
                $traits[] = $child;
            } else {
                $children[] = $child;
            }
        }
        $node = [
          'type' => 'class',
          'namespace' => $this->currentNamespace,
          'meta' => $this->getMeta(),
          'name' => $statement->name,
          'children' => $children,
          'traits' => $traits,
        ];
        if (isset($statement->extends->parts)) {
            if ($statement->extends instanceof Node\Name\FullyQualified) {
                $node['extends'] = '\\' . join('\\', $statement->extends->parts);
            } else {
                if (isset($this->useIndex[join('\\', $statement->extends->parts)])) {
                    $node['extends'] = $this->useIndex[join('\\', $statement->extends->parts)];
                } else {
                    $node['extends'] = $this->currentNamespace . '\\' . join('\\', $statement->extends->parts);
                }
            }
        }
        $implements = [];
        foreach ($statement->implements as $implement) {
            if ($implement instanceof Node\Name\FullyQualified) {
                $implementname = '\\' . join('\\', $implement->parts);
            } else {
                if (isset($this->useIndex[join('\\', $implement->parts)])) {
                    $implementname = $this->useIndex[join('\\', $implement->parts)];
                } else {
                    $implementname = $this->currentNamespace . '\\' . join('\\', $implement->parts);
                }
            }
            $implements[] = $implementname;
        }
        $node['implements'] = $implements;
        $this->addIndex($this->currentNamespace . '\\' . $statement->name, $this->getMeta()['output']);
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
            if ($statement->extends instanceof Node\Name\FullyQualified) {
                $node['extends'] = '\\' . join('\\', $statement->extends->parts);
            } else {
                $node['extends'] = $this->currentNamespace . '\\' . join('\\', $statement->extends->parts);
            }
        }
        $this->addIndex($this->currentNamespace . '\\' . $statement->name, $this->getMeta()['output']);
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
            if ($statement->extends instanceof Node\Name\FullyQualified) {
                $node['extends'] = '\\' . join('\\', $statement->extends->parts);
            } else {
                $node['extends'] = $this->currentNamespace . '\\' . join('\\', $statement->extends->parts);
            }
        }
        $this->addIndex($this->currentNamespace . '\\' . $statement->name, $this->getMeta()['output']);
        return [$node];
    }

    private function parseTraitUse(Node $statement)
    {
        $traits = array();
        foreach ($statement->traits as $trait) {

            if ($trait instanceof Node\Name\FullyQualified) {
                $node = '\\' . join('\\', $trait->parts);
            } else {
                if (isset($this->useIndex[join('\\', $trait->parts)])) {
                    $node = $this->useIndex[join('\\', $trait->parts)];
                } else {
                    $node = $this->currentNamespace . '\\' . join('\\', $trait->parts);
                }
            }
            $traits[] = $node;
        }
        return $traits;
    }

    private function parseConstant(Stmt\ClassConst $statement)
    {
        $node = [
          'type' => 'constant',
          'name' => $statement->consts[0]->name,
          'value' => $statement->consts[0]->name
        ];
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

    private function parseUse(Node $statement)
    {
        $path = '\\' . implode('\\', $statement->name->parts);
        $this->useIndex[$statement->alias] = $path;
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

    private function addIndex($fullyqualifiedname, $filename)
    {
        if (!isset($this->index[$fullyqualifiedname])) {
            $this->index[$fullyqualifiedname] = $filename;
        } else {
            $message = "Fully Qualified object name already in index: (%s) original file '%s', current file '%s'";
            //throw new \UnexpectedValueException(sprintf($message,$fullyqualifiedname, $this->index[$fullyqualifiedname], $filename));
        }

    }
}