<?php

/*
 * Boot WP functions.php
 * By Antoine Cadoret
 * Last update: 20/11/2014
 */

// Add a page with ALL Wordpress options
function add_all_general_settings_link(){ add_options_page(__('Tous les paramètres'), __('Tous les paramètres'), 'administrator', 'options.php'); }
add_action('admin_menu', 'add_all_general_settings_link');

// Enable Thumbnails support
add_theme_support('post-thumbnails');

// Add image sizes
add_image_size('list', 100, 100, true);

// Hide Wordpress mention in Meta
remove_action('wp_head', 'wp_generator');

// Add default posts and comments RSS feed links to head
add_theme_support('automatic-feed-links');

// Enable Links
add_filter('pre_option_link_manager_enabled', '__return_true');

// Hide Wordpress mention in Meta
remove_action('wp_head', 'wp_generator');

// Add CPT in archive
function cpt_in_archive($request){
    if (isset($request['tag']) && !isset($request['post_type']))
    $request['post_type'] = 'any';
    return $request;
}
add_filter('request', 'cpt_in_archive');

// Add CPT in feed
function cpt_in_feed($request) {
    if (isset($request['feed']))
        $request['post_type'] = get_post_types();
    return $request;
}
add_filter('request', 'cpt_in_feed');

// Custom back-office styles
function custom_back_office(){
    $admin_handle = 'admin_css';
    $admin_stylesheet = get_template_directory_uri() . '/back/back.css';
    wp_enqueue_style($admin_handle, $admin_stylesheet);
}
add_action('admin_print_styles', 'custom_back_office', 11);

// Custom login page
function custom_login_page(){   echo '<link rel="stylesheet" type="text/css" href="' .get_template_directory_uri() . '/back/login.css" />'; }
add_action('login_head', 'custom_login_page', 11);

// Change login page's logo link
function change_loginpage_link(){ return get_bloginfo('url'); }
add_filter('login_headerurl','change_loginpage_link');

// Change login page's logo title
function change_title_on_logo(){ return get_bloginfo('name'); }
add_filter('login_headertitle', 'change_title_on_logo');

// Obscure login error messages
function login_obscure(){ return '<strong>Aïe</strong> : il y a un problème avec le login'; }
add_filter('login_errors', 'login_obscure');

// Add favicon (upload file in in theme folder & main directory of the site)
function add_favicon(){ echo '<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" />'; }
add_action('wp_head', 'add_favicon');

// Register widgets
require(get_template_directory() . '/inc/widgets.php');

// Navigations
register_nav_menus(array(
    'primary'   => 'Menu principal',
    'footer'    => 'Liens du footer',
));

// Widgets Area - Sidebars
function widgets_area_init() {
    register_sidebar(array(
        'name'          => 'Home',
        'id'            => 'home-content',
        'description'   => 'Contenu de la page d\'accueil',
        'before_widget' => '<div id="%1$s" class="home-content %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>'
    ));
    register_sidebar(array(
        'name'          => 'Sidebar',
        'id'            => 'global-sidebar',
        'description'   => 'Widgets de la sidebar par défaut',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>'
    ));
}
add_action('widgets_init', 'widgets_area_init');

// Cut a string to the word
function wordCut($string, $nbChar){
    return (strlen($string) > $nbChar ? substr(substr($string, 0, $nbChar),0,
    strrpos(substr($string,0,$nbChar),' ')).' (...)' : $string);
}

// Cut a string to the letter
function strictCut($string, $nbChar = 20, $moreStr = '...'){
    $safeStr = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
    if(strlen($safeStr) > $nbChar){$new_string = substr($safeStr, 0, $nbChar) . $moreStr;
    }else{$new_string = $string;}
    return $new_string;
}

