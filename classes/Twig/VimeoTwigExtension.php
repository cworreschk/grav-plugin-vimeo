<?php

namespace Grav\Plugin\Vimeo\Twig;

class VimeoTwigExtension extends \Twig_Extension
{

    const VIMEO_PLAYER_URL = '//player.vimeo.com/video/';

    /**
     * Returns extension name.
     * @return string
     */
    public function getName()
    {
        return 'VimeoTwigExtension';
    }

    public function getFunctions()
    {
        return [
          new \Twig_SimpleFunction('vimeo_embed_url', [$this, 'embedUrl']),
        ];
    }

    /**
     * @param string $video_id Vimeo Video ID
     * @param array $params Player parameters
     * @return string
     */
    public function embedUrl($video_id, $params = [])
    {
        $url = static::VIMEO_PLAYER_URL.$video_id;
        if ((!empty($params)) && ($query = http_build_query($params))) {
            $url.= "?{$query}";
        }
        return $url;
    }

}