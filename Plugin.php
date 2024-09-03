<?php namespace SpAnjaan\Sitesearch;

use SpAnjaan\Sitesearch\Models\Settings;
use System\Classes\PluginBase;

/**
 * SiteSearch Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'spanjaan.sitesearch::lang.plugin.name',
            'description' => 'spanjaan.sitesearch::lang.plugin.description',
            'author' => 'spanjaan.sitesearch::lang.plugin.author',
            'icon' => 'icon-search',
            'homepage' => 'https://github.com/wintercms/wn-site-search-plugin',
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'SpAnjaan\Sitesearch\Components\SearchResults' => 'searchResults',
            'SpAnjaan\Sitesearch\Components\SearchInput' => 'searchInput',
            'SpAnjaan\Sitesearch\Components\SiteSearchInclude' => 'siteSearchInclude',
        ];
    }

    /**
     * Registers any back-end permissions.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'spanjaan.sitesearch.manage_settings' => [
                'tab' => 'spanjaan.sitesearch::lang.plugin.name',
                'label' => 'spanjaan.sitesearch::lang.plugin.manage_settings_permission',
            ],
            'spanjaan.sitesearch.view_log' => [
                'tab' => 'spanjaan.sitesearch::lang.plugin.name',
                'label' => 'spanjaan.sitesearch::lang.plugin.view_log_permission',
            ],
        ];
    }

    /**
     * Registers any back-end settings.
     *
     * @return array
     */
    public function registerSettings()
    {
        $settings = [
            'config' => [
                'label' => 'spanjaan.sitesearch::lang.plugin.name',
                'description' => 'spanjaan.sitesearch::lang.plugin.manage_settings',
                'category' => 'system::lang.system.categories.cms',
                'icon' => 'icon-search',
                'class' => 'SpAnjaan\Sitesearch\Models\Settings',
                'order' => 100,
                'keywords' => 'search',
                'permissions' => ['spanjaan.sitesearch.manage_settings'],
            ],
        ];

        if ((bool)Settings::get('log_queries', false) === false) {
            return $settings;
        }

        $settings['querylogs'] = [
            'label' => 'spanjaan.sitesearch::lang.log.title',
            'description' => 'spanjaan.sitesearch::lang.log.description',
            'category' => 'system::lang.system.categories.cms',
            'url' => \Backend::url('spanjaan/sitesearch/querylogs'),
            'keywords' => 'search log query queries',
            'icon' => 'icon-search',
            'permissions' => ['spanjaan.sitesearch.*'],
            'order' => 99,
        ];

        return $settings;
    }
}
