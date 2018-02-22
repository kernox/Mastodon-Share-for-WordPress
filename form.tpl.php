<?php define("ACCOUNT_CONNECTED",isset($account) && $account !== null);?>

<div class="wrap">
	<h1><?php esc_html_e( 'Mastodon Auto Share Configuration', 'wp-mastodon-share' ); ?></h1>
	<form method="POST">
		<?php wp_nonce_field( 'mastoshare-configuration' ); ?>
		<table class="form-table">
			<tbody>
				<tr style="display:<?php echo !ACCOUNT_CONNECTED ? "block":"none"?>">
					<th scope="row">
						<label for="instance"><?php esc_html_e( 'Instance', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<input type="text" id="instance" name="instance" size="80" value="<?php esc_attr_e( $instance ); ?>" list="mInstances">
					</td>
					<td>
						<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Connect to Mastodon', 'wp-mastodon-share' ); ?>" name="save" id="save">
					</td>
				</tr>
				<tr style="display:<?php echo ACCOUNT_CONNECTED ? "block" : "none"?>">
					<th scope="row">
						<label><?php esc_html_e( 'Status', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<div class="account">
						<?php if(ACCOUNT_CONNECTED): ?>
								<a href="<?php echo $account->url ?>" target="_blank"><img class="m-avatar" src="<?php echo $account->avatar ?>"></a>
						<?php endif ?>
							<div class="details">
								<?php if(ACCOUNT_CONNECTED): ?>
									<div class="connected"><?php esc_html_e( 'Connected as', 'wp-mastodon-share' ); ?>&nbsp;<?php echo $account->username ?></div>
									<a class="link" href="<?php echo $account->url ?>" target="_blank"><?php echo $account->url ?></a>

									<p><a href="<?php echo $_SERVER['REQUEST_URI'] . '&disconnect' ?>" class="button"><?php esc_html_e( 'Disconnect', 'wp-mastodon-share' ); ?></a>
									<a href="<?php echo $_SERVER['REQUEST_URI'] . '&testToot' ?>" class="button"><?php esc_html_e( 'Send test toot', 'wp-mastodon-share' ); ?></a></p>
								<?php else: ?>
									<div class="disconnected"><?php esc_html_e( 'Disconnected', 'wp-mastodon-share' ); ?></div>
								<?php endif ?>
							</div>
							<div class="separator"></div>
						</div>
					</td>
				</tr>
				<tr style="display:<?php echo ACCOUNT_CONNECTED ? "block" : "none"?>">
					<th scope="row">
						<label for="content_warning"><?php esc_html_e( 'Default Content Warning', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<input type="text" id="content_warning" name="content_warning" style="width:300px" value="<?php esc_attr_e( $content_warning ); ?>">
					</td>
				</tr>
				<tr style="display:<?php echo ACCOUNT_CONNECTED ? "block" : "none"?>">
					<th scope="row">
						<label for="message"><?php esc_html_e( 'Message', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<textarea  rows="10" cols="80" name="message" id="message"><?php esc_html_e( stripslashes( $message ) ); ?></textarea>
						<p class="description"><i><?php esc_html_e( 'You can use these metas in the message', 'wp-mastodon-share' ); ?></i>
							: [title], [excerpt], [permalink] <?php esc_html_e( 'and', 'wp-mastodon-share' ); ?> [tags]</p>
					</td>
				</tr>
				<tr style="display:<?php echo ACCOUNT_CONNECTED ? "block" : "none"?>">
					<th scope="row">
						<label for="mode"><?php esc_html_e( 'Toot mode', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
							<label><input type="radio" name="mode" <?php if ( 'public' === $mode ): ?>checked<?php endif; ?> value="public"><?php esc_html_e( 'Public', 'wp-mastodon-share' ); ?></label>
							<label><input type="radio" name="mode" <?php if ( 'unlisted' === $mode ): ?>checked<?php endif; ?> value="unlisted"><?php esc_html_e( 'Unlisted', 'wp-mastodon-share' ); ?></label>
							<label><input type="radio" name="mode" <?php if ( 'private' === $mode ): ?>checked<?php endif; ?> value="private"><?php esc_html_e( 'Private', 'wp-mastodon-share' ); ?></label>
							<label><input type="radio" name="mode" <?php if ( 'direct' === $mode ): ?>checked<?php endif; ?> value="direct"><?php esc_html_e( 'Direct', 'wp-mastodon-share' ); ?></label>
					</td>
				</tr>
				<tr style="display:<?php echo ACCOUNT_CONNECTED ? "block" : "none"?>">
					<th scope="row">
						<label for="size"><?php esc_html_e( 'Toot size', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<input name="size" id="size" type="number" min="100" max="500" value="<?php esc_attr_e( $toot_size ); ?>"> <?php esc_html_e( 'characters', 'wp-mastodon-share' ); ?>
					</td>
				</tr>
			</tbody>
		</table>

		<?php if(ACCOUNT_CONNECTED): ?>
			<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save configuration', 'wp-mastodon-share' ); ?>" name="save" id="save">
		<?php endif ?>

	</form>

	<hr class="spacer">

	<a href="https://github.com/kernox/Mastodon-Share-for-WordPress" target="_blank" class="github-icon">
		<svg aria-hidden="true" class="octicon octicon-mark-github" height="32" version="1.1" viewBox="0 0 16 16" width="32"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path></svg>
	</a>

	<a href="https://liberapay.com/Mastodon-Auto-Share-Team/donate"><img src="img/donate.svg"></a>
<?php
	require("instanceList.php")
?>
</div>
