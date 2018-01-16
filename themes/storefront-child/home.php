<?php
get_header();

    if (have_posts()) :
        while (have_posts()) : the_post(); ?>
            <div class="blogpost">
                <div class="image">
                    <?php the_post_thumbnail(); ?>
                </div>
                <div class="content">
                    <h2><?php the_title(); ?></h2>
                    <?php the_excerpt(); ?>
                    <a href="<?php the_permalink(); ?>" class="button">Read more</a>
                </div>
            </div>
        <?php endwhile;
    endif;

get_footer();
