<?php
/*
Plugin Name: Truth teller
Description: Truth teller
Version: 1.0 
Author: Yuri Victor ( hi@yurivictor.com ), Joey Marburger
License: MIT (attached)
*/

 if ( ! class_exists( 'Truth_Teller' ) ):

 final class Truth_Teller {


    /** Constants *************************************************************/

    const name       = 'Truth_Teller';  // human-readable name of plugin
    const key        = 'truth-teller';  // plugin slug, generally base filename and url endpoint
    const key_       = 'truth_teller';  // slug with underscores (PHP/JS safe)
    const prefix     = 'truth_teller_'; // prefix to append to all options, API calls, etc. w/ trailing underscore
    const nonce_key  = 'truth_teller_nonce';

    const version    = '1.0';
    

    /** Variables *************************************************************/

    private static $post_id     = null;
    private static $entry_query = null;


    /** Load Methods **********************************************************/


    /**
     * Register with WordPress API on Construct
     *
     * @uses add_action() to hook methods into WordPress actions
     *
     */
    function __construct() {

        self::includes();
        self::add_actions();

        self::add_admin_actions();

    }

    /**
     * Include the necessary files
     */
    private static function includes() {

        require( dirname( __FILE__ ) . '/classes/class-truthteller-admin.php' );

    }

    /**
     * Hook actions in that run on every page-load
     *
     * @uses add_action()
     */
    private static function add_actions() {

        add_action( 'init', array( __CLASS__, 'init' ) );
        add_action( 'init', array( __CLASS__, 'register_cpt' ) );

        add_action( 'init', array( __CLASS__, 'truthteller_endpoints_add_endpoint' ) );
        add_action( 'template_redirect', array( __CLASS__, 'truthteller_endpoints_template_redirect' ) );

        
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );

    }


    /**
     * Hook actions in that run on every admin page-load
     *
     * @uses is_admin()
     * @return if not in admin area
     */
    private function add_admin_actions() {

        // Bail if not in admin area
        if ( ! is_admin() )
            return;

        $this->admin = new Truth_Teller_Admin( self::key, $this );

    }


    /**
     * Enqueue the necessary JS and CSS needed to function
     *
     * @uses wp_enqueue_style()
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     *
     * @return If not a video post
     */
    public function enqueue() {

        // Only add files if this contains a transcript
        global $post;
        if( ! is_singular() || false === strpos( $post->post_content, '[transcript' ) )
            return;

        // css
        wp_enqueue_style( self::key, plugins_url( '/css/truthteller.css', __FILE__ ), null, self::version );

        // js
        wp_enqueue_script( self::key, plugins_url( '/js/truthteller.js', __FILE__ ), null, self::version, false );
        wp_localize_script( self::key, 'truthteller_settings', 
            apply_filters( 'truthteller_settings', array( 
                'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                'url'        => get_bloginfo( 'wpurl' ),

                'key'        => self::key,
            ) ) 
        );

    }

    /** Public Methods ********************************************************/

    /**
     * Truth Teller initialization functions.
     *
     * This is where Truth Teller sets up any additional things it needs to run
     * inside of WordPress.
     */
    public static function init() {

        add_shortcode( 'transcript', array( __CLASS__, 'add_shortcode' ) );   

        register_activation_hook( __FILE__, 'truthteller_endpoints_activate' );     
        register_deactivation_hook( __FILE__, 'truthteller_endpoints_deactivate' );


    }


    /**
     * Register "transcripts" custom post type
     *
     * @uses register_post_type
     */
    public function register_cpt() {

        $labels = array(
            'name'          => __( 'Transcripts', self::key ),
            'singular_name' => __( 'Transcript', self::key ),
            'add_new'       => __( 'Add transcript', self::key ),
            'add_new_item'  => __( 'Add json', self::key ),
        );

        $args = array(
            'labels'      => $labels,
            'public'      => false,
            'show_ui'     => true,
            'supports'    => array( 'editor' ),
            'rewrite'     => false,
        );

        register_post_type( 'transcript', $args );

    }

    /** 
     * Uses transcript shortcode to add transcript to page
     *
     * @param atts array ID of transcript to pull into the page
     */
    public function add_shortcode( $atts ) {


    }

    public function truthteller_endpoints_add_endpoint() {

        add_rewrite_endpoint( 'transcript', EP_PERMALINK | EP_PAGES );

    }

    public function truthteller_endpoints_template_redirect() {
        
        global $wp_query;
 
        // if this is not a request for json or it's not a singular object then bail
        if ( ! isset( $wp_query->query_vars['transcript'] ) || ! is_singular() )
                return;

        self::truthteller_endpoints_do_json();

        exit;

    }

    public function truthteller_endpoints_do_json() {

        header( 'Content-Type: application/json' );
 
        global $post;
        
        $pattern = get_shortcode_regex();

        preg_match ( '/'. $pattern . '/s', $post->post_content, $matches );

        if ( $matches[2] == 'transcript' ) {

            $atts = str_replace ( " ", "&", trim ( $matches[3] ) );
            $atts = str_replace ( '"', '', $atts);


            $atts = wp_parse_args( $atts );


            $post = get_post( $atts["id"] );

            $json = $post->post_content;
            
            echo $json;

        } else {

            $query = new WP_Query( array(
                'post_parent' => get_the_id(),
                'post_type' => 'transcript'
            ) );

            foreach ( $query->posts as $post ) {
                echo $post->post_content;
            }

        }

    }
    
    public function truthteller_endpoints_activate() {

        // ensure our endpoint is added before flushing rewrite rules
        truthteller_endpoints_add_endpoint();
        // flush rewrite rules - only do this on activation as anything more frequent is bad!
        flush_rewrite_rules();

    }
    
     
    public function truthteller_endpoints_deactivate() {
            // flush rules on deactivate as well so they're not left hanging around uselessly
            flush_rewrite_rules();
    }

    /** 
     * Load a template. MVC FTW!
     * @param string $template the template to load, without extension (assumes .php). File should be in templates/ folder
     * @param args array of args to be run through extract and passed to template
     */
    public function template( $template, $args = array() ) {

        extract( $args );

        if ( ! $template )
            return false;
            
        $path = dirname( __FILE__ ) . "/templates/{$template}.php";
        $path = apply_filters( 'truthteller', $path, $template );

        include $path;
        
    }
    

 }

 $truth_teller = new Truth_Teller();

 endif;
