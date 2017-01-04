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
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
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

        $this->enable([
          'onPageContentRaw' => ['onPageContentRaw', 0],
          'onAssetsInitialized' => ['onAssetsInitialized', 0],
          'onTwigExtensions'    => ['onTwigExtensions', 0],
          'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
        ]);
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
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
              'parameters' => $config->get('parameters'),
              'video_id'   => $matches[1],
            ]);

            // do the replacement
            return str_replace($search, $replace, $search);
        };

        $raw_content = $page->getRawContent();
        $page->setRawContent($this->parseLinks($raw_content, $function, static::VIMEO_REGEX));
    }

    /**
     * Add Vimeo Twig Extension
     */
    public function onTwigExtensions()
    {
        require_once __DIR__ . '/classes/Twig/VimeoTwigExtension.php';
        $this->grav['twig']->twig->addExtension(new VimeoTwigExtension());
    }

    public function onAssetsInitialized()
    {
        if (!$this->isAdmin() && $this->config->get('plugins.vimeo.built_in_css')) {
            $this->grav['assets']->add('plugin://vimeo/css/vimeo.css');
        }
    }

    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}
