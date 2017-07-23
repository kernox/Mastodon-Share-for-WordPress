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
</style>

<div class="wrap">
    <h1 class="big-title"><?php _e('Mastodon Share Configuration', 'mastoshare') ?></h1>
    <form method="POST">
        <div class="block">
            <label for="instance"><?php _e('Instance', 'mastoshare') ?></label>
            <input type="text" id="instance" name="instance" size="80" value="<?php echo $instance ?>">
            <p>
                <input
                    class="button button-secondary"
                    type="submit" name="obtain_key"
                    id="obtain_key"
                    value="<?php _e('Obtain Access Key', 'mastoshare') ?>"
                >
            </p>
        </div>
    </form>
    <form method="POST">

        <div class="block">
            <label for="token"><?php _e('Access Key', 'mastoshare') ?></label>
            <input type="text" name="token" id="token" value="<?php echo $token ?>" size="80" required>
        </div>


        <div class="block">
            <label for="message"><?php _e('Message', 'mastoshare') ?></label>
            <textarea  rows="10" cols="80" name="message" id="message"><?php echo htmlentities(stripslashes($message)) ?></textarea>
            <p><i><?php _e('You can use these metas in the message', 'mastoshare') ?></i> : [title], [excerpt], [permalink]</p>

        </div>

        <div class="block">
            <label for="mode"><?php _e('Toot mode', 'mastoshare') ?></label>
            <select name="mode" id="mode">
                <option <?php if($mode == 'public'): ?>selected<?php endif; ?> value="public"><?php _e('Public', 'mastoshare') ?></option>
                <option <?php if($mode == 'unlisted'): ?>selected<?php endif; ?> value="unlisted"><?php _e('Unlisted', 'mastoshare') ?></option>
                <option <?php if($mode == 'private'): ?>selected<?php endif; ?> value="private"><?php _e('Private', 'mastoshare') ?></option>
                <option <?php if($mode == 'direct'): ?>selected<?php endif; ?> value="direct"><?php _e('Direct', 'mastoshare') ?></option>
            </select>
        </div>

        <div class="block">
            <label for="size"><?php _e('Toot size', 'mastoshare') ?></label>
            <input name="size" id="size" type="number" min="100" max="500" value="<?php echo $tootSize ?>"> <?php _e('characters', 'mastoshare') ?>
        </div>

        <input class="button button-primary" type="submit" value="<?php _e('Save configuration', 'mastoshare') ?>" name="save" id="save">

    </form>
</div>