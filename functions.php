<?php
/* enqueue script for parent theme stylesheeet */
function childtheme_parent_styles()
{
    // enqueue style
    wp_enqueue_style('parent', get_template_directory_uri() . '/style.css');
}

add_action('wp_enqueue_scripts', 'childtheme_parent_styles');

# Modifiche personali

# Dà il supporto agli autori multipli
function lovecraft_post_meta()
{
    ?>
    <div class="post-meta">

        <p class="post-author"><span><?php _e('By', 'lovecraft'); ?> </span>
            <?php
            if (function_exists('coauthors_posts_links')) {
                coauthors_posts_links(null, " e ", null, null, true);
            } else {
                the_author_posts_link();
            } ?>
        </p>
        <p class="post-date"><span><?php _e('On', 'lovecraft'); ?> </span><a
                    href="<?php the_permalink(); ?>"><?php the_time(get_option('date_format')); ?></a></p>

        <?php if (has_category()) : ?>
            <p class="post-categories"><span><?php _e('In', 'lovecraft'); ?> </span><?php the_category(', '); ?></p>
        <?php endif; ?>

        <?php edit_post_link(__('Edit', 'lovecraft'), '<p>', '</p>'); ?>

    </div><!-- .post-meta -->

    <?php
}

# Crea lo shortcode per l'ultimo articolo della categoria "il punto"
function latest_punto()
{
    $args = array(
        'posts_per_page' => 1, // we need only the latest post, so get that post only
        'category_name' => 'il-punto' // Use the category id, can also replace with category_name which uses category slug
    );

    $str = "";
    $posts = get_posts($args);

    foreach ($posts as $post):
        $str = $str . "<div class='il-punto-block'>";
        $str = $str . "<h3 style='margin-bottom: 4px'><a href='" . get_post_permalink($post->ID) . "'>" . apply_filters('the_title', $post->post_title) . "</a></h3>";
        $str = $str . "<p class='post-date wp-block-latest-posts__post-date'>" . get_the_time(get_option('date_format'), $post->ID) . "</p>";
        $str = $str . "<p class='post-content-custom'>" . apply_filters('the_content', $post->post_content) . "</p>";
        $str = $str . "</div>";
    endforeach;

    return $str;
}

add_shortcode('latest_punto', 'latest_punto');


# Box degli autori tramite shortcode
function user_box($atts)
{
    $user_info = get_userdata($atts['userid']);
    return $user_info->user_email;
}

add_shortcode('user_box', 'user_box');


# Aggiunge gli utenti multipli sotto i post recenti
remove_action('init', 'register_block_core_latest_posts', 10);

