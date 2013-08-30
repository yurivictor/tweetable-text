<div class="wrap">
    <h2>Tweetable Options</h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'tweetable_options' ); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label name="<?php echo $key; ?>[color_bg]">Background color:</label></th>
                <td>
                    <input class="color-bg" data-default-color="whitesmoke" name="<?php echo $key; ?>[color_bg]" type="text" value="<?php echo $options['color_bg']; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label name="<?php echo $key; ?>[color_text]">Link text color:</label></th>
                <td>
                    <input class="color-text" data-default-color="#ed2e24" name="<?php echo $key; ?>[color_text]" type="text" value="<?php echo $options['color_text']; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label name="<?php echo $key; ?>[color_hover]">Hover color:</label></th>
                <td>
                    <input class="color-hover" data-default-color="#ed2e24" name="<?php echo $key; ?>[color_hover]" type="text" value="<?php echo $options['color_hover']; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Twitter username (via @):</th>
                <td><input type="text" name="<?php echo $key; ?>[username]" value="<?php echo $options['username']; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Bit.ly username:</th>
                <td><input type="text" name="<?php echo $key; ?>[bitly_user]" value="<?php echo $options['bitly_user']; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Bit.ly API Key:</th>
                <td><input type="text" name="<?php echo $key; ?>[bitly_key]" value="<?php echo $options['bitly_key']; ?>" /></td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>