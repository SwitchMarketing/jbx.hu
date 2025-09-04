<?php

namespace App\Helpers;

use App\Models\BlogModel;

class BlogRouteCache
{
    const CACHE_PATH = WRITEPATH . 'cache/blog_routes.php';

    public static function generate()
    {
        $model = new BlogModel();
        $posts = $model->where('published', 'yes')->findAll();

        $routes = [];

        // bejegyzések
        foreach ($posts as $post) {
            $routePath = trim($post->slug, '/');
            $routes[] = "\$routes->get('blog/$routePath', 'Blog::post/$post->id');";
        }

        $phpCode = "<?php\n\n// AUTO-GENERATED BLOG ROUTES\n";
        $phpCode .= implode("\n", $routes) . "\n";

        file_put_contents(self::CACHE_PATH, $phpCode);
    }

}
