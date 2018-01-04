<?php get_header();

    if (have_posts()) {
        while (have_posts()) { the_post(); ?>
            <div class="store">
                <div class="content">
                    <h2><?php the_title(); ?></h2>
                    <?php the_content(); ?>
                </div>
                <?php show_map(); ?>
            </div>
        <?php }
    }

get_footer();
