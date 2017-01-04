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
          'onPluginsInitialized' => ['onPluginsInitialized', 0]
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
          'onAssetsInitialized' => ['onAssetsInitialized', 0],
          'onTwigExtensions'    => ['onTwigExtensions', 0],
          'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
        ]);
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
     * Add Built-in CSS if wanted
     */
    public function onAssetsInitialized()
    {
        if (!$this->isAdmin() && $this->config->get('plugins.vimeo.built_in_css')) {
            $this->grav['assets']->add('plugin://vimeo/css/vimeo.css');
        }
    }

    /**
     * Add plugin templates to twig path
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}
