<?php
/*
Plugin Name: Mastodon Share for WP
Plugin URI: https://github.com/kernox
Description: Share new wordpress posts on a mastodon instance.
Version: 0.1
Author: Hellexis
Author URI: https://github.com/kernox
Text Domain: mastoshare
*/

require_once 'tootophp/autoload.php';

add_action( 'admin_menu', 'configuration_page');
add_action('save_post', 'toot_post');

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

    }

    $instance = get_option('mastoshare-instance');
    $token = get_option('mastoshare-token');
    $message = get_option('mastoshare-message', '[title] - [content] - [permalink]');
    $mode = get_option('mastoshare-mode', 'public');

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

    if($post->post_status === 'publish')
    {

        $metas = array(
            'title' => $post->post_title,
            'content' => $post->post_content,
            'permalink' => get_permalink($id)
        );

        $message = get_option('mastoshare-message');
        foreach($metas as $key => $value){
            $message = str_replace('['.$key.']', $value, $message);
        }

        $instance = get_option('mastoshare-instance');

        $tootoPHP = new TootoPHP\TootoPHP($instance);
        $app = $tootoPHP->registerApp('Mastodon Share for WP', 'http://www.github.com/kernox');

        $token = get_option('mastoshare-token');
        $app->registerAccessToken(trim($token));

        $mode = get_option('mastoshare-mode', 'public');
        
        @$app->postStatus($message, $mode); //Oui je sais l'@ c'est moche
    }

}
