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

	.account{
		border: 1px solid silver;
		background-color: white;
		padding: 15px;
		float:left;

	}

	.m-avatar{
		float:left;
		border-radius: 100px;
		margin-right: 20px;
		width: 60px;
	}
	.separator{
		clear:both;
	}

	.details{
		float:left;
	}

	.details .link{
		color:black;
		text-decoration: none;
	}

	.connected{
		color: #00AA00;
		font-size: 16px;
		margin-bottom: 10px;
	}

	.disconnected{
		color: #FF0000;
		font-size: 16px;
		text-align: center;
		width: 100%;
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
			<label for="token"><?php esc_html_e( 'Status', 'wp-mastodon-share' ); ?></label>
			<div class="account">
				<a href="<?php echo $account->url ?>" target="_blank"><img class="m-avatar" src="<?php echo $account->avatar ?>"></a>
				<div class="details">
					<?php if(!$account->error): ?>
						<div class="connected"><?php esc_html_e( 'Connected as', 'wp-mastodon-share' ); ?>&nbsp;<?php echo $account->username ?></div>
						<a class="link" href="<?php echo $account->url ?>" target="_blank"><?php echo $account->url ?></a>
					<?php else: ?>
						<div class="disconnected"><?php esc_html_e( 'Disconnected', 'wp-mastodon-share' ); ?></div>
					<?php endif ?>

				</div>
				<div class="separator"></div>
			</div>
		</div>

		<div class="separator" style="margin-bottom:20px"></div>

		<div class="block">
			<label for="message"><?php esc_html_e( 'Message', 'wp-mastodon-share' ); ?></label>
			<textarea  rows="10" cols="80" name="message" id="message"><?php esc_html_e( stripslashes( $message ) ); ?></textarea>
			<p><i><?php esc_html_e( 'You can use these metas in the message', 'wp-mastodon-share' ); ?></i> : [title], [excerpt], [permalink] <?php esc_html_e( 'and', 'wp-mastodon-share' ); ?> [tags]</p>
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
