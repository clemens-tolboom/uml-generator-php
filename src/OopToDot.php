<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 21-03-14
 * Time: 10:49
 */

namespace UmlGeneratorPhp;

class OopToDot
{
    protected $documenter = null;

    function __construct(Documentation $documenter = null)
    {
        if (empty($documenter)) {
            $documenter = new Documentation();
        }
        $this->documenter = $documenter;
    }

    /**
     * Merge parents into diagram.
     *
     * By using the file index we add all parents into the diagram.
     *
     * @param $array
     * @param $file_index
     * @return string
     */
    function getMergedDiagram($array, $file_index, $limit, $legacy)
    {
        $array = $this->loadParentDiagram($array, $file_index, $limit);
        return $this->getClassDiagram($array, $legacy);
    }

    function loadParentDiagram($array, $file_index, $limit, $loaded_files = [])
    {
        if ($limit > 0 && is_array($array)) {
            foreach ($array as $values) {
                if (isset($values['implements'])) {
                    foreach ($values['implements'] as $implement) {
                        if (isset($file_index[$implement])) {
                            if (!isset($loaded_files[$implement])) {
                                $source = json_decode(file_get_contents($file_index[$implement]['file']), true);
                                $loaded_files[$implement] = true;
                                $source = $this->loadParentDiagram($source, $file_index, $limit - 1, $loaded_files);
                                $array = array_merge($array, $source);
                            }
                        } else {
                            echo 'Not found: ' . $implement . PHP_EOL;
                            $loaded_files[$implement] = true;
                        }
                    }
                }
                if (isset($values['traits'])) {
                    foreach ($values['traits'] as $trait) {
                        if (isset($file_index[$trait])) {
                            if (!isset($loaded_files[$trait])) {
                                $source = json_decode(file_get_contents($file_index[$trait]['file']), true);
                                $loaded_files[$trait] = true;
                                $source = $this->loadParentDiagram($source, $file_index, $limit - 1, $loaded_files);
                                $array = array_merge($array, $source);
                            }
                        } else {
                            echo 'Not found: ' . $trait . PHP_EOL;
                            $loaded_files[$trait] = true;
                        }
                    }
                }
                if (isset($values['extends'])) {
                    if (isset($file_index[$values['extends']])) {
                        if (!isset($loaded_files[$values['extends']])) {
                            $source = json_decode(file_get_contents($file_index[$values['extends']]['file']), true);
                            $loaded_files[$values['extends']] = true;
                            $source = $this->loadParentDiagram($source, $file_index, $limit - 1, $loaded_files);
                            $array = array_merge($array, $source);

                        }
                    } else {
                        echo 'Not found: ' . $values['extends'] . PHP_EOL;
                        $loaded_files[$values['extends']] = true;
                    }
                }
            }

        }
        return $array;
    }

