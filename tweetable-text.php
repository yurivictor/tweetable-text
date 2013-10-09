<?php
/*
Plugin Name: Tweetable Text
Original Plugin URI: http://wordpress.org/extend/plugins/tweetable-text/
Description: Make your posts more shareable. Add a Tweet and Buffer button to key sentences right inside each blog post with a simple [tweetable] tag.
Version: 1.1
Author: Salim Virani (original), updated by Joshua Benton of Nieman Lab, Yuri Victor, Adam Schweigert
*/

define( 'TWEETABLETEXT_FILE', __FILE__ );

if ( ! class_exists ( 'TweetableText' ) ):

class TweetableText {

	/** Constants *************************************************************/

	const version    = '0.0.3';
	const key        = 'tweetable';
	const nonce_key  = 'post_formats_ui_nonce';

	/** Variables *************************************************************/

	protected $data = array(
		'color_bg'    => '#F5F5F5',
		'color_text'  => '#222',
		'color_hover' => '#ed2e24',
		'username'    => '',
		'bitly_user'  => '',
		'bitly_key'   => '',
	);

	/** Load Methods **********************************************************/

	/**
	 * Register with WordPress API on Construct
	 */
	function __construct() {

		//don't let this fire twice
		if ( get_class( self ) == 'TweetableText' )
			return;

		register_activation_hook( TWEETABLETEXT_FILE, array( __CLASS__, 'activate' ) );
	}

	/**
	 * Adds tables to WordPress
	 * @uses update_option()
	 */
	public function activate() {
		update_option( self::key, self::data);
	}

	/**
	 * Removes tables from WordPress
	 * @uses update_option()
	 */
	public function deactivate() {
		update_option( self::key, self::data );
	}

	/**
	 * Load necessary functions
	 */
	public static function load() {
		self::add_actions();
		self::add_filters();
		self::add_shortcodes();
	}

	/**
	 * Hook actions into WordPress API
	 * @uses add_action()
	 */
	private static function add_actions() {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_pages') );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
	}
	/**
	 * Hook filters into WordPress API
	 * @uses add_filter()
	 */
	private static function add_filters() {
		add_filter( 'wp_head', array( __CLASS__, 'create_css' ) );
		add_filter( 'no_texturize_shortcodes', array( __CLASS__, 'exempt_from_wptexturize' ) );
	}

	/**
	 * Enables shortcodes in WordPress posts
	 * @uses add_shortcode()
	 */
	private static function add_shortcodes() {
		add_shortcode( 'tweetable', array( __CLASS__, 'makeTweetable' ) );
	}

	/**
	 * Hook into WordPress settings API
	 * @uses register_setting()
	 */
	public static function register_settings() {
    	register_setting( 'tweetable_options', self::key, array( __CLASS__, 'settings' ) );
	}

	/**
	 * Enqueue the necessary CSS and JS
	 * @uses wp_enqueue_style()
	 */
	public static function enqueue_scripts() {
		// css
		$font_awesome_src = plugins_url( 'css/lib/font-awesome/css/font-awesome.min.css', __FILE__ );
		$font_awesome_src = apply_filters( 'tweetable_font_awesome_src', $font_awesome_src );
		wp_enqueue_style( 'tweetable-font-awesome', $font_awesome_src, null, '3.2.1' );
	}

