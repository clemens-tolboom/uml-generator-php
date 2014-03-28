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
    protected $merge;

    function __construct(Documentation $documenter = null, $merge = false)
    {
        if (empty($documenter)) {
            $documenter = new Documentation();
        }
        $this->documenter = $documenter;
        $this->merge = $merge;
    }

    function getMergedDiagram($array, $index){
        $loadedfiles = [];
        foreach ($array as $values) {
            if (isset($values['implements'])) {
                foreach ($values['implements'] as $implement) {
                    if (isset($file_index[$implement])) {
                        echo $file_index[$implement];
                        if (!isset($loadedfiles[$implement])) {
                            $source = json_decode(file_get_contents($file_index[$implement]), true);
                            $array = array_merge($array, $source);
                            $loadedfiles[$implement] = true;
                        }
                    } else {
                        echo 'Not found: ' . $implement . PHP_EOL;
                    }
                }
            }
            if (isset($values['extends'])) {
                if (isset($file_index[$values['extends']])) {
                    if (!isset($loadedfiles[$values['extends']])) {
                        $array = array_merge($array, json_decode(file_get_contents($file_index[$values['extends']]), true));
                        $loadedfiles[$values['extends']] = true;
                    }
                } else {
                    echo 'Not found: ' . $values['extends'] . PHP_EOL;
                }
            }

        }
        return $this->getClassDiagram($array);
    }

    /**
     * Generate an UML Class Diagram dot file based on the given objects.
     *
     * TODO: links to Name, vars and methods
     *
     * @param $array
     * @return string
     */
    function getClassDiagram($array)
    {
        if (!is_array($array)) return;
        $result = array();

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

            $result[] = "  $safename [";
            $result[] = "    label=<";
            $result[] = '<table border="1" cellpadding="2" cellspacing="0" cellborder="0">';
            $result[] = '<tr><td align="center"' . $fileUrl . ' title="' . $values['type'] . ' ' . $values['name'] . '">' . $values['name'] . '</td></tr><hr />';

            $scope = array(
                'classifier' => '<u>%s</u>',
                'instance' => '%s',
            );
            $scope_tooltip = array(
                // TODO: fix for entity: '&laquo; static &raquo; %s',
                'classifier' => '&lt;&lt; static &gt;&gt; %s',
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

            $properties = array_filter($values['children'], function ($item) {
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
            $methods = array_filter($values['children'], function ($item) {
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

                $result[] = '<tr><td align="left"' . $methodUrl . ' title="' . $t . '">' . $s . '()</td></tr>';

            }
            if (count($methods) == 0) {
                $result[] = '<tr><td>&nbsp;</td></tr>';
            }
            $result[] = '</table>';
            $result[] = "  >";

            $result[] = "  ];";
        }
        foreach ($links as $link) {
            if ($link['type'] == 'extend') {
                $result[] = $link['from'] . ' -> ' . $link['to'] . ' [arrowhead="empty"];' . PHP_EOL;
            } else {
                $result[] = $link['from'] . ' -> ' . $link['to'] . ' [arrowhead="empty" style="dashed"];' . PHP_EOL;
            }
        }
        $result[] = "}";

        return join(PHP_EOL, $result);

    }

    private function getSafeName($namespace)
    {
        $safename = preg_replace('/[^a-zA-Z0-9]/', '_', substr($namespace, 1));
        return $safename;
    }

}
