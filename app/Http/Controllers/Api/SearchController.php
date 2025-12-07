<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search across the entire website
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('q', $request->input('search', ''));
        
        if (empty($searchTerm)) {
            return response()->json([
                'blogs' => [],
                'categories' => [],
                'pages' => [],
                'total' => 0
            ]);
        }

        $results = [
            'blogs' => $this->searchBlogs($searchTerm),
            'categories' => $this->searchCategories($searchTerm),
            'pages' => $this->searchPages($searchTerm),
        ];

        $results['total'] = count($results['blogs']) + count($results['categories']) + count($results['pages']);

        return response()->json($results);
    }

    /**
     * Search blogs
     */
    private function searchBlogs(string $searchTerm): array
    {
        $blogs = Blog::with('category', 'user')
            ->where('status', 'published')
            ->where(function($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('content', 'like', "%{$searchTerm}%")
                      ->orWhereHas('category', function($q) use ($searchTerm) {
                          $q->where('name', 'like', "%{$searchTerm}%");
                      });
            })
            ->get()
            ->map(function($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'excerpt' => $this->extractExcerpt($blog->content, 150),
                    'image' => $blog->image,
                    'category' => $blog->category ? $blog->category->name : null,
                    'url' => "/insights/{$blog->slug}",
                    'type' => 'blog',
                    'created_at' => $blog->created_at,
                ];
            })
            ->toArray();

        return $blogs;
    }

    /**
     * Search categories
     */
    private function searchCategories(string $searchTerm): array
    {
        $categories = Category::where('name', 'like', "%{$searchTerm}%")
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->name,
                    'slug' => $category->slug,
                    'excerpt' => "Browse all {$category->name} insights",
                    'image' => null,
                    'category' => null,
                    'url' => "/insights?category={$category->slug}",
                    'type' => 'category',
                    'created_at' => $category->created_at,
                ];
            })
            ->toArray();

        return $categories;
    }

    /**
     * Search static pages and services
     */
    private function searchPages(string $searchTerm): array
    {
        $pages = [
            [
                'title' => 'About Us',
                'url' => '/about',
                'excerpt' => 'Learn more about ECR Technology Services',
                'type' => 'page',
                'keywords' => ['about', 'company', 'who we are', 'our story'],
            ],
            [
                'title' => 'Contact Us',
                'url' => '/contact',
                'excerpt' => 'Get in touch with our team',
                'type' => 'page',
                'keywords' => ['contact', 'get in touch', 'reach us', 'email', 'phone'],
            ],
            [
                'title' => 'Book Appointment',
                'url' => '/appointment',
                'excerpt' => 'Schedule a consultation with us',
                'type' => 'page',
                'keywords' => ['appointment', 'book', 'schedule', 'consultation', 'meeting'],
            ],
            [
                'title' => 'Education & Training',
                'url' => '/services/education-training',
                'excerpt' => 'Comprehensive education and training solutions',
                'type' => 'service',
                'keywords' => ['education', 'training', 'learning', 'courses', 'academy'],
            ],
            [
                'title' => 'Software Development',
                'url' => '/services/software-development',
                'excerpt' => 'Custom software development services',
                'type' => 'service',
                'keywords' => ['software', 'development', 'coding', 'programming', 'apps', 'application'],
            ],
            [
                'title' => 'Data Analytics',
                'url' => '/services/data-analytics',
                'excerpt' => 'Data analytics and business intelligence solutions',
                'type' => 'service',
                'keywords' => ['data', 'analytics', 'business intelligence', 'reporting', 'insights'],
            ],
            [
                'title' => 'IT Strategy & Consulting',
                'url' => '/services/it-strategy-consulting',
                'excerpt' => 'Strategic IT consulting and planning',
                'type' => 'service',
                'keywords' => ['it strategy', 'consulting', 'planning', 'strategy', 'it consulting'],
            ],
            [
                'title' => 'Graphics Designing',
                'url' => '/services/graphics-designing',
                'excerpt' => 'Professional graphics and design services',
                'type' => 'service',
                'keywords' => ['graphics', 'design', 'designing', 'visual', 'creative'],
            ],
            [
                'title' => 'Cyber Security Solution',
                'url' => '/services/cyber-security-solution',
                'excerpt' => 'Comprehensive cybersecurity solutions',
                'type' => 'service',
                'keywords' => ['cyber security', 'security', 'cybersecurity', 'protection', 'safe'],
            ],
            [
                'title' => 'All Services',
                'url' => '/services',
                'excerpt' => 'View all our services',
                'type' => 'page',
                'keywords' => ['services', 'what we do', 'offerings', 'solutions'],
            ],
            [
                'title' => 'ECR Insights',
                'url' => '/insights',
                'excerpt' => 'Browse all our blog posts and insights',
                'type' => 'page',
                'keywords' => ['insights', 'blog', 'articles', 'posts', 'news'],
            ],
        ];

        $searchLower = strtolower($searchTerm);
        $matchedPages = [];

        foreach ($pages as $page) {
            $titleMatch = stripos($page['title'], $searchTerm) !== false;
            $excerptMatch = stripos($page['excerpt'], $searchTerm) !== false;
            $keywordMatch = false;
            
            foreach ($page['keywords'] as $keyword) {
                if (stripos($keyword, $searchTerm) !== false || stripos($searchTerm, $keyword) !== false) {
                    $keywordMatch = true;
                    break;
                }
            }

            if ($titleMatch || $excerptMatch || $keywordMatch) {
                $matchedPages[] = [
                    'id' => null,
                    'title' => $page['title'],
                    'slug' => null,
                    'excerpt' => $page['excerpt'],
                    'image' => null,
                    'category' => null,
                    'url' => $page['url'],
                    'type' => $page['type'],
                    'created_at' => null,
                ];
            }
        }

        return $matchedPages;
    }

    /**
     * Extract excerpt from HTML content
     */
    private function extractExcerpt(string $content, int $length = 150): string
    {
        $text = strip_tags($content);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
}

