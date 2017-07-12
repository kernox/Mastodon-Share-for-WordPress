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
    <h1 class="big-title">Mastodon Share Configuration</h1>
    <form method="POST">
        <div class="block">
            <label for="instance">Instance</label>
            <input type="text" id="instance" name="instance" size="80" value="<?php echo $instance ?>">
            <p>
                <input class="button button-secondary" type="submit" name="obtain_key" id="obtain_key" value="Obtain Access Key">
            </p>
        </div>
    </form>
    <form method="POST">

        <div class="block">
            <label for="token">Access Key</label>
            <input type="text" name="token" id="token" value="<?php echo $token ?>" size="80">
        </div>

        <div class="block">
            <label for="message">Message</label>
            <textarea  rows="20" cols="80" name="message" id="message"><?php echo htmlentities(stripslashes($message)) ?></textarea>
        </div>

        <div class="block">
            <label for="mode">Toot mode</label>
            <select name="mode" id="mode">
                <option <?php if($mode == 'public'): ?>selected<?php endif; ?> value="public">Public</option>
                <option <?php if($mode == 'unlisted'): ?>selected<?php endif; ?> value="unlisted">Unlisted</option>
                <option <?php if($mode == 'private'): ?>selected<?php endif; ?> value="private">Private</option>
                <option <?php if($mode == 'direct'): ?>selected<?php endif; ?> value="direct">Direct</option>
            </select>
        </div>

        <input class="button button-primary" type="submit" value="Save configuration" name="save" id="save">

    </form>
</div>