<?php

namespace App\Service;

use Symfony\Component\Config\Definition\Exception\Exception;

class GenTreeGenerator
{
    /**
     * @param string $input Путь до файла исходника
     * @param string $output Путь до сгенерированного файла
     * @var array $relatedItems - список элементов используемых для связей
     */
    public function generateJSON(string $input, string $output): void
    {
        $data = $this->readFromCsv($input);

        $tree = $this->generateTree($data);

        $this->writeToJson($output, $tree);
    }

    public function readFromCsv(string $path): array
    {
        $result = [];
        if (!file_exists($path)) {
            throw new Exception('Файл ' . $path . ' не существует!');
        }
        if (!is_readable($path)) {
            throw new Exception('Проблема с правами доступа к файлу ' . $path);
        }
        $handle = fopen($path, "r");

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            list($name, $type, $parent, $relation) = $data;
            $result[] = [
                'itemName' => $name,
                'type' => $type,
                'parent' => $parent ?: null,
                'relation' => $relation ?: null,
                'children' => []
            ];
        }

        fclose($handle);

        unset($result[0]);

        return array_values($result);
    }

    private function generateTree(array $data): array
    {
        $tree = $this->sortByParents($data);

        $relations = $this->getRelationsList($tree, $data);

        $this->addRelations($tree, $relations);

        return $tree;
    }


    /**
     * Метод для сортировки списка по родителському элементу - построение дерева
     */
    private function sortByParents(array $data): array
    {
        foreach ($data as &$el) {
            if ($el['parent']) {
                $this->updateArray($data, $el);
            }
        }

        return array_values(array_filter($data));
    }

    /**
     * Вспомогательный метод для перемещения элемента к родителю
     */
    private function updateArray(&$arr, &$el): void
    {
        if (is_array($arr)) {
            if (isset($arr['itemName']) && isset($el['parent']) && $arr['itemName'] == $el['parent']) {
                $arr['children'][] = $el;
                $el = [];
                return;
            }
            foreach ($arr as &$values) {
                if (is_array($values)) {
                    $this->updateArray($values, $el);
                }
            }
        }
    }

    /**
     * Метода для получения списка item-ов с соотнесением дочерних элементов
     * согласно выстроенному $tree
     */
    private function getRelationsList($tree, $data): array
    {
        $list = [];

        foreach ($data as $el) {
            if (!empty($el['relation']) && !in_array($el['relation'], $list)) {
                $list[] = $el['relation'];
            }
        }

        return $this->getRelationChildsList($tree, $list);

    }

    /**
     * Метод для соотносения Relation item-ов их children
     */
    private function getRelationChildsList(array $tree, array $list): array
    {
        $result = [];
        foreach ($list as $item) {
            $result[$item] = $this->getChildByItem($tree, $item);
        }

        return $result;
    }

    /**
     * Метод для получения children по item-у
     */
    private function getChildByItem($arr, $item): ?array
    {
        if (is_array($arr)) {
            if (isset($arr['itemName']) && $arr['itemName'] == $item) {
                return $arr['children'];
            }
            foreach ($arr as $values) {
                if (is_array($values)) {
                    $result = $this->getChildByItem($values, $item);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Метод для добавления children в элементы дерева согласно $relations
     */
    private function addRelations(&$tree, $relations): void
    {
        if (is_array($tree)) {
            if (isset($tree['relation']) && !empty($tree['relation'])) {
                $tree['children'] = $relations[$tree['relation']];
                if (!empty($tree['children'])) {
                    $tree['children'][0]['parent'] = $tree['itemName'];
                }
            }
            unset($tree['relation']);
            unset($tree['type']);

            foreach ($tree as &$v) {
                if (is_array($v)) {
                    $this->addRelations($v, $relations);
                }
            }
        }
    }

    private function writeToJson(string $path, array $data): void
    {
        if (file_exists($path) && !is_writable($path)) {
            throw new Exception('Проблема с правами доступа к файлу ' . $path);
        }
        $handle = fopen($path, "w");
        fwrite($handle, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fclose($handle);
    }
}