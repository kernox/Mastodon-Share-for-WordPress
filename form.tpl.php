<div class="wrap">
	<h1><?php esc_html_e( 'Mastodon Share Configuration', 'wp-mastodon-share' ); ?></h1>
	<form method="POST">
		<?php wp_nonce_field( 'mastoshare-configuration' ); ?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="instance"><?php esc_html_e( 'Instance', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<input type="text" id="instance" name="instance" size="80" value="<?php esc_attr_e( $instance ); ?>" pattern="^http.+">
						<p class="description"><?php esc_html_e('The instance url must be like http(s)://domain.tld', 'wp-mastodon-share') ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php esc_html_e( 'Status', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<div class="account">
							<a href="<?php echo $account->url ?>" target="_blank"><img class="m-avatar" src="<?php echo $account->avatar ?>"></a>
							<div class="details">
								<?php if($account !== null): ?>
									<div class="connected"><?php esc_html_e( 'Connected as', 'wp-mastodon-share' ); ?>&nbsp;<?php echo $account->username ?></div>
									<a class="link" href="<?php echo $account->url ?>" target="_blank"><?php echo $account->url ?></a>

									<p><a href="<?php echo $_SERVER['REQUEST_URI'] . '&disconnect' ?>" class="button"><?php esc_html_e( 'Disconnect', 'wp-mastodon-share' ); ?></a></p>
								<?php else: ?>
									<div class="disconnected"><?php esc_html_e( 'Disconnected', 'wp-mastodon-share' ); ?></div>
								<?php endif ?>

							</div>
							<div class="separator"></div>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="message"><?php esc_html_e( 'Message', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<textarea  rows="10" cols="80" name="message" id="message"><?php esc_html_e( stripslashes( $message ) ); ?></textarea>
						<p class="description"><i><?php esc_html_e( 'You can use these metas in the message', 'wp-mastodon-share' ); ?></i>
							: [title], [excerpt], [permalink] <?php esc_html_e( 'and', 'wp-mastodon-share' ); ?> [tags]</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="mode"><?php esc_html_e( 'Toot mode', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<select name="mode" id="mode">
							<option <?php if ( 'public' === $mode ): ?>selected<?php endif; ?> value="public"><?php esc_html_e( 'Public', 'wp-mastodon-share' ); ?></option>
							<option <?php if ( 'unlisted' === $mode ): ?>selected<?php endif; ?> value="unlisted"><?php esc_html_e( 'Unlisted', 'wp-mastodon-share' ); ?></option>
							<option <?php if ( 'private' === $mode ): ?>selected<?php endif; ?> value="private"><?php esc_html_e( 'Private', 'wp-mastodon-share' ); ?></option>
							<option <?php if ( 'direct' === $mode ): ?>selected<?php endif; ?> value="direct"><?php esc_html_e( 'Direct', 'wp-mastodon-share' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="size"><?php esc_html_e( 'Toot size', 'wp-mastodon-share' ); ?></label>
					</th>
					<td>
						<input name="size" id="size" type="number" min="100" max="500" value="<?php esc_attr_e( $toot_size ); ?>"> <?php esc_html_e( 'characters', 'wp-mastodon-share' ); ?>
					</td>
				</tr>
			</tbody>
		</table>

		<?php if($account !== null): ?>
			<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save configuration', 'wp-mastodon-share' ); ?>" name="save" id="save">
		<?php else: ?>
			<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Connect to Mastodon', 'wp-mastodon-share' ); ?>" name="save" id="save">
		<?php endif ?>

	</form>

	<hr class="spacer">

	<a href="https://github.com/kernox/Mastodon-Share-for-WordPress" target="_blank" class="github-icon">
		<svg aria-hidden="true" class="octicon octicon-mark-github" height="32" version="1.1" viewBox="0 0 16 16" width="32"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0 0 16 8c0-4.42-3.58-8-8-8z"></path></svg>
	</a>

	<script class="liberapay" src="https://liberapay.com/hellexis/widgets/button.js"></script>
	<noscript><a href="https://liberapay.com/hellexis/donate"><img src="https://liberapay.com/assets/widgets/donate.svg"></a></noscript>

</div>
