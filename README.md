# tweetable-text #

A WordPress plugin that lets you choose specific phrases or sentences for one-click tweeting.

Based on <a href="http://wordpress.org/plugins/tweetable-text/">Tweetable Text</a> by Salim Virani, with additions by Joshua Benton of Nieman Lab.

Lets you highlight specific parts of a WordPress post for one-click tweeting. Use:

<blockquote>Schardt says that <strong>&#91;tweetable&#93;</strong>finding creative journalists with an awareness of what technologies are available to them is half the battle.<strong>&#91;/tweetable&#93;</strong> The advancements themselves outpace the average newsroom's awareness and ability, but funding continues to be overwhelmingly aimed at furthering these platforms — while journalists struggle to keep up.</blockquote>

Optionally, you can include an <code>alt</code> tag in the shortcode if you want the text of the tweet to be different than the exact text you're highlighting:

<blockquote>Schardt says that <strong>&#91;tweetable alt=&#34;This is actually the text that will show up in the tweet.&#34;&#93;</strong>finding creative journalists with an awareness of what technologies are available to them is half the battle.<strong>&#91;/tweetable&#93;</strong> The advancements themselves outpace the average newsroom's awareness and ability, but funding continues to be overwhelmingly aimed at furthering these platforms — while journalists struggle to keep up.</blockquote>

You can also add hashtags to the tweet:

<blockquote>Schardt says that <strong>&#91;tweetable hashtag=&#34;#journalism #publicmedia&#34;&#93;</strong>finding creative journalists with an awareness of what technologies are available to them is half the battle.<strong>&#91;/tweetable&#93;</strong> The advancements themselves outpace the average newsroom's awareness and ability, but funding continues to be overwhelmingly aimed at furthering these platforms — while journalists struggle to keep up.</blockquote>

Or add an @username to use as the "via" source of the tweet:

<blockquote>Schardt says that <strong>&#91;tweetable via=&#34;aschweig&#34;&#93;</strong>finding creative journalists with an awareness of what technologies are available to them is half the battle.<strong>&#91;/tweetable&#93;</strong> The advancements themselves outpace the average newsroom's awareness and ability, but funding continues to be overwhelmingly aimed at furthering these platforms — while journalists struggle to keep up.</blockquote>

## Customize ##

### Settings page ###
![alt tag](https://raw.github.com/yurivictor/tweetable-text/master/img/settings.png)

You can customize the colors, the link text, the background of the link and the hover state to match your web sites.

You can also add your Twitter handle so tweets show up via you.

### Templates ###
Parent and child themes can include templates for the outputted HTML, inline CSS, or settings page by including <code>tweet.php</code>, <code>css.php</code>, or <code>options.php</code> inside a <code>tweetable/</code> directory. See the defaults in this plugin's <code>templates/</code> directory for available variables.

The default outputted HTML includes a Twitter bird from <a href="http://fortawesome.github.io/Font-Awesome/">Font Awesome</a>. If you want to use your own version of Font Awesome, the <code>tweetable_font_awesome_src</code> filter is available. If you don't want to use Font Awesome, remove it with <code>wp_dequeue_style()</code> or <code>add_filter( 'tweetable_font_awesome_src', '__return_false' )</code>.

### Allowed Post Types ###
The shortcode works with Posts by default. You can whitelist more post types with the <code>tweetable_allowed_post_types</code> filter.

## Bugs ##
* Improve bit.ly shortening for links (check for valid API credentials on options page save)

## Improvements ##
* Tweetable button
* Tweetable settings

## Todo ##
* Submit pull request to jbenton
