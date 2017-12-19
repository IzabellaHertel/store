<?php

/**
* Crete stores with Custom Post Types
* add action init
*/
function add_stores() {
    register_post_type( 'stores',
        [
            'labels' => array(
                'name' => __( 'Stores' ),
                'singular_name' => __( 'Store' )
            ),
            'public' => true,
            'has_archive' => false,
            'rewrite' => ['slug' => 'stores'],
            'menu_icon' => 'dashicons-store',
            'capability_type' => 'page',
        ]
    );
}
add_action( 'init', 'add_stores' );
