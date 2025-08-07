<?php

namespace App\Helpers;

use App\Models\CategoryTreeModel;

class BreadcrumbsHelper
{

    public static function getCategoryTrail($categoryId)
    {
        $model = new CategoryTreeModel();
        $categories = $model->findAll();

        // Indexelés unas_id szerint
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat->unas_id] = $cat;
        }

        // Összeállítjuk a láncot a path alapján
        $trail = [];

        $cat = $categoryMap[$categoryId] ?? null;

        if (!$cat || !$cat->path) return [];

        $pathIds = explode('/', $cat->pathIds);

        foreach ($pathIds as $id) {
            if (isset($categoryMap[$id])) {
                $trail[] = $categoryMap[$id];
            }
        }

        return $trail; // Lista: [ [unas_id, name, slug], ... ]
    }

}
