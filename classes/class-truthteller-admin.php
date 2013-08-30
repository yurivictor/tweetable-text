<?php
/**
 * Administrative functions
 * @package Truth_Teller
 */

class Truth_Teller_Admin {

    /**
     * Hook into WordPress API on init
     */
    public function __construct( $key, &$parent ) {

        $this->key    = $key;
        $this->parent = &$parent;

        $this->admin_actions();

    }

    /**
     * Hook actions in that run on admin page-load
     *
     * @uses add_action()
     */
    private function admin_actions() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'wp_insert_post_data', array( $this, 'save_transcripts_meta' ), 10, 2 );
    }


    /**
     * Add meta boxes to admin pages
     */
    function add_meta_boxes() {

        add_meta_box( $this->key, 'Truth_Teller', array( $this, 'transcripts_meta_box' ), 'transcript', 'side' );

    }


    /**
     * Save information from transcripts meta box
     */
    function save_transcripts_meta( $post_data, $post_array ) {

        if ( !isset( $post_array['ID'] ) )
            return $post_data;

        if ( defined( 'DOING_AUTOSAVE' ) )
            return $post_data;

        $post_data['post_parent'] = intval( $_POST['transcripts_entry_post'] );
    
        return $post_data;

    }

    /**
     * Render select video meta box
     */
    function transcripts_meta_box( $post ) {
    
        // Get all active posts
        $posts = array(); 
        $query = new WP_Query( array(
            'post_type' => 'post',
            'posts_per_page' => -1
        ) );

        while ( $query->have_posts() ):
        
            $query->next_post();
            $posts[$query->post->ID] = esc_attr( $query->post->post_title );

        endwhile;

        $this->parent->template( 'transcripts-meta-box', compact( 'posts' ) );

    }



}