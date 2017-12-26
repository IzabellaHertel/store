<?php

get_header();

if (have_posts()) :
    while (have_posts()) : the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <small>By <?php the_author(); ?> on <?php the_date(); ?></small>
        <?php if (has_post_thumbnail()) {
            the_post_thumbnail();
        }
        the_content();

    endwhile;
endif;

if (get_field('product_cat_name')) {
    related_products_post(get_field('product_cat_name'));
}

get_footer();
