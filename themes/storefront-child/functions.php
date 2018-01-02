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
* Google map api key and script
*
*/
function my_acf_init() {
    acf_update_setting('google_api_key', 'AIzaSyDINrZWq2LjXRB7O_f8_HGE1B7IKIrht-E ');
}
add_action('acf/init', 'my_acf_init');

function add_child_theme_google_map() {
    wp_enqueue_script('google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDINrZWq2LjXRB7O_f8_HGE1B7IKIrht-E', [], '3', true );
}

add_action( 'wp_enqueue_scripts', 'add_child_theme_google_map' );

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
        <div class="swiper-container swipe-1">
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

/**
* Best selling products
*
*/
function best_selling_products() {
    $args = [
        'posts_per_page' => 3,
        'post_type' => 'product',
        'meta_key' => 'total_sales',
        'orderby' => 'meta_value_num'
    ];
    $products = new WP_Query($args); ?>

    <h2>Best sellers</h2>
    <div class="columns-3">
        <ul class="products bestsellers">
            <?php while ($products->have_posts()) : $products->the_post();
                woocommerce_get_template_part('content', 'product');
            endwhile; ?>
        </ul>
    </div>

    <?php wp_reset_postdata();
}

/**
* Products on sale
*
*/
function products_on_sale() {
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => '_sale_price',
                'value' => 0,
                'compare' => '>',
                'type' => 'numeric'
            ],
            [
                'key' => '_min_variation_sale_price',
                'value' => 0,
                'compare' => '>',
                'type' => 'numeric'
            ]
        ]
    );

    $products = new WP_Query( $args ); ?>
    <h2>On sale right now</h2>
    <div class="columns-3">
        <ul class="products">
            <div class="swiper-container swipe-2">
                <div class="swiper-wrapper">
                    <?php while ($products->have_posts()) : $products->the_post(); ?>
                         <div class="swiper-slide">
                            <?php woocommerce_get_template_part('content', 'product'); ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </ul>
    </div>
<?php }

/**
* Get recent posts
*
*/
function recent_posts() {
    $args = array(
        'post_type' => 'post',
    	'posts_per_page' => 3,
    	'orderby' => 'post_date',
    	'order' => 'DESC',
    	'post_status' => 'publish'
    );

    $posts = new WP_Query($args); ?>

    <h2>Latest from our blog</h2>
    <div class="columns-3">
        <ul class="blogposts">
            <?php while($posts->have_posts()) : $posts->the_post(); ?>
                <li class="blogpost">
                    <a href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail(); ?>
                        <h3><?php the_title(); ?></h1>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
<?php }


/**
* Related products on single post
*
*/
function related_products_post($category_name) {
    // Get the full term from category name to access ID
    $category = get_term_by('name', $category_name, 'product_cat');

    $args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'ignore_sticky_posts' => true,
        'posts_per_page' => '3',
        'orderby' => 'rand',
        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'terms' => $category->term_id,
                'operator' => 'IN'
            ],
            [
                'taxonomy' => 'product_visibility',
                'field' => 'slug',
                'terms' => 'exclude-from-catalog',
                'operator' => 'NOT IN'
            ]
        ]
    ];
    $products = new WP_Query($args); ?>

    <h2>Related to <?php the_title(); ?></h2>
    <div class="columns-3">
        <ul class="products bestsellers">
            <?php while ($products->have_posts()) : $products->the_post();
                woocommerce_get_template_part('content', 'product');
            endwhile; ?>
        </ul>
    </div>
<?php }
