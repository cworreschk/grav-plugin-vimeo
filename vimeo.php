<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\Vimeo\Twig\VimeoTwigExtension;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class VimeoPlugin
 * @package Grav\Plugin
 */
class VimeoPlugin extends Plugin
{

    protected $locale;

    const VIMEO_REGEX = '(?:\S*)?:?\/{2}(?:\S*)vimeo.com(?:\/video)?\/(\d*)';

    /**
     * Returns a list of events the plugins wants to listen to.
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onAssetsInitialized'  => ['onAssetsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        $this->initializeLocale();

        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) return;

        // Enable the events we are interested in
        $this->enable([
            'onPageContentRaw'    => ['onPageContentRaw', 0],
            'onTwigExtensions'    => ['onTwigExtensions', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
        ]);
    }

    private function initializeLocale() {
        $locales = [];
        $language = $this->grav['language'];

        // Available Languages
        if (isset($this->grav['user']) && $this->grav['user']->authenticated) $locales[] = $this->grav['user']->language;
        if ($language->enabled())$locales[] = $language->getLanguage();
        $locales[] = 'en';

        $locales = array_unique(array_filter($locales));
        foreach ($locales as $locale) {
            if (isset($this->grav['languages'][$locale]['PLUGIN_VIMEO'])) {
                $this->locale = $locale;
                break;
            }
        }
    }

    /**
     * Add Built-in CSS and Editor Button JS if wanted
     */
    public function onAssetsInitialized()
    {
        if (!$this->isAdmin() && $this->config->get('plugins.vimeo.plugin_css')) {
            $this->grav['assets']->add('plugin://vimeo/css/vimeo.css');
        }

        if ($this->isAdmin() && $this->config->get('plugins.vimeo.editor_button')) {
            $plugin_translations = $this->grav['languages'][$this->locale]['PLUGIN_VIMEO'];
            $translations = [
                'EDITOR_BUTTON_TOOLTIP' => $plugin_translations['EDITOR_BUTTON_TOOLTIP'],
                'EDITOR_BUTTON_PROMPT' => $plugin_translations['EDITOR_BUTTON_PROMPT']
            ];
            $code = 'this.GravVimeoPlugin = this.GravVimeoPlugin || {};';
            $code.= 'if (!this.GravVimeoPlugin.translations) this.GravVimeoPlugin.translations = '.json_encode($translations, JSON_UNESCAPED_SLASHES) .';';
            $this->grav['assets']->addInlineJs($code);
            $this->grav['assets']->add('plugin://vimeo/admin/editor-button/js/button.js');
        }
    }

    /**
     * After a page has been found, header processed, but content not processed.
     * @param Event $e Event
     */
    public function onPageContentRaw(Event $e)
    {
        $page = $e['page'];
        $config = $this->mergeConfig($page, true);
        if (!$config->get('enabled')) return;

        // Function
        $twig = $this->grav['twig'];
        $function = function ($matches) use ($twig, $config) {
            $search = $matches[0];

            // double check to make sure we found a valid Vimeo video ID
            if (!isset($matches[1])) return $search;

            // build the replacement embed HTML string
            $replace = $twig->processTemplate('partials/vimeo.html.twig', [
                'video_id' => $matches[1],
                'player_parameters' => $config->get('player_parameters', [])
            ]);

            // do the replacement
            return str_replace($search, $replace, $search);
        };

        $raw_content = $page->getRawContent();
        $page->setRawContent($this->parseLinks($raw_content, $function, static::VIMEO_REGEX));
    }

    /**
     * Add Vimeo twig extension
     */
    public function onTwigExtensions()
    {
        require_once __DIR__ . '/classes/Twig/VimeoTwigExtension.php';
        $this->grav['twig']->twig->addExtension(new VimeoTwigExtension());
    }

    /**
     * Add plugin templates to twig path
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

}
