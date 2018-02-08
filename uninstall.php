<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option( 'mastoshare-client-id' );
delete_option( 'mastoshare-client-secret' );
delete_option( 'mastoshare-token' );
delete_option( 'mastoshare-instance' );
delete_option( 'mastoshare-message' );
delete_option( 'mastoshare-mode' );
delete_option( 'mastoshare-toot-size' );
delete_option( 'mastoshare-notice' );