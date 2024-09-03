<?php

namespace SpAnjaan\Sitesearch\Classes;


use Cms\Classes\Controller;
use Event;
use Illuminate\Support\Collection;
use LogicException;

// Uncomment your required Providers here
// use SpAnjaan\Sitesearch\Classes\Providers\ArrizalaminPortfolioResultsProvider;
use SpAnjaan\Sitesearch\Classes\Providers\CmsPagesResultsProvider;
// use SpAnjaan\Sitesearch\Classes\Providers\FeeglewebOctoshopProductsResultsProvider;
use SpAnjaan\Sitesearch\Classes\Providers\GenericResultsProvider;
// use SpAnjaan\Sitesearch\Classes\Providers\GrakerPhotoAlbumsResultsProvider;
// use SpAnjaan\Sitesearch\Classes\Providers\IndikatorNewsResultsProvider;
// use SpAnjaan\Sitesearch\Classes\Providers\JiriJKShopResultsProvider;
// use SpAnjaan\Sitesearch\Classes\Providers\OfflineSnipcartShopResultsProvider;
// use SpAnjaan\Sitesearch\Classes\Providers\RadiantWebProBlogResultsProvider;
use SpAnjaan\Sitesearch\Classes\Providers\WinterBlogResultsProvider;
use SpAnjaan\Sitesearch\Classes\Providers\WinterPagesResultsProvider;
use SpAnjaan\Sitesearch\Classes\Providers\ResponsivShowcaseResultsProvider;
use SpAnjaan\Sitesearch\Classes\Providers\ResultsProvider;
// use SpAnjaan\Sitesearch\Classes\Providers\VojtaSvobodaBrandsResultsProvider;
use SpAnjaan\Sitesearch\Models\QueryLog;
use SpAnjaan\Sitesearch\Models\Settings;

class SearchService
{
    /**
     * @var string
     */
    public $query;
    /**
     * @var Controller
     */
    public $controller;
    /**
     * @var bool
     */
    public $logQueries;

    public function __construct($query, $controller = null)
    {
        $this->query      = $query;
        $this->controller = $controller ?: new Controller();
        $this->logQueries = Settings::get('log_queries', false);
    }

    /**
     * Fetch all available results for the provided query
     *
     * @return ResultCollection
     * @throws \DomainException
     */
    public function results()
    {
        $this->logQuery($this->query);

        $resultsCollection = new ResultCollection();
        $resultsCollection->setQuery($this->query);

        if (trim($this->query) === '') {
            return $resultsCollection;
        }

        $results = $this->resultsProviders();

        $results = $results->map(function (ResultsProvider $provider) {
            $provider->setQuery($this->query);
            $provider->search();

            return $provider->results();
        });

        $resultsCollection->addMany($results->toArray());

        $modified = Event::fire('spanjaan.sitesearch.results', $resultsCollection);

        $modified = array_filter($modified);

        return count($modified) > 0 ? $modified[0] : $resultsCollection->sortByDesc('relevance');
    }

    /**
     * Returns all native and the additional results providers.
     *
     * @return Collection
     */
    protected function resultsProviders()
    {
        return collect($this->nativeResultsProviders())
            ->merge($this->additionalResultsProviders());
    }

    /**
     * Return all natively supported results providers.
     *
     * @return ResultsProvider[]
     */
    protected function nativeResultsProviders()
    {
        return [
            // Uncomment the required Provider here

            // new OfflineSnipcartShopResultsProvider(),
            // new RadiantWebProBlogResultsProvider($this->query, $this->controller),
            // new FeeglewebOctoshopProductsResultsProvider(),
            // new JiriJKShopResultsProvider(),
            // new IndikatorNewsResultsProvider(),
            // new ArrizalaminPortfolioResultsProvider(),
            new ResponsivShowcaseResultsProvider(),
            new WinterBlogResultsProvider($this->query, $this->controller),
            new WinterPagesResultsProvider(),
            new CmsPagesResultsProvider(),
            new GenericResultsProvider(),
            // new VojtaSvobodaBrandsResultsProvider(),
            // new GrakerPhotoAlbumsResultsProvider($this->query, $this->controller),
        ];
    }

    /**
     * Gather all additional ResultsProviders that
     * are registered by other plugins.
     *
     * @return ResultsProvider[]
     * @throws \LogicException
     */
    protected function additionalResultsProviders()
    {
        $returns = collect(Event::fire('spanjaan.sitesearch.extend'))->filter()->flatten();

        $returns->each(function ($return) {
            if ( ! $return instanceof ResultsProvider) {
                throw new LogicException('The spanjaan.sitesearch.extend listener needs to return a ResultsProvider instance.');
            }
        });

        return $returns->toArray();
    }

    /**
     * Log the current query.
     *
     * @return void
     */
    protected function logQuery($query)
    {
        if ( ! $this->logQueries || ! $query) {
            return;
        }

        QueryLog::cleanup();
        QueryLog::create([
            'query' => $query
        ]);
    }
}
