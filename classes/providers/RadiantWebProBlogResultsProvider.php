<?php

namespace SpAnjaan\Sitesearch\Classes\Providers;

use Carbon\Carbon;
use Cms\Classes\Controller;
use Html;
use Illuminate\Database\Eloquent\Collection;
use SpAnjaan\Sitesearch\Classes\Result;
use SpAnjaan\Sitesearch\Models\Settings as SiteSearchSettings;
use Radiantweb\Problog\Models\Post;
use Radiantweb\Problog\Models\Settings as ProBlogSettings;

/**
 * Searches the contents generated by the
 * Radiantweb.Problog plugin
 *
 * @package SpAnjaan\Sitesearch\Classes\Providers
 */
class RadiantWebProBlogResultsProvider extends ResultsProvider
{
    /**
     * @var Controller to be used to form urls to search results
     */
    protected $controller;

    /**
     * ResultsProvider constructor.
     *
     * @param                         $query
     * @param \Cms\Classes\Controller $controller
     */
    public function __construct($query, Controller $controller)
    {
        parent::__construct($query);
        $this->controller = $controller;
    }

    /**
     * Runs the search for this provider.
     *
     * @return ResultsProvider
     */
    public function search()
    {
        if ( ! $this->isInstalledAndEnabled()) {
            return $this;
        }

        foreach ($this->posts() as $post) {
            // Make this result more relevant, if the query is found in the title
            $relevance = mb_stripos($post->title, $this->query) === false ? 1 : 2;

            if ($relevance > 1 && $post->published_at) {
                if ( ! $post->published_at instanceof Carbon) {
                    $post->published_at = Carbon::createFromFormat('Y-m-d H:i:s', $post->published_at);
                }
                $relevance -= $this->getAgePenalty($post->published_at->diffInDays(Carbon::now()));
            }

            $result        = new Result($this->query, $relevance);
            $result->title = $post->title;
            $result->text  = $this->getSummary($post);
            $result->url   = $this->getUrl($post);
            $result->thumb = $this->getThumb($post->featured_images);
            $result->model = $post;

            $this->addResult($result);
        }

        return $this;
    }

    /**
     * Get all posts with matching title or content.
     *
     * @return Collection
     */
    protected function posts()
    {
        return Post::with(['categories', 'featured_images'])
                   ->isPublished()
                   ->where(function ($query) {
                       $query->where('title', 'like', "%{$this->query}%");
                       $query->orWhere('content', 'like', "%{$this->query}%");
                       $query->orWhere('excerpt', 'like', "%{$this->query}%");
                   })
                   ->get();
    }

    /**
     * Checks if the Radiantweb.Problog Plugin is installed and
     * enabled in the config.
     *
     * @return bool
     */
    protected function isInstalledAndEnabled()
    {
        return $this->isPluginAvailable($this->identifier)
            && SiteSearchSettings::get('radiantweb_problog_enabled', true);
    }

    /**
     * Generates the url to a blog post.
     *
     * @param $post
     *
     * @return string
     */
    protected function getUrl($post)
    {
        $page = $post->parent ?: ProBlogSettings::instance()->get('blogPost');

        $parameters = ['slug' => $post->slug];
        if ($post->categories) {
            $parameters['filter'] = $post->categories->slug;
        }

        return $this->controller->pageUrl($page, $parameters);
    }

    /**
     * Get the post's excerpt if available
     * otherwise fall back to a limited content string.
     *
     * @param $post
     *
     * @return string
     */
    private function getSummary($post)
    {
        $excerpt = $post->excerpt;
        if (mb_strlen(trim($excerpt))) {
            return $excerpt;
        }

        $content = $post->content;
        if (ProBlogSettings::get('markdownMode')) {
            $content = Post::formatHtml($content);
        }

        return $content;
    }

    /**
     * Display name for this provider.
     *
     * @return mixed
     */
    public function displayName()
    {
        return SiteSearchSettings::get('radiantweb_problog_label', 'Blog');
    }

    /**
     * Return the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'Radiantweb.Problog';
    }
}
