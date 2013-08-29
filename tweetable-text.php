<?php
/*
Plugin Name: Tweetable Text
Original Plugin URI: http://wordpress.org/extend/plugins/tweetable-text/
Description: Make your posts more shareable. Add a Tweet and Buffer button to key sentences right inside each blog post with a simple [tweetable] tag.
Version: 1.1
Author: Salim Virani (original), updated by Joshua Benton of Nieman Lab, forked by Yuri Victor
*/

if ( ! class_exists ( 'TweetableText' ) ):

class TweetableText {

	/** Constants *************************************************************/

	const version          = '0.0.1';
	const key              = 'tweetable';

	/** Load Methods **********************************************************/

	/**
	 * Load necessary functions
	 */
	public static function load() {
		self::add_actions();
		self::add_shortcodes();
		self::remove_filters();
	}

	/**
	 * Hook actions into WordPress API
	 * @uses add_action()
	 */
	private static function add_actions() {
		add_action(  'wp_head', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Enables shortcodes in WordPress posts
	 * @uses add_shortcode()
	 */
	private static function add_shortcodes() {
		add_shortcode( 'tweetable', array( __CLASS__, 'makeTweetable' ) );
	}

	/**
	 * Remove filters from WordPress API
	 * @uses remove_filter()
	 */
	private static function remove_filters() {
		// Stops WordPress from converting your quote symbols into smartquotes, since they are not compatible with the Twitter Share button. (The urlencoding of single quotes / apostrophes breaks in the tweet.)
		remove_filter( 'the_content', 'wptexturize' );
	}

	/**
	 * Enqueue the necessary CSS and JS
	 * @uses wp_enqueue_style()
	 */
	public static function enqueue_scripts() {
		// css
		wp_enqueue_style( self::key, plugins_url( 'css/tweetable.css', __FILE__ ), null, self::version );
	}

	/** Public Methods *****************************************************/

	/**
	 * Turns [tweetable] shortcode into link
	 *
	 * @param array $atts, [tweetable] shortcode attributes
	 * @param string $content, the content wrapped in [tweetable] shortcode
	 * @uses shortcode_atts()
	 * @return if not post
	 */
	public static function makeTweetable( $atts, $content = '' ) {

		global $post;

		// bail if not a post
		if ( ! get_post_type( $post ) == 'post' )
			return $content;

		// [tweetable] shortcode attributes
		// @param string alt, an alternate tweet
		// @param string hashtag, a hashtag to attach to the tweet
		extract( shortcode_atts( array(
			'alt'     	=> '',
			'hashtag' 	=> '',
			'via'		=> '',
		), $atts ) );
		$permalink = get_permalink( $post->ID );
		$tweetcontent = ucfirst( strip_tags( $content ) );

		//for Largo sites only we'll use the site's twitter handle if no manual override is provided
		if ( !$via && of_get_option('twitter_link') && function_exists('twitter_url_to_username') )
			$via = twitter_url_to_username( of_get_option('twitter_link') );

		if ( $alt ) $tweetcontent = $alt;
		if ( $hashtag ) $tweetcontent .= ' ' . $hashtag;

		ob_start();
			self::template( 'tweet', compact( 'content', 'tweetcontent', 'permalink', 'via' ) );
			$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Load a template. MVC FTW!
	 * @param string $template the template to load, without extension (assumes .php). File should be in templates/ folder
	 * @param args array of args to be run through extract and passed to template
	 */
	public static function template( $template, $args = array() ) {

	    extract( $args );

	    if ( ! $template )
	        return false;

	    $path = dirname( __FILE__ ) . "/templates/{$template}.php";
	    $path = apply_filters( 'liveblog', $path, $template );

	    include $path;

	}
}

TweetableText::load();

endif;