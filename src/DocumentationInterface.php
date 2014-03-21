<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 21-03-14
 * Time: 16:11
 */

namespace UmlGeneratorPhp;

interface DocumentationInterface {
    function getSiteURL($data);
    function getObjectURL($data);
    function getMethodURL($data);
    function getPropertyURL($data);
}
