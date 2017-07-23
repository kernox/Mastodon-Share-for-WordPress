<?php
/*
Plugin Name: Mastodon Share for WP
Plugin URI: https://github.com/kernox/mastoshare-wp
Description: Share new wordpress posts on a mastodon instance.
Version: 0.3
Author: Hellexis
Author URI: https://github.com/kernox
Text Domain: mastoshare
*/

require_once 'tootophp/autoload.php';

add_action( 'admin_menu', 'mastoshare_configuration_page');
add_action('save_post', 'mastoshare_toot_post');
add_action('admin_notices', 'mastoshare_admin_notices');
add_action('post_submitbox_misc_actions', 'add_publish_meta_options');
add_action('plugins_loaded', 'mastoshare_init');


function mastoshare_init() {
   $plugin_dir = basename(dirname(__FILE__));
   load_plugin_textdomain('mastoshare', false, $plugin_dir.'/languages' );
}

function mastoshare_configuration_page() {
    add_menu_page(
        'Mastodon Share',
        'Mastodon Share',
        'install_plugins',
        'mastoshare',
        'mastoshare_show_configuration_page',
        'dashicons-share',
        1000
    );
}

function mastoshare_show_configuration_page() {

    if(isset($_POST['save'])) {

        $isValidNonce = wp_verify_nonce($_POST['_wpnonce'], 'mastoshare-configuration');

        if($isValidNonce){
            $message = $_POST['message'];
            update_option('mastoshare-message', sanitize_textarea_field($message));
            update_option('mastoshare-token', sanitize_key($_POST['token']));
            update_option('mastoshare-mode', sanitize_text_field($_POST['mode']));
            update_option('mastoshare-toot-size', (int)$_POST['size']);
        }
    }

    $instance = get_option('mastoshare-instance');
    $token = get_option('mastoshare-token');
    $message = get_option('mastoshare-message', '[title] - [excerpt] - [permalink]');
    $mode = get_option('mastoshare-mode', 'public');
    $tootSize = get_option('mastoshare-toot-size', 500);

    if(isset($_POST['obtain_key'])) {

        $isValidNonce = wp_verify_nonce($_POST['_wpnonce'], 'instance-access-key');

        if($isValidNonce){
            $instance = $_POST['instance'];
            update_option('mastoshare-instance', $instance);

            $tootoPHP = new TootoPHP\TootoPHP($instance);

            // Setting up your App name and your website
            $app = $tootoPHP->registerApp('Mastodon Share for WP', 'http://www.github.com/kernox');
            if ( $app === false) {
                throw new Exception('Problem during register app');
            }

            $authUrl =  $app->getAuthUrl();
            echo '<script>window.open("'.$authUrl.'")</script>';
        }

    }

    include 'form.tpl.php';
}

function add_publish_meta_options($post) {

    $status = get_post_meta($post->ID, 'mastoshare-post-status', true);

    $checked = (!$status) ? 'checked' : '';

    echo '<div class="misc-pub-section misc-pub-section-last">'.
    '<input '.$checked.' type="checkbox" name="toot_on_mastodon" id="toot_on_mastodon">'.
    '<label for="toot_on_mastodon">'. __('Toot on Mastodon', 'mastoshare') .'</label>'.
    '</div>';
}


function mastoshare_toot_post($id){

    $post = get_post($id);
    $tootSize = (int)get_option('mastoshare-toot-size', 500);

    $tootOnMastodonOption = ($_POST['toot_on_mastodon'] == 'on');

    if($tootOnMastodonOption && $post->post_status === 'publish')
    {
        $message = mastoshare_generate_toot($id, $tootSize, $tootSize);
        $message = strip_tags($message);

        if(!empty($message))
        {
            $instance = get_option('mastoshare-instance');

            $tootoPHP = new TootoPHP\TootoPHP($instance);
            $app = $tootoPHP->registerApp('Mastodon Share for WP', 'http://www.github.com/kernox');

            $token = get_option('mastoshare-token');

            $app->registerAccessToken(trim($token));

            $mode = get_option('mastoshare-mode', 'public');
            $toot = $app->postStatus($message, $mode);

            update_post_meta($post->ID, 'mastoshare-post-status', 'off');

            if(isset($toot['error'])){
                update_option(
                    'mastoshare-notice',
                    serialize(
                        array(
                            'message' => 'Mastodon Share: '.__('Sorry, can\'t send toot !', 'mastoshare').
                            '<p><strong>'. __('Instance message', 'mastoshare').'</strong> : '.$toot['error'].'</p>',
                            'class' => 'error'
                            )
                        )
                    );
            } else {
                update_option(
                    'mastoshare-notice',
                    serialize(
                        array(
                            'message' => 'Mastodon Share: '. __('Toot successfully sent !', 'mastoshare'),
                            'class' => 'success'
                            )
                        )
                    );
            }
        }
    }
}

function mastoshare_admin_notices() {

    $notice = unserialize(get_option('mastoshare-notice'));

    if(is_array($notice)){
        echo '<div class="notice notice-'.$notice['class'].' is-dismissible"><p>'.$notice['message'].'</p></div>';
        update_option('mastoshare-notice', null);
    }
}

function mastoshare_generate_toot($post_id, $excerpt_limit, $goal_limit) {

    $post = get_post($post_id);

    $metas = array(
        'title' => $post->post_title,
        'excerpt' => (empty($post->post_excerpt)) ? $post->post_content : $post->post_excerpt,
        'permalink' => get_permalink($post_id)
        );

    $metas['excerpt'] = substr(
        $metas['excerpt'],
        0,
        $excerpt_limit
        ).'...';

    $message = get_option('mastoshare-message');
    foreach($metas as $key => $value){
        $message = str_replace('['.$key.']', $value, $message);
    }

    if(strlen($message) > $goal_limit) {
        //Not good size retry to generate the too
        return mastoshare_generate_toot($post_id, $excerpt_limit - 5, $goal_limit);
    }
    else
    {
        //Good size return the generated toot
        return $message;
    }
}
