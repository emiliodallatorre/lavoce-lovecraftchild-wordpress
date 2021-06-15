<?php
/*
Plugin Name: Inline Related Posts
Plugin URI: http://emiliodallatorre.it/intelly-related-posts/
Description: Inline Related Posts AUTOMATICALLY inserts related posts INSIDE your content, capturing immediately the reader's attention
Author: IntellyWP, Emilio Dalla Torre
Author URI: http://emiliodallatorre.it/
Email: info@emiliodallatorre.it
Version: 4.0.0
*/
define('IRP_PLUGIN_PREFIX', 'IRP_');
define('IRP_PLUGIN_FILE',__FILE__);
define('IRP_PLUGIN_SLUG', 'intelly-related-posts');
define('IRP_PLUGIN_NAME', 'Inline Related Posts');
define('IRP_PLUGIN_VERSION', '3.0.4');
define('IRP_PLUGIN_AUTHOR', 'IntellyWP');
define('IRP_PLUGIN_ROOT', dirname(__FILE__).'/');
define('IRP_PLUGIN_IMAGES', plugins_url( 'assets/images/', __FILE__ ));
define('IRP_PLUGIN_ASSETS', plugins_url( 'assets/', __FILE__ ));

define('IRP_LOGGER', FALSE);
define('IRP_DEBUG_BLOCK', FALSE);
define('IRP_DISABLE_RELATED', FALSE);
define('IRP_QUERY_POSTS_OF_TYPE', 1);
define('IRP_QUERY_POST_TYPES', 2);
define('IRP_QUERY_CATEGORIES', 3);
define('IRP_QUERY_TAGS', 4);

define('IRP_ENGINE_SEARCH_CATEGORIES_TAGS', 0);
define('IRP_ENGINE_SEARCH_CATEGORIES', 1);
define('IRP_ENGINE_SEARCH_TAGS', 2);

define('IRP_PLUGIN_URI', plugins_url('/', __FILE__ ));
define('IRP_INTELLYWP_SITE', 'http://www.intellywp.com/');
define('IRP_INTELLYWP_ENDPOINT', IRP_INTELLYWP_SITE.'wp-content/plugins/intellywp-manager/data.php');
define('IRP_PAGE_FAQ', IRP_INTELLYWP_SITE.IRP_PLUGIN_SLUG);
define('IRP_PAGE_WORDPRESS', 'https://wordpress.org/plugins/'.IRP_PLUGIN_SLUG.'/');
define('IRP_PAGE_PREMIUM', IRP_INTELLYWP_SITE.IRP_PLUGIN_SLUG);
define('IRP_PAGE_SETTINGS', admin_url().'options-general.php?page='.IRP_PLUGIN_SLUG);

define('IRP_TAB_SETTINGS', 'settings');
define('IRP_TAB_SETTINGS_URI', IRP_PAGE_SETTINGS.'&tab='.IRP_TAB_SETTINGS);
define('IRP_TAB_ABOUT', 'about');
define('IRP_TAB_ABOUT_URI', IRP_PAGE_SETTINGS.'&tab='.IRP_TAB_ABOUT);
define('IRP_TAB_DOCS', 'docs');
define('IRP_TAB_DOCS_URI', 'http://intellywp.com/docs/category/inline-related-posts/');
define('IRP_TAB_WHATS_NEW', 'whatsnew');
define('IRP_TAB_WHATS_NEW_URI', IRP_PAGE_SETTINGS.'&tab='.IRP_TAB_WHATS_NEW);

include_once(dirname(__FILE__).'/autoload.php');
irp_include_php(dirname(__FILE__).'/includes/');

global $irp;
$irp=new IRP_Singleton();
$irp->init();
