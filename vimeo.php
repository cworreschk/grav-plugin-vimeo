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

    const VIMEO_REGEX = '(?:\S*)?:?\/{2}(?:\S*)vimeo.com(?:\/video)?\/(\d*)';

    /**
     * Returns a list of events the plugins wants to listen to.
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
          'onPluginsInitialized' => ['onPluginsInitialized', 0],
          'onAssetsInitialized' => ['onAssetsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) return;

        // Enable the events we are interested in
        $this->enable([
          'onPageContentRaw' => ['onPageContentRaw', 0],
          'onTwigExtensions'    => ['onTwigExtensions', 0],
          'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
        ]);
    }

    /**
     * Returns the plugins translations for the currently logged in user or by the given language
     * @param string $locale
     * @return array
     */
    private function getPluginTranslations($locale='')
    {
        $locale = empty($locale) ? $this->grav['user']->get('language') : $locale;
        if (!isset($this->grav['languages'][$locale]['PLUGIN_VIMEO'])) $locale = 'en'; // English is the fallback language
        return $this->grav['languages'][$locale]['PLUGIN_VIMEO'];
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
            $plugin_translations = $this->getPluginTranslations(); // Workaround User/System language bug (this->grav['language']->translate doesn't work properly)
            $translations = [
              'EDITOR_BUTTON_TOOLTIP' => $plugin_translations['EDITOR_BUTTON_TOOLTIP'],
              'EDITOR_BUTTON_PROMPT' => $plugin_translations['EDITOR_BUTTON_PROMPT']
            ];
            $code = 'this.GravAdmin.translations.PLUGIN_VIMEO = '. json_encode($translations, JSON_UNESCAPED_SLASHES) .';';
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
              'video_id'   => $matches[1],
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
