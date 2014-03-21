<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 21-03-14
 * Time: 16:12
 */

namespace UmlGeneratorPhp;

use UmlGeneratorPhp\DocumentationInterface;

class DrupalDocumentation implements DocumentationInterface
{

    protected $meta;

    function __construct($meta)
    {
        $this->meta = $meta;
    }

    function getSiteURL($data)
    {
        return $this->meta['siteURL'];
    }

    function getMethodURL($data, $classdata)
    {
        $parts = array();
        $parts[] = $this->getSiteURL($data);
        $parts[] = $this->meta['component'];

        $file = str_replace($this->meta['basePath'], '', $classdata['meta']['file']);
        $parts[] = str_replace(DIRECTORY_SEPARATOR, '!', $file);
        $parts[] = 'function';
        $parts[] = $classdata['name'] . '::' . $data['name'];
        $parts[] = $this->meta['version'];

        return join("/", $parts);
    }

    function getPropertyURL($data, $classdata)
    {
        $parts = array();
        $parts[] = $this->getSiteURL($data);
        $parts[] = $this->meta['component'];

        $file = str_replace($this->meta['basePath'], '', $classdata['meta']['file']);
        $parts[] = str_replace(DIRECTORY_SEPARATOR, '!', $file);
        $parts[] = 'property';
        $parts[] = $classdata['name'] . '::' . $data['name'];
        $parts[] = $this->meta['version'];

        return join("/", $parts);
    }

    function getObjectURL($data)
    {
        $parts = array();
        $parts[] = $this->getSiteURL($data);
        $parts[] = $this->meta['component'];

        $file = str_replace($this->meta['basePath'], '', $data['meta']['file']);
        $parts[] = str_replace(DIRECTORY_SEPARATOR, '!', $file);
        $parts[] = $data['type'];
        $parts[] = $data['name'];
        $parts[] = $this->meta['version'];

        return join("/", $parts);

    }
}
