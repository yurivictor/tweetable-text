<?php if ( 0 == count( $posts ) ): ?>
<label for="transcripts_entry_post"><?php _e( 'No posts', 'posts' ); ?></label>
<?php else: ?>
<label for="transcripts_entry_post"><?php _e( 'Select post', 'posts' ); ?></label>
<select id="transcripts_entry_post" name="transcripts_entry_post">
<?php foreach ( $posts as $posts_id => $posts_name ): ?>
    <option value="<?php echo $posts_id; ?>" <?php echo selected( posts_id, $active_id ); ?>><?php echo $posts_name ?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>