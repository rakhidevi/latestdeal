<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Editorial\EditorialService;
use App\Services\SeoService;
use App\Services\BreadcrumbService;

class EditorialController extends Controller
{
    protected $editorialService;
    protected $seoService;
    protected $breadcrumbService;

    public function __construct(
        EditorialService $editorialService,
        SeoService $seoService,
        BreadcrumbService $breadcrumbService
    ) {
        $this->editorialService = $editorialService;
        $this->seoService = $seoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    /**
     * Display the Editorial Hub (Index of all content or specific type)
     */
    public function index(string $type = null)
    {
        $articles = $this->editorialService->getAll($type);
        
        $title = 'LatestDeal Editorial ' . ($type ? ucfirst($type) . 's' : 'Hub');
        $this->seoService->setTitle($title);
        $this->seoService->setDescription("Read our expert shopping guides, price analysis, and savings tips to get the most out of your online shopping.");
        
        $this->breadcrumbService->add('Home', url('/'));
        $this->breadcrumbService->add('Editorial', route('editorial.index'));
        if ($type) {
            $this->breadcrumbService->add(ucfirst($type) . 's', route('editorial.index', ['type' => $type]));
        }

        return view('editorial.index', [
            'articles' => $articles,
            'type' => $type,
            'breadcrumbs' => $this->breadcrumbService->generate()
        ]);
    }

    /**
     * Display a specific Editorial Article (Blog, Guide, Event)
     */
    public function show(string $type, string $slug)
    {
        $article = $this->editorialService->getArticle($slug, $type);

        if (!$article) {
            abort(404);
        }

        $this->seoService->setTitle($article->title . ' - LatestDeal Guides');
        $this->seoService->setDescription($article->description);
        
        // Add specific schemas
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->description,
            'author' => [
                '@type' => 'Person',
                'name' => $article->author
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'LatestDeal.in',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url('/logo.png')
                ]
            ],
        ];
        
        if ($article->publishedAt) {
            $schema['datePublished'] = $article->publishedAt;
        }
        if ($article->updatedAt) {
            $schema['dateModified'] = $article->updatedAt;
        }
        
        $this->seoService->addSchema('Article', $schema);

        // Breadcrumbs
        $this->breadcrumbService->add('Home', url('/'));
        $this->breadcrumbService->add('Editorial', route('editorial.index'));
        $this->breadcrumbService->add(ucfirst($type) . 's', route('editorial.index', ['type' => $type]));
        $this->breadcrumbService->add($article->title, request()->url());

        // Get Related content
        $relatedArticles = [];
        if (!empty($article->relatedLinks)) {
            foreach ($article->relatedLinks as $relatedSlug) {
                // Try to find the related article in the same category
                $related = $this->editorialService->getArticle($relatedSlug, $type);
                if ($related) {
                    $relatedArticles[] = $related;
                }
            }
        } else {
            // Fallback: grab some other articles of the same type
            $all = $this->editorialService->getAll($type);
            foreach ($all as $other) {
                if ($other->slug !== $article->slug) {
                    $relatedArticles[] = $other;
                    if (count($relatedArticles) >= 3) break;
                }
            }
        }

        return view('editorial.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'breadcrumbs' => $this->breadcrumbService->generate()
        ]);
    }
}
