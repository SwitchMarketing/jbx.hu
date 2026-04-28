<?php

namespace App\Helpers;

use App\Models\CategoryTreeModel;

class CategoryRouteCache
{
    const CACHE_PATH = WRITEPATH . 'cache/category_routes.php';

    public static function generate()
    {
        $model = new CategoryTreeModel();
        $categories = $model->getAllOrdered();

        $routes = [];

        // kategóriák
        foreach ($categories as $cat) {
            $slugPath = self::getSlugPath($cat, $categories);
            $routePath = trim($slugPath, '/');
            $routes[] = "\$routes->get('termekek/$routePath', 'ShopProducts::index/$cat->unas_id');";
        }

        // kategóriák termékoldalai
        foreach ($categories as $cat) {
            $slugPath = self::getSlugPath($cat, $categories);
            $routePath = trim($slugPath, '/');
            $routes[] = "\$routes->get('termekek/$routePath/(:segment)/(:segment)', 'ShopProducts::product/$1/$2');";
        }

        $phpCode = "<?php\n\n// AUTO-GENERATED CATEGORY ROUTES\n";
        $phpCode .= implode("\n", $routes) . "\n";

        file_put_contents(self::CACHE_PATH, $phpCode);
    }

        
    /**
     * getSlugPath
     *
     * @param  mixed $cat
     * @param  mixed $all
     * @return string
     */
    private static function getSlugPath($cat, $all)
    {
        $map = [];
        foreach ($all as $c) {
            $map[$c->unas_id] = $c;
        }

        $segments = [];
        while ($cat) {
            $segments[] = $cat->slug;
            $cat = isset($map[$cat->parent_id]) ? $map[$cat->parent_id] : null;
        }

        return implode('/', array_reverse($segments));
    }
}
