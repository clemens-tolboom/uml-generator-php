<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 18-03-14
 * Time: 16:45
 */

use PhpParser\Node;
use PhpParser\Node\Stmt;

class NodeVisitorDump extends PhpParser\NodeVisitorAbstract
{
    public function leaveNode(Node $node) {
        if ($node instanceof Node\Name) {
            return "";
        } elseif ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Interface_
            || $node instanceof Stmt\Function_) {
            $node->name = $node->namespacedName->toString('_');
        } elseif ($node instanceof Stmt\Const_) {
            foreach ($node->consts as $const) {
                $const->name = $const->namespacedName->toString('_');
            }
        }
        return "X";
    }
}