	/**
	 * Enqueue the necessary CSS and JS
	 * @uses wp_enqueue_style()
	 * @uses wp_enqueue_script()
	 */
	public static function admin_enqueue_scripts( $hook_suffix ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( self::key . '-admin', plugins_url( 'js/tweetable-admin.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}

	/**
	 * Adds Tweetable to settings
	 * @uses add_option_page()
	 */
	public function add_pages() {
	    add_options_page( 'Tweetable', 'Tweetable', 'manage_options', 'tweetable_options', array( __CLASS__, 'options_page' ) );
	}

	/** Public Methods *****************************************************/

	/**
	 * Turns [tweetable] shortcode into link
	 *
	 * @param array $atts, [tweetable] shortcode attributes
	 * @param string $content, the content wrapped in [tweetable] shortcode
	 * @uses shortcode_atts()
	 * @return if not an allowed post type
	 */
	public static function makeTweetable( $atts, $content = '' ) {

		global $post;

		// bail if not in the allowed post types
		$allowed_post_types = array( 'post' );
		$allowed_post_types = apply_filters( 'tweetable_allowed_post_types', $allowed_post_types );
		if ( ! in_array( get_post_type( $post ), $allowed_post_types ) )
			return $content;

		// [tweetable] shortcode attributes
		// @param string alt, an alternate tweet
		// @param string hashtag, a hashtag to attach to the tweet
		// @param string via, a twitter username to use as the via attribute (no @ sign)
		extract( shortcode_atts( array(
			'alt'     	=> '',
			'hashtag' 	=> '',
			'via'		=> '',
		), $atts ) );
		$options      = get_option( 'tweetable' );
		$permalink    = get_permalink( $post->ID );
		$tweetcontent = ucfirst( strip_tags( $content ) );


		if ( !$via && $options['username'] )
			$via = $options['username'];

		if ( $alt )     $tweetcontent  = $alt;

		if ( $hashtag ) $tweetcontent .= ' ' . $hashtag;

		if ( $options['bitly_user'] && $options['bitly_key'] )
			$permalink = self::get_bitly_short_url( $permalink, $options['bitly_user'], $options['bitly_key'] );

		$href = sprintf( 'https://twitter.com/intent/tweet?original_referer=%1$s&source=tweetbutton&text=%2$s&url=%1$s%3$s',
			$permalink,
			rawurlencode( $tweetcontent ),
			$via ? '&via=' . $via : ''
		);

		ob_start();
			self::template( 'tweet', compact( 'content', 'href' ) );
			$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 * Create html for options page
	 * @uses get_option
	 * @return html of options page
	 */
	public function options_page() {
    	$key     = self::key;
    	$options = get_option( $key );

		return self::template( 'options', compact( 'key', 'options' ) );
	}

	/**
	 * Create CSS for tweetable
	 * @uses get_option
	 * @return css
	 */
	public static function create_css() {
		$options      = get_option( 'tweetable' );

		$color_bg     = $options['color_bg'];
		$color_text   = $options['color_text'];
		$color_hover  = $options['color_hover'];

		return self::template( 'css', compact( 'color_bg', 'color_text', 'color_hover' ) );
	}

	/**
	 * Stop WordPress from converting Tweetable quotes into smartquotes
	 *
	 * The smartquotes are incompatible with the Twitter Share button.
	 * The urlencoding of single quotes / apostrophes breaks in the
	 * tweet.
	 *
	 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/no_texturize_shortcodes
	 * @param array $shortcodes The existing exempted shortcodes
	 * @return array The updated exempted shortcodes
	 */
	public static function exempt_from_wptexturize( $shortcodes ) {
	  $shortcodes[] = 'tweetable';
	  return $shortcodes;
	}

	/**
	 * Sanitize user settings submission
	 * @return array $valid, the sanitized input
	 */
	public static function settings( $input ) {
		$valid = array();
		$valid['color_bg']     = sanitize_text_field( $input['color_bg'] );
		$valid['color_text']   = sanitize_text_field( $input['color_text'] );
		$valid['color_hover']  = sanitize_text_field( $input['color_hover'] );
		$valid['username']     = sanitize_text_field( $input['username'] );

		// @todo check to make sure the bitly username and api key are valid
		$valid['bitly_user']   = sanitize_text_field( $input['bitly_user'] );
		$valid['bitly_key']    = sanitize_text_field( $input['bitly_key'] );

		return $valid;
	}

	/**
	 * Load a template. MVC FTW!
	 *
	 * Templates in a parent or child theme should be in the tweetable/
	 * folder; otherwise, templates should be in templates/ folder
	 *
	 * @param string $template the template to load, without extension (assumes .php).
	 * @param args array of args to be run through extract and passed to template
	 */
	public static function template( $template, $args = array() ) {

		extract( $args );

		if ( ! $template )
			return false;

		$path = locate_template( "tweetable/{$template}.php", false, false );
		if ( ! $path )
			$path = dirname( __FILE__ ) . "/templates/{$template}.php";
		$path = apply_filters( 'liveblog', $path, $template );

		include( $path );
	}

	/**
	 * Get a short bit.ly URL for a given long URL
	 * @param string $url the long URL
	 * @param string $user bit.ly username
	 * @param string $key bit.ly API key
	 * @param string $format format of the API response to return
	 */
	public static function get_bitly_short_url( $url, $user, $key, $format='txt' ) {
		$connectURL = 'http://api.bit.ly/v3/shorten?login=' . $user . '&apiKey=' . $key . '&uri=' . urlencode( $url ) . '&format=' . $format;
		return self::urlopen( $connectURL );
	}

	/**
	 * Helper function to read the contents of the bit.ly API response
	 * @param string $url, the bitly API url
	 * @return string the shortened url
	 */
	public static function urlopen( $url ) {
		if ( function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			$result = curl_exec( $ch );
			curl_close( $ch );
			return $result;
		} else {
			return file_get_contents( $url );
		}
	}
}

TweetableText::load();

endif;