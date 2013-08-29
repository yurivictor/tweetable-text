<div class="wrap">
    <h2>Tweetable Options</h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'tweetable_options' ); ?>
        <table class="form-table">
            <tr valign="top"><th scope="row">Backround color:</th>
                <td><input type="text" name="<?php echo $key; ?>[color_bg]" value="<?php echo $options['color_bg']; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Text color:</th>
                <td><input type="text" name="<?php echo $key; ?>[color_text]" value="<?php echo $options['color_text']; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Text hover color:</th>
                <td><input type="text" name="<?php echo $key; ?>[color_hover]" value="<?php echo $options['color_hover']; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Twitter username:</th>
                <td><input type="text" name="<?php echo $key; ?>[username]" value="<?php echo $options['username']; ?>" /></td>
            </tr>            
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>