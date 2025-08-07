<?php

namespace App\Helpers;

use App\Models\CategoryModel;

class CategoryHelper
{
    public static function getDescendantIds($parentId): array
    {
        $model = new CategoryModel();
        $all = $model->findAll();

        $tree = [];
        foreach ($all as $cat) {
            $tree[$cat->parent_id][] = $cat->unas_id;
        }

        $result = [$parentId];

        $stack = [$parentId];

        while (!empty($stack)) {
            $current = array_pop($stack);
            if (isset($tree[$current])) {
                foreach ($tree[$current] as $childId) {
                    $result[] = $childId;
                    $stack[] = $childId;
                }
            }
        }

        return $result;
    }
}
