<?php if ( isset( self::$messages['error'] ) || isset( self::$messages['success'] ) ): ?>
	<div id="message" class="notice-<?php echo key( self::$messages ) ?> notice is-dismissible">
		<p><strong><?php echo self::$messages[ key( self::$messages ) ] ?></strong>.</p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'csv-importer-posts' ) ?></span>
		</button>
	</div>
<?php endif; ?>

<h2><?php _e( 'Step', 'csv-importer-posts' ) ?> 1/2</h2>

<form enctype="multipart/form-data" method="post">
	<?php wp_nonce_field( 'cip_settings_form_save' );
	?>
	<input type="file" name="file" />
	<br />
	<br />
	<input type="submit" class="button button-primary button-large" name="upload_file"
	       value="<?php _e( 'Upload file', 'csv-importer-posts' ) ?>" />
</form>
