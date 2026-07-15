<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/BlogPost.php';

class BlogController
{
    /**
     * GET /blog — liste des articles publiés
     */
    public function index(): void
    {
        $pageTitle  = "Blog";
        $isLoggedIn = false; // TODO: vraie vérification session/JWT
        $userRole   = null;

        $posts = \BlogPost::getAllPublished();

        ob_start();
        require __DIR__ . '/../../resources/views/pages/blog.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }

    /**
     * GET /blog/{slug} — article complet
     */
    public function show(string $slug): void
    {
        $pageTitle  = "Article";
        $isLoggedIn = false;
        $userRole   = null;

        $post = \BlogPost::findBySlug($slug);

        if (!$post) {
            http_response_code(404);
            echo "Article introuvable.";
            return;
        }

        ob_start();
        require __DIR__ . '/../../resources/views/pages/blog_show.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../resources/views/layouts/main.php';
    }
}