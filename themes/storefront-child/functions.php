<?php

/**
* Load custom style and script
* add action wp enwueue scripts
*/
function add_child_theme_scripts() {
    wp_enqueue_style('swiper-style', get_stylesheet_directory_uri().'/assets/css/swiper.min.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri().'/assets/css/main.css');
    wp_enqueue_script('swiper-script', get_stylesheet_directory_uri().'/assets/js/swiper.min.js', ['jquery'], false, true);
    wp_enqueue_script('child-script', get_stylesheet_directory_uri().'/assets/js/script.js', ['jquery'], false, true);
}
add_action('wp_enqueue_scripts', 'add_child_theme_scripts');

/**
* Crete stores with Custom Post Types
* add action init
*/
function add_stores() {
    register_post_type('stores',
        [
            'labels' => array(
                'name' => __('Stores'),
                'singular_name' => __('Store')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'stores'],
            'menu_icon' => 'dashicons-store',
            'capability_type' => 'page',
        ]
    );
}
add_action('init', 'add_stores');

/**
* Display map
*
*/
function show_map() {
    $location = get_field('map');
    if(!empty($location)) { ?>
        <div class="acf-map">
            <div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>"></div>
        </div>
    <?php }
}

/**
* Get coupons
*
*/
function get_coupons() {
    $args = [
        'posts_per_page' => -1,
        'post_type' => 'shop_coupon',
        'post_status' => 'publish',
    ];
    $coupons = get_posts($args);

    if (count($coupons) > 0) { ?>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php foreach ($coupons as $coupon) { ?>
                    <div class="swiper-slide">
                        <div>
                            <p class="swiper-excerpt"><?php echo $coupon->post_excerpt; ?></p>
                            <p class="swiper-code">Use code: "<span><?php echo $coupon->post_title; ?></span>" in checkout</p>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    <?php }
}
