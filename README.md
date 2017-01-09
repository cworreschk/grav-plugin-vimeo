# Vimeo Plugin

The **Vimeo** Plugin for [Grav CMS](http://github.com/getgrav/grav) converts markdown video links into the [Vimeo](https://vimeo.com) Universal Embed Code.

### Features
- Simply embed Vimeo videos and configure the player
- Override the player parameters for each page
- Button to add Vimeo videos in the content editor
- Twig functions to embed Vimeo videos in your templates
- Multi-Language Support

## Installation

Installing the Vimeo plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install vimeo

This will install the Vimeo plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/vimeo`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `vimeo`. You can find these files on [GitHub](https://github.com/christian-worreschk/grav-plugin-vimeo) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/vimeo
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) and the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) to operate.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/vimeo/vimeo.yaml` to `user/config/plugins/vimeo.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
plugin_css: true
editor_button: true

player_parameters:
  autopause: true
  autoplay: false
  byline: true
  color: "#00adef"
  loop: false
  player_id: ""
  portrait: true
  title: true
```

* `enabled` Toggles if the Vimeo plugin is turned on or off.
* `plugin_css` Toggles if the built in CSS for a responsive layout is used or not.
* `editor_button` Allows you to easily add Vimeo videos in the page content editor.
* `autopause` Enables or disables pausing this video when another video is played.
* `autoplay` Plays the video automatically on load. Note that this won’t work on some devices.
* `byline` Shows the user’s byline on the video.
* `color` Specifies the color of the video controls.
* `loop` Plays the video again when it reaches the end.
* `player_id` A unique id for the player that will be passed back with all Javascript Vimeo API responses.
* `portrait` Shows the user’s portrait on the video.
* `title` Shows the title on the video.

> NOTE: If the owner of the video is a Plus member, `byline`,`color`, `portrait` and `title` may be overridden by their preferences..


You can also set any of these settings on a per-page basis by adding them under a `vimeo:` setting in your page header. For example:
```markdown
---
title: Example page
vimeo:
  player_parameters:
    autoplay: true
---

[plugin:vimeo](https://vimeo.com/123456789)
```

This will embed a video and play it automatically on load.

## Usage

To use this plugin you simply need to include a vimeo URL in markdown link such as:
```markdown
[plugin:vimeo](https://vimeo/123456789)
```

This will be converted into the following embeded HTML:
```html
<div class="grav-vimeo">
  <iframe src="//player.vimeo.com/video/123456789" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
</div>
```

## Twig

This plugin offers two [Twig](http://twig.sensiolabs.org) functions that can be used. `vimeo_embed_url()` and `vimeo_embed_video()`
 
#### vimeo_embed_url( video_id, parameters=[] )
This function builds and returns the Vimeo embed URL like `//player.vimeo.com/video/123456789?autoplay=1`
  - `video_id` : The Vimeo video ID
  - `parameters` : An optional `array` of player parameters. See `player_parameters` in the configuration file.

#### vimeo_embed_video( video_id, parameters=[], div=true )
This function builds and returns the full Vimeo embed iframe snippet.
  - `video_id` : The Vimeo video ID
  - `parameters` : An optional `array` of player parameters. See `player_parameters` in the configuration file.
  - `div` : If set to `true`, the iframe HTML code will be surrounded by a div element with the class `grav-vimeo`. 

## Contributing
The **Vimeo Grav Plugin** follows the [GitFlow branching model](http://nvie.com/posts/a-successful-git-branching-model), from development to release. The ```master``` branch always reflects a production-ready state while the latest development is taking place in the ```develop``` branch.

Each time you want to work on a fix or a new feature, create a new branch based on the ```develop``` branch: ```git checkout -b BRANCH_NAME develop```. Only pull requests to the ```develop``` branch will be merged.

## Copyright and license

Copyright &copy; 2017 Christian Worreschk under the [MIT Licence](http://opensource.org/licenses/MIT). See [README](LICENSE).