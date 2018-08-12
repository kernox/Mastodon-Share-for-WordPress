<?php

/**
 * Plugin Name: Mastodon Auto Share
 * Plugin URI: https://github.com/kernox/mastoshare-wp
 * Description: Share WordPress posts on a mastodon instance.
 * Version: 1.5
 * Author: Hellexis
 * Author URI: https://github.com/kernox
 * Text Domain: wp-mastodon-share
 * Domain Path: /languages
 */

require_once 'client.php';

class Mastoshare
{
	public function __construct(){
		add_action( 'plugins_loaded', array($this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array($this, 'configuration_page' ) );
		add_action( 'admin_menu', array($this, 'toot_editor_page' ) );
		add_action( 'save_post', array($this, 'toot_post' ) );
		add_action( 'admin_notices', array($this, 'admin_notices' ) );
		add_action( 'add_meta_boxes', array($this, 'add_metabox' ) );
		add_action( 'publish_future_post', array($this, 'toot_scheduled_post') );
	}

	/**
	 * Init
	 *
	 * Plugin initialization
	 *
	 * @return void
	 */
	public function init(){
		$plugin_dir = basename( dirname( __FILE__ ) );
		load_plugin_textdomain( 'wp-mastodon-share', false, $plugin_dir . '/languages' );

		if(isset($_GET['code'])){
			$code = $_GET['code'];
			$client_id = get_option('mastoshare-client-id');
			$client_secret = get_option('mastoshare-client-secret');

			if(!empty($code) && !empty($client_id) && !empty($client_secret))
			{
				echo 'Authentification, please wait ...';
				update_option( 'mastoshare-token', 'nada' );

				$instance = get_option( 'mastoshare-instance' );
				$client = new Client($instance);
				$token = $client->get_bearer_token($client_id, $client_secret, $code, get_admin_url());

				if(isset($token->error)){
					print_r($token);
					//TODO: Propper error message
					update_option(
						'mastoshare-notice',
						serialize(
							array(
								'message' => '<strong>Mastodon Auto Share</strong> : ' . __( "Can't log you in.", 'wp-mastodon-share' ) .
									'<p><strong>' . __( 'Instance message', 'wp-mastodon-share' ) . '</strong> : ' . $token->error_description . '</p>',
									'class' => 'error',
								)
							)
						);
						unset($token);
						update_option('mastoshare-token', '');
				}else{
					update_option('mastoshare-client-id', '');
					update_option('mastoshare-client-secret', '');
					update_option('mastoshare-token', $token->access_token);
				}
				$redirect_url = get_admin_url().'options-general.php?page=wp-mastodon-share';
			}
			else
			{
				//Probably hack or bad refresh, redirect to homepage
				$redirect_url = home_url();
			}

			wp_redirect($redirect_url);
			exit;
		}
	}

	/**
	 * Enqueue_scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts($hook) {

		global $pagenow;

		$infos = get_plugin_data(__FILE__);
		if($pagenow == "options-general.php"){
			//We might be on settings page <-- Do you know a bette solution to get if we are in our own settings page?
			$plugin_url = plugin_dir_url( __FILE__ );
			wp_enqueue_script( 'settings_page', $plugin_url . 'js/settings_page.js', array('jquery'), $infos['Version'], true );
		}
	}

	/**
	 * Configuration_page
	 *
	 * Add the configuration page menu
	 *
	 * @return void
	 */
	public function configuration_page() {
		add_options_page(
			'Mastodon Auto Share',
			'Mastodon Auto Share',
			'install_plugins',
			'wp-mastodon-share',
			array($this, 'show_configuration_page')
		);
	}

    /**
     * Add the toot editor page
     *
     * @return void
     */
	public function toot_editor_page() {
        add_submenu_page(
            NULL,
            'Toot editor',
            'Toot editor',
            'publish_posts',
            'toot_editor',
            array($this, 'show_toot_editor_page')
        );
    }

	/**
	 * Show_configuration_page
	 *
	 * Content of the configuration page
	 *
	 * @throws Exception The exception.
	 * @return void
	 */
	public function show_configuration_page() {

		wp_enqueue_style('mastoshare-configuration', plugin_dir_url(__FILE__).'style.css');

		if( isset( $_GET['disconnect'] ) ) {
			update_option( 'mastoshare-token' , '');
		}elseif( isset( $_GET['testToot'] ) ) {
			$this->sendTestToot();
		}

		$token = get_option( 'mastoshare-token' );

		if ( isset( $_POST['save'] ) ) {

			$is_valid_nonce = wp_verify_nonce( $_POST['_wpnonce'], 'mastoshare-configuration' );

			if ( $is_valid_nonce ) {
				$instance = esc_url( $_POST['instance'] );
				$message = stripslashes($_POST['message']);
				$content_warning = $_POST['content_warning'];

				$client = new Client($instance);
				$redirect_url = get_admin_url();
				$auth_url = $client->register_app($redirect_url);

				if(empty($instance)){
					update_option(
						'mastoshare-notice',
						serialize(
							array(
							'message' => '<strong>Mastodon Auto Share</strong> : ' . __( 'Thank you to set your Mastodon instance before connect !', 'wp-mastodon-share' ),
							'class' => 'error',
							)
						)
					);
				} else 	{
					update_option('mastoshare-client-id', $client->get_client_id());
					update_option('mastoshare-client-secret', $client->get_client_secret());

					update_option( 'mastoshare-instance', $instance );
					update_option( 'mastoshare-message', sanitize_textarea_field( $message ) );
					update_option( 'mastoshare-mode', sanitize_text_field( $_POST['mode'] ) );
					update_option( 'mastoshare-toot-size', (int) $_POST['size'] );

					update_option( 'mastoshare-content-warning', sanitize_textarea_field( $content_warning ) );

					$account = $client->verify_credentials($token);

					if( isset( $account->error ) ){
						echo '<meta http-equiv="refresh" content="0; url=' . $auth_url . '" />';
						echo 'Redirect to '.$instance;
						exit;
					}

					//Inform user that save was successfull
					update_option(
						'mastoshare-notice',
						serialize(
							array(
							'message' => '<strong>Mastodon Auto Share</strong> : ' . __( 'Configuration successfully saved !', 'wp-mastodon-share' ),
							'class' => 'success',
							)
						)
					);

				}

				$this->admin_notices();
			}
		}

		$instance = get_option( 'mastoshare-instance' );

		if( !empty( $token ) ) {
			$client = new Client($instance);
			$account = $client->verify_credentials($token);
		}

		$message = get_option( 'mastoshare-message', "[title]\n[excerpt]\n[permalink]\n[tags]" );
		$mode = get_option( 'mastoshare-mode', 'public' );
		$toot_size = get_option( 'mastoshare-toot-size', 500 );
		$content_warning = get_option( 'mastoshare-content-warning', '');

		include 'form.tpl.php';
	}

	public function show_toot_editor_page() {
	    // http://wordpress.local/wp-admin/admin.php?page=toot_editor
        $id = (int)$_GET['id'];
        $toot_size = get_option( 'mastoshare-toot-size', 500 );
        $generated_toot = $this->getTootFromTemplate($id);

        $backlink = get_edit_post_link($id);

        if(!empty($_POST)){
            $this->toot_post($id);
        }
        include 'toot_editor.tpl.php';

    }

	/**
	 * Toot_post
	 * Post the toot
	 *
	 * @param int $id The post ID.
	 * @return void
	 */
	public function toot_post( $id ) {

		$post = get_post( $id );
		$thumb_url = get_the_post_thumbnail_url($id, 'medium_large'); //Don't change the resolution !
		$toot_size = (int) get_option( 'mastoshare-toot-size', 500 );

		$cw_content = $_POST['cw_content'];
        $message = stripslashes($_POST['toot']);

        if ( !empty( $message ) ) {

            //Save the toot, for scheduling
            if($post->post_status == 'future') {
                update_post_meta($id, 'mastoshare-toot', $message);

                if ( $thumb_url ) {

                    $thumb_path = str_replace( get_site_url(), get_home_path(), $thumb_url );
                    update_post_meta($id, 'mastoshare-toot-thumbnail', $thumb_path);
                }

                update_option(
                    'mastoshare-notice',
                    serialize(
                        array(
                            'message' => '<strong>Mastodon Auto Share</strong> : ' . __( 'Toot saved for schedule !', 'wp-mastodon-share' ),
                            'class' => 'info'
                        )
                    )
                );
            } else if($post->post_status == 'publish') {

                $instance = get_option( 'mastoshare-instance' );
                $access_token = get_option('mastoshare-token');
                $mode = get_option( 'mastoshare-mode', 'public' );

                $client = new Client($instance, $access_token);

                if ( $thumb_url ) {


                    $thumb_path = str_replace( get_site_url(), get_home_path(), $thumb_url );
                    $attachment = $client->create_attachment( $thumb_path );

                    if(is_object($attachment))
                    {
                        $media = $attachment->id;
                    }
                }

                $toot = $client->postStatus($message, $mode, $media, $cw_content);

                update_post_meta( $id, 'mastoshare-post-status', 'off' );

                add_action('admin_notices', 'mastoshare_notice_toot_success');
                if ( isset( $toot->error ) ) {
                    update_option(
                        'mastoshare-notice',
                        serialize(
                            array(
                                'message' => '<strong>Mastodon Auto Share</strong> : ' . __( 'Sorry, can\'t send toot !', 'wp-mastodon-share' ) .
                                '<p><strong>' . __( 'Instance message', 'wp-mastodon-share' ) . '</strong> : ' . $toot->error . '</p>',
                                'class' => 'error',
                            )
                        )
                    );
                } else {
                    update_option(
                        'mastoshare-notice',
                        serialize(
                            array(
                                'message' => '<strong>Mastodon Auto Share</strong> : ' . __( 'Toot successfully sent !', 'wp-mastodon-share' ). ' <a href="'.$toot->url.'" target="_blank">'. __('View Toot', 'wp-mastodon-share') .'</a>',
                                'class' => 'success',
                            )
                        )
                    );
                    //Save the toot url for syndication
                    update_post_meta($id, 'mastoshareshare-lastSuccessfullTootURL',$toot->url);
                }
            }
        }
	}

	/**
	 * Toot_scheduled_post
	 * @param  integer $post_id
	 */
	public function toot_scheduled_post($post_id) {

		$instance = get_option( 'mastoshare-instance' );
		$access_token = get_option('mastoshare-token');
		$mode = get_option( 'mastoshare-mode', 'public' );

		$message = get_post_meta($post_id, 'mastoshare-toot', true);

		$thumb_url = get_the_post_thumbnail_url($post_id);
		$thumb_path = get_post_meta($post_id, 'mastoshare-toot-thumbnail', true);

		$client = new Client($instance, $access_token);

		if ( $thumb_url ) {

			$attachment = $client->create_attachment( $thumb_path );

			if(is_object($attachment))
			{
				$media = $attachment->id;
			}
		}

		$toot = $client->postStatus($message, $mode, $media);
	}

	/**
	 * Admin_notices
	 * Show the notice (error or info)
	 *
	 * @return void
	 */
	public function admin_notices() {

		$notice = unserialize( get_option( 'mastoshare-notice' ) );

		if ( is_array( $notice ) ) {
			echo '<div class="notice notice-' . sanitize_html_class( $notice['class'] ) . ' is-dismissible"><p>' . $notice['message'] . '</p></div>';
			update_option( 'mastoshare-notice', null );
		}
	}

	/**
	 * Add_metabox
	 *
	 * @return void
	 */
	public function add_metabox() {
		add_meta_box(
			'mastoshare_metabox',
			__( 'Mastodon Auto Share', 'wp-mastodon-share' ),
			array($this, 'metabox'),
			['post', 'page'],
			'side',
			'high'
		);
	}

	/**
	 * Metabox
	 *
	 * @param WP_Post $post the current post.
	 * @return void
	 */
	public function metabox( $post ) {
		$status = get_post_meta( $post->ID, 'mastoshare-post-status', true );

		$toot_editor_url = get_admin_url() . 'admin.php?page=toot_editor&id=' . $post->ID;
		echo '<a href="'. $toot_editor_url .'" class="button button-primary button-large">Open Toot editor</a>';
	}


	private function getTootFromTemplate($id) {

		$post = get_post( $id );
		$toot_size = (int) get_option( 'mastoshare-toot-size', 500 );


		$message_template = get_option( 'mastoshare-message', "[title]\n[excerpt]\n[permalink]\n[tags]" );

				//Replace title
				$post_title = get_the_title( $id );
				$message_template = str_replace("[title]", $post_title, $message_template);

				//Replace permalink
				$post_permalink = get_the_permalink( $id );
				$message_template = str_replace("[permalink]", $post_permalink, $message_template);

				//Replace tags  
				$post_tags = get_the_tags($post->ID);
		        $post_tags_content = '';
		        if ( $post_tags ) {
				    foreach( $post_tags as $tag ) {
				    	$post_tags_content =  $post_tags_content . '#'.  preg_replace('/\s+/', '',$tag->name). ' '  ; 
				    }
				    $post_tags_content = trim($post_tags_content);
				}
				$message_template = str_replace("[tags]", $post_tags_content, $message_template);

				//Replace excerpt
				$post_content_long = wp_trim_words($post->post_content);
				$excerpt_len = $toot_size - strlen($message_template) + 9 - 5;

				$post_excerpt = substr($post_content_long,0,$excerpt_len) ."[...]";

				$message_template = str_replace("[excerpt]", $post_excerpt, $message_template);


				return substr($message_template,0,$toot_size);
	}

	private function sendTestToot(){
		$instance = get_option( 'mastoshare-instance' );
		$access_token = get_option('mastoshare-token');
		$mode = get_option( 'mastoshare-mode', 'public' );

		$client = new Client($instance, $access_token);
		//TODO: Add propper message
		$message=__("This is my first post with mastodon auto share",'wp-mastodon-share');
		$media=null;
		$toot = $client->postStatus($message, $mode, $media);

		if ( isset( $toot->error ) ) {
			update_option(
				'mastoshare-notice',
				serialize(
					array(
						'message' => '<strong>Mastodon Auto Share</strong> : ' . __( 'Sorry, can\'t send toot !', 'wp-mastodon-share' ) .
						'<p><strong>' . __( 'Instance message', 'wp-mastodon-share' ) . '</strong> : ' . $toot->error . '</p>',
						'class' => 'error',
					)
				)
			);
		} else {
			update_option(
				'mastoshare-notice',
				serialize(
					array(
						'message' => '<strong>Mastodon Auto Share</strong> : ' . __( 'Toot successfully sent !', 'wp-mastodon-share' ). ' <a href="'.$toot->url.'" target="_blank">'. __('View Toot', 'wp-mastodon-share') .'</a>',
						'class' => 'success',
					)
				)
			);
		}
		$this->admin_notices();
	}
}

$mastoshare = new Mastoshare();
