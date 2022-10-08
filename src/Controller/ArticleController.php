<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route(
    path: '/{_locale}/it-news',
    name: 'blog',
    requirements: [
        '_locale' => 'en|fr',
    ],
)]
class ArticleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}
    
    #[Route('/', name: '_index')]
    public function index(Request $request): Response
    {
        $locale = $request->getLocale();
        $page = 1;
        $category = 'all';
        $orderBy = 'desc';
        $qry = null;

        if ($request->get('page') !== null) {
            $page = (int) $request->get('page');
        }

        $category = ($request->get('category') !== 'all') ? $request->get('category') : null;
        $orderBy = $request->get('order');
        $qry = $request->get('qry');

        $articles = $this->entityManager->getRepository(Article::class)->findAllByOrderDesc($locale, $page, $category, $orderBy, $qry);
        $totalArticles = count($articles);

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'totalItems' => $totalArticles,
            'total' => ceil(($totalArticles/10)),
            'current' => $page,
            'category' => $category,
            'order' => $orderBy,
            'qry' => $qry,
            'page' => $page
        ]);
    }

    #[Route('/article/{uri}', name: '_article')]
    public function article(Request $request, string $uri): Response
    {
        $lang = ($request->getLocale() === 'fr') ? 'french' : 'english';
        /* @var Article $article */
        $article = $this->entityManager->getRepository(Article::class)->findOneBy(['uri' => $uri, 'language' => $lang]);
        return $this->render('article/article.html.twig', [
            'article' => $article
        ]);
    }
}
