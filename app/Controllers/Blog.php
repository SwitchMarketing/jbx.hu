<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\BuildPage;
use App\Models\BlogModel;

class Blog extends BaseController
{
        
    /**
     * index
     *
	 * a blog bejegyzések lista oldal
	 * 
     * @return void
     */
    public function index()
    {

        // a blog bejegyzések lekérése
		// a lapozóhoz szükséges paraméterek
		$itemsPerPage = 6;

		$page = $this->request->getGet('page') ?? 1;
		$limit = $this->request->getGet('limit') ?? $itemsPerPage;

		$start = ($page * $itemsPerPage) - $itemsPerPage;

		// a blog modelje
		$blogModel = model(BlogModel::class);

		// a legfrissebb bejegyzések a sidebarhoz
		$latestPosts = $blogModel->select('title, slug, published_at')
							->where('published', 'yes')
							->orderBy('published_at', 'DESC')
							->findAll(5);


		// a bejegyzések lekérése
        $posts = $blogModel->where('published', 'yes')
						   ->orderBy('published_at', 'DESC')
						   ->findAll($limit, $start);
		$total = $blogModel->countAllResults(false);

        // a lapozó
		$pager = service('pager');

        $data = [
			'header' => [
				'title'	  => page_title('Blog'),		
				'section' => 'blog'		
            ],
            'body' => [
                'posts' => $posts,
				'latestPosts' => $latestPosts,
                'links' => $pager->makeLinks($page, $limit, $total, 'blog')
            ]
		];

		BuildPage::render('blog', $data);
    }

	public function post($id = null)
    {

		// a blog modelje
		$blogModel = model(BlogModel::class);

		// a bejegyzés lekérése
		$post = $blogModel->where('published', 'yes')->find($id);

		if (!$post) {
			throw new \CodeIgniter\Exceptions\PageNotFoundException('A bejegyzés nem található.');
		}

		// a legfrissebb bejegyzések a sidebarhoz
		$latestPosts = $blogModel->select('title, slug, published_at')
							->where('published', 'yes')
							->orderBy('published_at', 'DESC')
							->findAll(5);

        $data = [
			'header' => [
				'title'	  => page_title('Blog'),		
				'section' => 'blog'		
            ],
            'body' => [
                'post' => $post,
				'latestPosts' => $latestPosts
            ]
		];

		BuildPage::render('blog-post', $data);
    }

}
