<?php get_header();

    if (have_posts()) {
        while (have_posts()) { the_post(); ?>
            <h2><?= get_the_title(); ?></h2>
            <?php the_content();
            show_map();
        }
    }

get_footer();
