<?php get_header();

    if (have_posts()) :
        while (have_posts()) : the_post(); ?>
            <div class="store">
                <div class="content">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php the_content(); ?>
                </div>
                <div class="hidden-mobile">
                    <?php show_map(); ?>
                </div>
            </div>
        <?php endwhile;
    endif;

get_footer();