function updated_render_block_core_latest_posts($attributes)
{
    global $post, $block_core_latest_posts_excerpt_length;

    $args = array(
        'posts_per_page' => $attributes['postsToShow'],
        'post_status' => 'publish',
        'order' => $attributes['order'],
        'orderby' => $attributes['orderBy'],
        'suppress_filters' => false,
    );

    $block_core_latest_posts_excerpt_length = $attributes['excerptLength'];
    add_filter('excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20);

    if (isset($attributes['categories'])) {
        $args['category__in'] = array_column($attributes['categories'], 'id');
    }
    if (isset($attributes['selectedAuthor'])) {
        $args['author'] = $attributes['selectedAuthor'];
    }

    $recent_posts = get_posts($args);

    $list_items_markup = '';

    foreach ($recent_posts as $post) {
        $post_link = esc_url(get_permalink($post));

        $list_items_markup .= '<li>';

        if ($attributes['displayFeaturedImage'] && has_post_thumbnail($post)) {
            $image_style = '';
            if (isset($attributes['featuredImageSizeWidth'])) {
                $image_style .= sprintf('max-width:%spx;', $attributes['featuredImageSizeWidth']);
            }
            if (isset($attributes['featuredImageSizeHeight'])) {
                $image_style .= sprintf('max-height:%spx;', $attributes['featuredImageSizeHeight']);
            }

            $image_classes = 'wp-block-latest-posts__featured-image';
            if (isset($attributes['featuredImageAlign'])) {
                $image_classes .= ' align' . $attributes['featuredImageAlign'];
            }

            $featured_image = get_the_post_thumbnail(
                $post,
                $attributes['featuredImageSizeSlug'],
                array(
                    'style' => $image_style,
                )
            );
            if ($attributes['addLinkToFeaturedImage']) {
                $featured_image = sprintf(
                    '<a href="%1$s">%2$s</a>',
                    $post_link,
                    $featured_image
                );
            }
            $list_items_markup .= sprintf(
                '<div class="%1$s">%2$s</div>',
                $image_classes,
                $featured_image
            );
        }

        # Mostra le categorie, finalmente
        if (has_category('', $post->ID)) {
            $category_array = wp_get_post_categories($post->ID);
            $category_list = array();
            foreach ($category_array as $categories) {
                $category_list[] = get_cat_name($categories);
            }
            $lister = implode(', ', $category_list);

            $list_items_markup .= "<div class='post-tags latest-posts-block'><a rel='tag'>" . $lister . "</a></div>";
        }

        $title = get_the_title($post);
        if (!$title) {
            $title = __('(no title)');
        }
        $list_items_markup .= sprintf(
            '<a href="%1$s">%2$s</a>',
            $post_link,
            $title
        );

        if (isset($attributes['displayAuthor']) && $attributes['displayAuthor']) {
            $author_display_name = coauthors(null, " e ", null, null, false);

            /* translators: byline. %s: current author. */
            $byline = sprintf(__('by %s'), $author_display_name);

            if (!empty($author_display_name)) {
                $list_items_markup .= sprintf(
                    '<div class="wp-block-latest-posts__post-author">%1$s</div>',
                    esc_html($byline)
                );
            }
        }

        if (isset($attributes['displayPostDate']) && $attributes['displayPostDate']) {
            $list_items_markup .= sprintf(
                '<time datetime="%1$s" class="wp-block-latest-posts__post-date">%2$s</time>',
                esc_attr(get_the_date('c', $post)),
                esc_html(get_the_date('', $post))
            );
        }

        if (isset($attributes['displayPostContent']) && $attributes['displayPostContent']
            && isset($attributes['displayPostContentRadio']) && 'excerpt' === $attributes['displayPostContentRadio']) {

            $trimmed_excerpt = get_the_excerpt($post);

            if (post_password_required($post)) {
                $trimmed_excerpt = __('This content is password protected.');
            }

            $list_items_markup .= sprintf(
                '<div class="wp-block-latest-posts__post-excerpt">%1$s</div>',
                $trimmed_excerpt
            );
        }

        if (isset($attributes['displayPostContent']) && $attributes['displayPostContent']
            && isset($attributes['displayPostContentRadio']) && 'full_post' === $attributes['displayPostContentRadio']) {

            $post_content = wp_kses_post(html_entity_decode($post->post_content, ENT_QUOTES, get_option('blog_charset')));

            if (post_password_required($post)) {
                $post_content = __('This content is password protected.');
            }

            $list_items_markup .= sprintf(
                '<div class="wp-block-latest-posts__post-full-content">%1$s</div>',
                $post_content
            );
        }

        $list_items_markup .= "</li>\n";
    }

    remove_filter('excerpt_length', 'block_core_latest_posts_get_excerpt_length', 20);

    $class = 'wp-block-latest-posts__list';

    if (isset($attributes['postLayout']) && 'grid' === $attributes['postLayout']) {
        $class .= ' is-grid';
    }

    if (isset($attributes['columns']) && 'grid' === $attributes['postLayout']) {
        $class .= ' columns-' . $attributes['columns'];
    }

    if (isset($attributes['displayPostDate']) && $attributes['displayPostDate']) {
        $class .= ' has-dates';
    }

    if (isset($attributes['displayAuthor']) && $attributes['displayAuthor']) {
        $class .= ' has-author';
    }

    $wrapper_attributes = get_block_wrapper_attributes(array('class' => $class));

    return sprintf(
        '<ul %1$s>%2$s</ul>',
        $wrapper_attributes,
        $list_items_markup
    );
}

function register_updated_render_block_core_latest_posts()
{
    register_block_type_from_metadata(
        __DIR__ . '/latest-posts',
        array(
            'render_callback' => 'updated_render_block_core_latest_posts',
        ),
    );
}

add_action('init', 'register_updated_render_block_core_latest_posts');

