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
        $str = $str . "<a class='il-punto-titolo' style='margin-bottom: 4px' href='" . get_post_permalink($post->ID) . "'>" . apply_filters('the_title', $post->post_title) . "</a>";
        $str = $str . "<time class='post-date wp-block-latest-posts__post-date il-punto-data'>" . get_the_time(get_option('date_format'), $post->ID) . "</time>";
        $str = $str . "<div class='post-content-custom'>" . wpautop(apply_filters('the_content', $post->post_content)) . "</div>";
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
remove_action('init', 'register_block_core_latest_posts');

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


    if (!array_key_exists("category__in", $args)) {
        $args["category__in"] = array_map(function ($category) {
            return $category->cat_ID;
        }, get_categories(array("child_of" => 10304)));
        # error_log(print_r("Le categorie sono inserite manualmente.", TRUE));
    } # else
    # (print_r("Le categorie erano già presenti.", TRUE));
    # error_log(print_r($args["category__in"], TRUE));

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
            /* $byline = sprintf(__('by %s'), $author_display_name); */

            /* if (!empty($author_display_name)) {
                $list_items_markup .= sprintf(
                    '<div class="wp-block-latest-posts__post-author">%1$s</div>',
                    esc_html($byline)
                );
            } */

            if (function_exists('coauthors_posts_links')) {
                $a = coauthors_posts_links(null, " e ", null, null, false);
            } else {
                $a = the_author_posts_link();
            }

            $list_items_markup .= sprintf(
                '<div class="wp-block-latest-posts__post-author">%1$s</div>',
                $a,
            );
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

        $list_items_markup .=
            sprintf(
                '<a class="blog-continue-reading wp-block-button__link has-white-color has-text-color has-background" href="%1$s">Leggi tutto »</a>',
                $post_link
            );


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


/**
 * Register handler for auto-generated excerpt.
 *
 * @wp-hook get_the_excerpt
 * @param string $excerpt
 * @return  string
 */
function t5_excerpt_clean_up($excerpt)
{
    if (!empty ($excerpt))
        return $excerpt;

    add_filter('the_content', 't5_excerpt_content');

    return $excerpt;
}

/**
 * Strip parts from auto-generated excerpt.
 *
 * @wp-hook the_content
 * @param string $content
 * @return  string
 */
function t5_excerpt_content($content)
{
    // Remove immediately; maybe the next post doesn't
    // use an excerpt, but the full content.
    remove_filter(current_filter(), __FUNCTION__);

    // Fails with nested tables. Just don't do that. :)
    return preg_replace('~<div class="wp-block-group sustain-block has-background.*</div>~ms', '', $content);
}

add_filter('get_the_excerpt', 't5_excerpt_clean_up', 1);


# Crea lo shortcode per i vari redattori
function user_profile($atts)
{
    # error_log(print_r("\n\n" . $atts["slug"] . "\n\n", TRUE));

    #$result = (    ->get_guest_author_by('login', $atts["login"]);
    #$result = json_encode($result);
    $user = $GLOBALS['coauthors_plus']->get_coauthor_by('id', $atts["id"]);

    $image_regex = array();
    preg_match("<img .{0,220}\".{0,4}/>", $user->description, $image_regex);
    $description = wp_trim_words($user->description, 24);

    $image = '<' . $image_regex[0] . '>';
    if ($image_regex[0] == "") $image = "";

    return sprintf(
        '<div class="entry-author co-author editor">
            <div class="author-image">%3$s</div>
            <a href="%1$s">%2$s</a></span>
            <br>
            <p>%4$s</p>
        </div>',
        '/archives/author/' . $user->user_nicename,
        $user->display_name,
        $image,
        $description,
    );
}

add_shortcode('user_profile', 'user_profile');

function newsletter_form($atts)
{
    $result = <<<EOD
    <form name="subscribe" method="post" action="https://clientsection.contactlab.it/service/subscribe.php" class="newsletter-form">
      Email: <input type="text" name="e" value=""/>
      Sesso: <input type="text" name="extra[sex]" value=""/>
      CAP: <input type="text" name="extra[cap]" value=""/>
      Professione: <input type="text" name="extra[professione]" value=""/>
      Titolo di studio: <input type="text" name="extra[titolo_studio]" value=""/>
      Nazione: <input type="text" name="extra[nazione]" value=""/>
      <!-- PRIVACY CONTROL -->
      <p class="newsletter-form-buttons-wrapper">
          <p class="newsletter-form-buttons">
              <input id="p1" type="radio" name="__privacy_control" value="1" /> Accetto
              <input type="radio" checked="checked" name="__privacy_control" value="0" /> Non accetto
              <input type="hidden" name="g" value="1000009"/>
              <input type="hidden" name="wfc" value="110000091274"/>
              <input type="hidden" name="lang" value="it" />
              <!-- ONCLICK SUBMIT VALIDATE JS -->
              <input type="submit" name="do_subscribe"  value="Iscrivimi" onClick="return(validateWebForm());" />
          </p>
      </p>
    </form>
    <script language="JavaScript" type="text/javascript">
      function validateWebForm() {
    <!-- JS PRIVACY CHECK BEFORE SUBMIT -->
      if(document.getElementById('p1').checked == false) {
        alert("Se non accetti la privacy policy, non puoi proseguire con l'iscrizione.");
        return false;
      }else{
        return true;
      }}
    
    </script>
    EOD;

    return $result;
}

add_shortcode('newsletter_form', 'newsletter_form');


# Abilita la ricerca dei guest authors

/*
 * https://185.34.85.109/archives/author/massimo-taddei/
 * https://185.34.85.109/?post_type=guest-author&p=2148
 */

function adjust_permalink($permalink, $post)
{
    $post_type = get_post_type($post);
    if ($post_type === 'guest-author') {
        global $coauthors_plus;
        $author = $coauthors_plus->get_coauthor_by('ID', $post->ID);
        $permalink = get_author_posts_url($author->ID, $author->user_nicename);
    }
    return $permalink;
}

add_filter('post_type_link', 'adjust_permalink', 10, 2);


# Crea lo shortcode per la visualizzazione dei PDF
function gview($atts)
{
    $file_link = $atts["file"];

    return sprintf(
        '<iframe
            src="https://docs.google.com/viewer?url=%1$s&embedded=true"
            style="width: 600px;
            height: 600px;">
        </iframe>',
        $file_link,
    );
}

add_shortcode('gview', 'gview');

# Modifica la priorità della ricerca degli articoli
function order_the_results($hits)
{
    $priority_posts = array();
    $regular_posts = array();

    $args = array('parent' => 10304, 'fields' => 'ids');
    $important_categories = get_categories($args);

    error_log(json_encode($important_categories));

    foreach ($hits[0] as $hit) {
        $category_ids = wp_get_post_categories($hit->ID, array('fields' => 'ids'));

        $important_article = false;
        foreach ($category_ids as $category_id) {
            if (in_array($category_id, $important_categories)) {
                $important_article = true;
            }
        }

        if ($important_article) {
            $priority_posts[] = $hit;
        } else {
            $regular_posts[] = $hit;
        }
    }

    $hits[0] = array_merge($priority_posts, $regular_posts);
    return $hits;
}

# add_filter('relevanssi_hits_filter', 'order_the_results');

# Cambia la mail di WordPress
function wpb_sender_email($original_email_address)
{
    return 'desk@lavoce.info';
}

add_filter('wp_mail_from', 'wpb_sender_email');

# Cambia il nome del mittente delle mail
function wpb_sender_name($original_email_from)
{
    return 'Desk de Lavoce.info';
}

add_filter('wp_mail_from_name', 'wpb_sender_name');

