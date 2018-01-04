<?php
/**
* Template name: Contact
*/

get_header(); ?>
    <div class="col-full">
        <?php if (have_posts()) {
            while (have_posts()) { the_post(); ?>
                <div class="contact">
                    <div class="content">
                        <h1><?php the_title(); ?></h1>
                        <?php the_content(); ?>
                    </div>
                    <h2>Our headquarters</h2>
                    <?php show_map(); ?>
                </div>
            <?php }
        } ?>
    </div>
<?php get_footer();
