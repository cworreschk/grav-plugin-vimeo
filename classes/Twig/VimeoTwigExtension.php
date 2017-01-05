<?php

namespace Grav\Plugin\Vimeo\Twig;

use Grav\Common\GravTrait;

/**
 * Class VimeoTwigExtension
 * @package Grav\Plugin\Vimeo\Twig
 */
class VimeoTwigExtension extends \Twig_Extension
{

    use GravTrait;

    const VIMEO_PLAYER_URL = '//player.vimeo.com/video/';

    /**
     * Filters player parameters to only those not matching Vimeo defaults
     * @param array $params Player parameters
     * @return array
     */
    private function filterParameters($params=[])
    {
        $filtered = [];
        $grav = static::getGrav();
        $blueprints = $grav['config']->blueprints();
        foreach ($params as $key => $value) {

            // Skip if string value is empty
            if (is_string($value)){
                $value = trim($value);
                if (empty($value)) continue;
            }

            // Skip if there is an default value and it's different to the looping parameter value
            $blueprint_param = $blueprints->get("plugins.vimeo.player_parameters.{$key}");
            if (isset($blueprint_param['default']) && ($blueprint_param['default'] == $value)) {
              continue;
            }

            if ($key == 'color') $value = str_replace('#', '', $value); // Remove # from hexadecimal color value

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * Returns an array of available twig functions
     * @return array
     */
    public function getFunctions()
    {
        return [
          new \Twig_SimpleFunction('vimeo_embed_url', [$this, 'embedUrl']),
          new \Twig_SimpleFunction('vimeo_embed_video', [$this, 'embedVideo']),
        ];
    }

    /**
     * Builds the Vimeo embed url
     * @param string $video_id Vimeo Video ID
     * @param array $params Player parameters
     * @return string
     */
    public function embedUrl($video_id, $params = [])
    {
        $url = static::VIMEO_PLAYER_URL.$video_id;
        $params = $this->filterParameters($params);
        if ((!empty($params)) && ($query = http_build_query($params))) $url.= "?{$query}";

        return $url;
    }

    /**
     * Builds the Vimeo embed iframe
     * @param string $video_id Vimeo Video ID
     * @param array $params Player parameters
     * @param bool $div Surrounds the iframe code with a div element
     * @return string
     */
    public function embedVideo($video_id, $params = [], $div=true)
    {
        $url = $this->embedUrl($video_id, $params);
        $code = "<iframe src=\"{$url}\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
        if ($div) $code = "<div class=\"grav-vimeo\">{$code}</div>";

        return $code;
    }
}