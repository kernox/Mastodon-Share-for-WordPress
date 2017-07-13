<?php
/*
Plugin Name: Mastodon Share for WP
Plugin URI: https://github.com/kernox
Description: Share new wordpress posts on a mastodon instance.
Version: 0.2
Author: Hellexis
Author URI: https://github.com/kernox
Text Domain: mastoshare
*/

require_once 'tootophp/autoload.php';
session_start();
add_action( 'admin_menu', 'configuration_page');
add_action('save_post', 'toot_post');
add_action('admin_notices', 'admin_notices');


function configuration_page() {
    add_menu_page(
        'Mastodon Share',
        'Mastodon Share',
        'install_plugins',
        'mastoshare',
        'show_configuration_page',
        'dashicons-share',
        1000
        );
}

function show_configuration_page() {

    if(isset($_POST['save'])) {

        $message = $_POST['message'];
        update_option('mastoshare-message', $message);
        update_option('mastoshare-token', $_POST['token']);
        update_option('mastoshare-mode', $_POST['mode']);
        update_option('mastoshare-toot-size', $_POST['size']);

    }

    $instance = get_option('mastoshare-instance');
    $token = get_option('mastoshare-token');
    $message = get_option('mastoshare-message', '[title] - [excerpt] - [permalink]');
    $mode = get_option('mastoshare-mode', 'public');
    $tootSize = get_option('mastoshare-toot-size', 500);

    if(isset($_POST['obtain_key'])) {

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

    include 'form.tpl.php';
}


function toot_post($id){

    $post = get_post($id);
    $tootSize = (int)get_option('mastoshare-toot-size', 500);

    if($post->post_status === 'publish')
    {
        $message = generate_toot($id, $tootSize, $tootSize);

        if(!empty($message))
        {
            $instance = get_option('mastoshare-instance');

            $tootoPHP = new TootoPHP\TootoPHP($instance);
            $app = $tootoPHP->registerApp('Mastodon Share for WP', 'http://www.github.com/kernox');

            $token = get_option('mastoshare-token');

            $app->registerAccessToken(trim($token));

            $mode = get_option('mastoshare-mode', 'public');
            $toot = $app->postStatus($message, $mode);

            if(isset($toot['error'])){
                $_SESSION['mastoshare-notice'] = array('message' => 'Mastodon Share : Sorry, can\'t send toot !', 'class' => 'error');
            } else {
                $_SESSION['mastoshare-notice'] = array('message' => 'Mastodon Share : Toot successfully sent !', 'class' => 'success');
            }
        }
    }
}

function admin_notices() {
    if(isset($_SESSION['mastoshare-notice'])) {
        $notice = $_SESSION['mastoshare-notice'];
        unset($_SESSION['mastoshare-notice']);

        echo '<div class="notice notice-'.$notice['class'].' is-dismissible"><p>'.$notice['message'].'</p></div>';
    }
}

function generate_toot($post_id, $excerpt_limit, $goal_limit) {

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
        return generate_toot($post_id, $excerpt_limit - 5, $goal_limit);
    }
    else
    {
        //Good size return the generated toot
        return $message;
    }
}
