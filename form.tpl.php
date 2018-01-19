<style>
	label{
		display: block;
		font-weight: bold;
	}

	.block{
		margin-bottom: 20px;
	}

	#instance, #message, #token{
		width: 500px;
	}

	h1.big-title{
		margin-bottom: 20px;
	}

	.spacer{
		margin: 20px 0;
	}
</style>

<div class="wrap">
	<h1 class="big-title"><?php esc_html_e( 'Mastodon Share Configuration', 'wp-mastodon-share' ); ?></h1>
	<form method="POST">
		<?php wp_nonce_field( 'mastoshare-configuration' ); ?>
		<div class="block">
			<label for="instance"><?php esc_html_e( 'Instance', 'wp-mastodon-share' ); ?></label>
			<input type="text" id="instance" name="instance" size="80" value="<?php esc_attr_e( $instance ); ?>">
		</div>

		<div class="block">
			<label for="token"><?php esc_html_e( 'Access Key', 'wp-mastodon-share' ); ?></label>
			<?php echo $token ?>
		</div>


		<div class="block">
			<label for="message"><?php esc_html_e( 'Message', 'wp-mastodon-share' ); ?></label>
			<textarea  rows="10" cols="80" name="message" id="message"><?php esc_html_e( stripslashes( $message ) ); ?></textarea>
			<p><i><?php esc_html_e( 'You can use these metas in the message', 'wp-mastodon-share' ); ?></i> : [title], [excerpt], [permalink] and [tags]</p>
		</div>

		<div class="block">
			<label for="mode"><?php esc_html_e( 'Toot mode', 'wp-mastodon-share' ); ?></label>
			<select name="mode" id="mode">
				<option <?php if ( 'public' === $mode ): ?>selected<?php endif; ?> value="public"><?php esc_html_e( 'Public', 'wp-mastodon-share' ); ?></option>
				<option <?php if ( 'unlisted' === $mode ): ?>selected<?php endif; ?> value="unlisted"><?php esc_html_e( 'Unlisted', 'wp-mastodon-share' ); ?></option>
				<option <?php if ( 'private' === $mode ): ?>selected<?php endif; ?> value="private"><?php esc_html_e( 'Private', 'wp-mastodon-share' ); ?></option>
				<option <?php if ( 'direct' === $mode ): ?>selected<?php endif; ?> value="direct"><?php esc_html_e( 'Direct', 'wp-mastodon-share' ); ?></option>
			</select>
		</div>

		<div class="block">
			<label for="size"><?php esc_html_e( 'Toot size', 'wp-mastodon-share' ); ?></label>
			<input name="size" id="size" type="number" min="100" max="500" value="<?php esc_attr_e( $toot_size ); ?>"> <?php esc_html_e( 'characters', 'wp-mastodon-share' ); ?>
		</div>

		<input class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save configuration', 'wp-mastodon-share' ); ?>" name="save" id="save">

	</form>

	<hr class="spacer">
	<script src="https://liberapay.com/hellexis/widgets/button.js"></script>
	<noscript><a href="https://liberapay.com/hellexis/donate"><img src="https://liberapay.com/assets/widgets/donate.svg"></a></noscript>

</div>
