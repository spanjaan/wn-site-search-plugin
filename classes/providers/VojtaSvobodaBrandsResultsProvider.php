<?php namespace SpAnjaan\Sitesearch\Classes\Providers;

use Illuminate\Database\Eloquent\Collection;
use SpAnjaan\Sitesearch\Classes\Result;
use SpAnjaan\Sitesearch\Models\Settings;
use VojtaSvoboda\Brands\Models\Brand;

/**
 * Searches the contents generated by the VojtaSvoboda.Brands plugin
 *
 * @package SpAnjaan\Sitesearch\Classes\Providers
 */
class VojtaSvobodaBrandsResultsProvider extends ResultsProvider
{
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

        foreach ($this->items() as $item) {
            // Make this result more relevant, if the query is found in the title
            $relevance = mb_stripos($item->name, $this->query) === false ? 1 : 2;

            $result        = new Result($this->query, $relevance);
            $result->title = $item->name;
            $result->text  = $item->description;
            $result->url   = $this->getUrl($item);
            $result->thumb = $this->getThumb($item->logo);
            $result->model = $item;

            $this->addResult($result);
        }

        return $this;
    }

    /**
     * Get all posts with matching title or content.
     *
     * @return Collection
     */
    protected function items()
    {
        return Brand::with(['logo'])
                   ->where('name', 'like', "%{$this->query}%")
                   ->orWhere('description', 'like', "%{$this->query}%")
                   ->get();
    }

    /**
     * Checks if the VojtaSvoboda.Brands Plugin is installed and
     * enabled in the config.
     *
     * @return bool
     */
    protected function isInstalledAndEnabled()
    {
        return $this->isPluginAvailable($this->identifier)
        && Settings::get('vojtasvoboda_brands_enabled', true);
    }

    /**
     * Generates the url to a brand.
     *
     * @param $post
     *
     * @return string
     */
    protected function getUrl($post)
    {
        $url        = trim(Settings::get('vojtasvoboda_brands_url', '/brand'), '/');
        $langPrefix = $this->translator ? $this->translator->getLocale() : '';

        return implode('/', [$langPrefix, $url, $post->slug]);
    }

    /**
     * Display name for this provider.
     *
     * @return mixed
     */
    public function displayName()
    {
        return Settings::get('vojtasvoboda_brands_label', 'Brands');
    }

    /**
     * Return the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'VojtaSvoboda.Brands';
    }
}