    /**
     * Generate an UML Class Diagram dot file based on the given objects.
     *
     * TODO: links to Name, vars and methods
     *
     * @param $array
     * @return string
     */
    function getClassDiagram($array, $legacy)
    {
        if (!is_array($array)) return;
        $result = array();

        $laquo = '&laquo;';
        $raquo = '&raquo;';

        if ($legacy) {
            $laquo = '&lt;&lt;';
            $raquo = '&gt;&gt;';
        }

        $result[] = 'digraph "Class Diagram" {';
        $result[] = "  node [shape=plaintext]";
        $links = [];
        foreach ($array as $index => $values) {
            $meta = $values['meta'];

            $fileUrl = $this->documenter->getObjectURL($values);
            if (!empty($fileUrl)) {
                $fileUrl = ' href="' . $fileUrl . '"';
            }
            $safename = $this->getSafeName($values['namespace'] . '\\' . $values['name']);
            if (isset($values['implements'])) {
                foreach ($values['implements'] as $implement) {
                    $links[$this->getSafeName($implement) . $safename] = [
                      'from' => $this->getSafeName($implement),
                      'to' => $safename,
                      'type' => 'implement'
                    ];
                }
            }
            if (isset($values['extends'])) {
                $links[$this->getSafeName($values['extends']) . $safename] = [
                  'from' => $this->getSafeName($values['extends']),
                  'to' => $safename,
                  'type' => 'extend'
                ];
            }
            if (isset($values['traits'])) {
                foreach ($values['traits'] as $trait) {
                    $links[$this->getSafeName($trait) . $safename] = [
                      'from' => $this->getSafeName($trait),
                      'to' => $safename,
                      'type' => 'trait'
                    ];
                }
            }

            $result[] = "  $safename [";
            $result[] = "    label=<";
            $result[] = '<table border="1" cellpadding="2" cellspacing="0" cellborder="0">';
            $escaped = str_replace('\\', '\\\\', $values['namespace'] . '\\' . $values['name']);
            $result[] = '<tr><td align="center">' . $laquo . ' ' . $values['type'] . ' ' . $raquo . '</td></tr>';

            $result[] = '<tr><td align="center"' . $fileUrl . ' title="' . $values['type'] . ' ' . $values['name'] . '">' . $escaped . '</td></tr><hr />';

            $scope = array(
              'classifier' => '<u>%s</u>',
              'instance' => '%s',
            );
            $scope_tooltip = array(
                // TODO: fix for entity: '&laquo; static &raquo; %s',
              'classifier' => $laquo . ' static ' . $raquo . ' %s',
              'instance' => '%s',
            );

            $visibility = array(
              'public' => '+ %s',
              'protected' => '# %s',
              'private' => '- %s',
            );
            $visibility_tooltip = array(
              'public' => 'public %s',
              'protected' => 'protected %s',
              'private' => 'private %s',
            );
            // TODO: refactor for both attributes and methods
            //       both use similar (same) filter and sort anon function

            // Get attributes
            $properties = array_filter($values['children'], function ($item) {
                if (!is_array($item)){
                  return FALSE;
                }
                return $item['type'] == 'attribute';
            });
            uasort($properties, function ($a, $b) {
                if ($a['visibility'] <> $b['visibility']) {
                    // public before protected before private
                    return ($a['visibility'] > $b['visibility']) ? -1 : 1;
                }
                if ($a['scope'] <> $b['scope']) {
                    // classifiers before instance
                    return ($a['scope'] < $b['scope']) ? -1 : 1;
                }
                return 0;
            });

            foreach ($properties as $property) {
                $s = sprintf($visibility[$property['visibility']], $property['name']);
                $s = sprintf($scope[$property['scope']], $s);
                $t = sprintf($visibility_tooltip[$property['visibility']], $property['name']);
                $t = sprintf($scope_tooltip[$property['scope']], $t);

                $propertyUrl = $this->documenter->getPropertyURL($property, $values);
                if (!empty($propertyUrl)) {
                    $propertyUrl = ' href="' . $propertyUrl . '"';
                }
                $result[] = '<tr><td align="left"' . $propertyUrl . ' title="' . $t . '">' . $s . '</td></tr>';
            }
            if (count($properties) == 0) {
                $result[] = '<tr><td></td></tr>';
            }
            $result[] = '<hr />';
            // Get methods
            $methods = array_filter($values['children'], function ($item) {
                if (!is_array($item)){
                  return FALSE;
                }
                return $item['type'] == 'method';
            });
            uasort($methods, function ($a, $b) {
                if ($a['visibility'] <> $b['visibility']) {
                    // public before protected before private
                    return ($a['visibility'] > $b['visibility']) ? -1 : 1;
                }
                if ($a['scope'] <> $b['scope']) {
                    // classifiers before instance
                    return ($a['scope'] < $b['scope']) ? -1 : 1;
                }
                return 0;
            });

            foreach ($methods as $method) {
                $s = sprintf($visibility[$method['visibility']], $method['name']);
                $s = sprintf($scope[$method['scope']], $s);
                $t = sprintf($visibility_tooltip[$method['visibility']], $method['name']);
                $t = sprintf($scope_tooltip[$method['scope']], $t);

                $methodUrl = $this->documenter->getMethodURL($method, $values);
                if (!empty($methodUrl)) {
                    $methodUrl = ' href="' . $methodUrl . '"';

                }
                $parameters = array();
                foreach ($method['parameters'] as $parameter) {
                    if ($parameter['type'] == null) {
                        $parameter['type'] = 'mixed';
                    }
                    $parameters[] = $parameter['name'] . ' : ' . $parameter['type'];
                }
                $parameters = implode(', ', $parameters);
                $result[] = '<tr><td align="left"' . $methodUrl . ' title="' . $t . '">' . $s . '(' . $parameters . ')</td></tr>';

            }
            if (count($methods) == 0) {
                $result[] = '<tr><td>&nbsp;</td></tr>';
            }
            $result[] = '</table>';
            $result[] = "  >";

            $result[] = "  ];";
        }
        foreach ($links as $link) {
            switch ($link['type']) {
                case "extend":
                    $result[] = $link['from'] . ' -> ' . $link['to'] . ' [arrowhead="empty"];' . PHP_EOL;
                    break;
                case "implement":
                    $result[] = $link['from'] . ' -> ' . $link['to'] . ' [arrowhead="empty" style="dashed"];' . PHP_EOL;
                    break;
                case "trait":
                    $result[] = $link['from'] . ' -> ' . $link['to'] . ' [arrowhead="empty" style="dotted"];' . PHP_EOL;
                    break;
            }
        }
        $result[] = "}";

        return join(PHP_EOL, $result);

    }

    private function getSafeName($namespace)
    {
        $safename = preg_replace('/[^a-zA-Z0-9\\\\]/', '_', substr($namespace, 1));
        $safename = addslashes($safename);
        return '"\\\\' . $safename . '"';
    }

}
