<style>
    form#toot_editor label{
        font-weight: bold;
    }

    form#toot_editor textarea{
        font-size: 16px;
        line-height: 25px;
        min-width: 320px;
    }

    form#toot_editor input{
        font-size: 16px;
    }

    form#toot_editor{
        display: grid;
        grid-gap: 20px;
    }

    form#toot_editor .left-column{
        grid-column: 1/2;
        grid-row: 1;
        /*border: 1px solid red;*/
    }

    form#toot_editor .char-counter{
        font-size: 30px;
        border: 1px solid silver;
        background-color: white;
        padding: 20px 10px;
        display: inline-block;
    }

    form#toot_editor .right-column{
        grid-column: 2/12;
        grid-row: 1;
    }
</style>

<h1><?php _e('Toot editor','wp-mastodon-share') ?></h1>
<form id="toot_editor" method="POST">
    <div class="left-column">
        <h2><?php _e('General', 'wp-mastodon-share') ?></h2>
        <label for="toot"><?php _e('Message', 'wp-mastodon-share') ?></label>
        <p><textarea name="toot" id="toot" rows="15" maxlength="<?php echo $toot_size ?>"><?php echo trim($generated_toot) ?></textarea></p>
    </div>
    <div class="right-column">
        <h2><?php _e('Options', 'wp-mastodon-share') ?></h2>
        <label for="cw_content"><?php _e('CW Content', 'wp-mastodon-share') ?></label>
        <p><input id="cw_content" name="cw_content" type="text"></p>

        <h2><?php _e('Informations', 'wp-mastodon-share') ?></h2>
        <span class="char-counter"><span class="current-size">1</span> / <?php echo $toot_size ?></span>
    </div>
    <p>
        <a class="button button-large" href="<?php echo $backlink ?>"><?php _e('Retour', 'wp-mastodon-share') ?></a>
        <input type="submit" class="button button-large button-primary" value="<?php _e('Send to Mastodon', 'wp-mastodon-share') ?>">
    </p>

</form>

<script>
    jQuery(function(){

        var refreshCounter = function(){

            var tootLength = jQuery('#toot').val().length;
            jQuery('.char-counter .current-size').text(tootLength);
        };

        refreshCounter();

        jQuery('#toot').on('keyup', function(){
            refreshCounter();
        });

        jQuery('#toot').on('change', function(){
            refreshCounter();
        });

    });
</script>
