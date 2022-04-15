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

    foreach ($posts as $post) :
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

/**
 * Register handler for auto-generated excerpt.
 *
 * @wp-hook get_the_excerpt
 * @param string $excerpt
 * @return  string
 */
function t5_excerpt_clean_up($excerpt)
{
    if (!empty($excerpt))
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
    <form action="https://clientsection.contactlab.it/service/subscribe.php" method="post" name="subscribe" class="newsletter-shrinked-form">
        <input name="e" type="text" value="" placeholder="Email" />
        <br><br>
    <!-- PRIVACY CONTROL -->Consenso al trattamento dei dati personali:
    <br>
    <input id="p1" name="__privacy_control" type="radio" value="1" /> Accetto
    <input checked="checked" name="__privacy_control" type="radio" value="0" /> Non accetto
      <!-- CAPTCHA CONTROL -->
      <script src="https://www.google.com/recaptcha/api.js" async defer></script>
      <div class="g-recaptcha" data-sitekey="6Lf_qCkTAAAAADIrgt1DfnNCsfrtIq8gSBX3Whmi"></div>
      <input type="hidden" name="g" value="1000009"/>
      <input type="hidden" name="wfc" value="110000091274"/>
      <input type="hidden" name="lang" value="it" />
        Vuoi darci alcune informazioni aggiuntive su di te, per aiutarci a conoscerti meglio? Compila il form completo disponibile <a href="/newsletter/">qui</a>.
        <br><br>
    <!-- ONCLICK SUBMIT VALIDATE JS -->
    <input name="do_subscribe" type="submit" value="Iscrivimi" /></form>
    <script language="JavaScript" type="text/javascript">
      function validateWebForm() {
        <!-- JS PRIVACY CHECK BEFORE SUBMIT -->
        if(document.getElementById('p1').checked == false) {
          alert("Se non accetti la privacy non puoi proseguire con l\'iscrizione");
          return false;
        }
        <!-- JS CAPTCHA CHECK BEFORE SUBMIT -->
        if(grecaptcha.getResponse().length == 0) {
          alert('Per poter proseguire valida il CAPTCHA');
          grecaptcha.reset();
          return false;
        }else{
          return true;
        }
    }
    
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

add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode');