// Pagination
if(!function_exists('theme_pagination')){
    function theme_pagination(){
        global $wp_query, $wp_rewrite;
        $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

        $pagination = array(
            'base'      => @add_query_arg('page','%#%'),
            'format'    => '',
            'total'     => $wp_query->max_num_pages,
            'current'   => $current,
            'show_all'  => false,
            'end_size'  => 1,
            'mid_size'  => 2,
            'type'      => 'list',
            'next_text' => '&gt;&gt;',
            'prev_text' => '&lt;&lt;'
        );

        if($wp_rewrite->using_permalinks())
            $pagination['base'] = user_trailingslashit(trailingslashit(remove_query_arg('s', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged');

        if(!empty($wp_query->query_vars['s']))
            $pagination['add_args'] = array('s' => str_replace(' ' , '+', get_query_var('s')));

        echo str_replace('page/1/','', paginate_links($pagination));
    }
}

// Call first uploaded image
function main_image(){
    $files = get_children('post_parent=' .get_the_ID() . '&post_type=attachment&post_mime_type=image&order=desc');
    if($files) :
        $keys = array_reverse(array_keys($files));
        $j = 0;
        $num = $keys[$j];
        $image = wp_get_attachment_image($num, 'large', true);
        $imagepieces = explode('"', $image);
        $imagepath = $imagepieces[1];
        $main = wp_get_attachment_url($num);
        $template = get_template_directory();
        $the_title = get_the_title();
        print '<img src="' . $main . '" alt="' . $the_title . '" class="attachment-post-thumbnail wp-post-image" />';
    endif;
}

// Add shortcode
function short_intro($atts, $content = '') {
    return '<div class="intro">' . $content . '</div>';
}
add_shortcode('intro', 'short_intro'); /* [intro]content[/intro] */



/* --- USER --- */

// Disabled theme Editor
define('DISALLOW_FILE_EDIT', true);

// Remove color scheme (profile page)
remove_action('admin_color_scheme_picker', 'admin_color_scheme_picker');

// Remove some fields (profile page)
function hide_profile_fields($contactmethods){
    unset($contactmethods['aim']);
    unset($contactmethods['jabber']);
    unset($contactmethods['yim']);
    return $contactmethods;
}
add_filter('user_contactmethods','hide_profile_fields', 10, 1);

// Add customs fields (profile page)
function extra_user_profile_fields( $user ) { ?>
<h3><?php _e('Informations complémentaires', 'Page profile'); ?></h3>
 <table class="form-table">
    <tr>
        <th><label for="photoauteur"><?php _e("Photo de l'auteur"); ?></label></th>
        <td>
            <input type="text" name="photoauteur" id="photoauteur" value="<?php echo esc_attr( get_the_author_meta('photoauteur', $user->ID ) ); ?>" class="regular-text" /><br />
        </td>
    </tr>
    <tr>
        <th><label for="linkedin"><?php _e("Profil LinkedIn de l'auteur"); ?></label></th>
        <td>
            <input type="text" name="linkedin" id="linkedin" value="<?php echo esc_attr( get_the_author_meta('linkedin', $user->ID ) ); ?>" class="regular-text" /><br />
        </td>
    </tr>
</table>
<?php }
add_action('show_user_profile', 'extra_user_profile_fields');
add_action('edit_user_profile', 'extra_user_profile_fields');

function save_extra_user_profile_fields( $user_id ) {
    if ( !current_user_can('edit_user', $user_id ) ) { return false; }
    update_usermeta( $user_id, 'photoauteur', $_POST['photoauteur'] );
    update_usermeta( $user_id, 'linkedin', $_POST['linkedin'] );
}
add_action('personal_options_update', 'save_extra_user_profile_fields');
add_action('edit_user_profile_update', 'save_extra_user_profile_fields');



/* --- CHILD THEME OPTIONS --- */

// Enqueue child theme styles
function child_css(){
    wp_register_style('styles', get_stylesheet_directory_uri() . '/style.css', 'css', '');
    wp_enqueue_style('styles');
}
add_action('wp_enqueue_scripts', 'child_css');
