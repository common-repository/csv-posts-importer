<h2><?php _e( 'Step', 'csv-importer-posts' ) ?> 2/2</h2>

<form method="post">
	<?php wp_nonce_field( 'cip_settings_form_save' ) ?>
	<input type="hidden" name="csv_file" value="<?php echo self::$csv_file_hash ?>">
	<input type="hidden" name="action" value="create_posts">

	<div class="left-side">
		<h3>Post Types</h3>
		<select name="post_type" id="post-type" size="5">
			<?php foreach ( self::$fields as $post_type ): ?>

				<?php $current_post_type = ''; ?>


				<?php echo $current_post_type != $post_type['slug'] ? '<option value="' . $post_type['slug'] . '">' . $post_type['title'] . ' (' . $post_type['slug'] . ')' . '</option>' : '' ?>

				<?php $current_post_type = $post_type['slug']; ?>

			<?php endforeach; ?>
		</select>

		<hr />

		<input type="submit" class="button button-primary button-large" name="create_posts"
		       value="<?php _e( 'Import', 'csv-importer-posts' ) ?>" disabled="disabled" />
	</div>

	<div class="right-side">

		<div>
			<div class="field-block">
				<h3><?php _e( 'Fields', 'csv-importer-posts' ) ?></h3>
			</div>
			<div class="csv-block">
				<h3><?php _e( 'CSV', 'csv-importer-posts' ) ?></h3>
			</div>
			<div class="csv-block">
				<h3><?php _e( 'Default Values', 'csv-importer-posts' ) ?></h3>
			</div>
		</div>

		<div class="clear-both"></div>

		<hr />

		<div>
			<?php foreach ( self::$fields as $post_type ): ?>

			<?php $current_post_type = ''; ?>

			<?php if ( $current_post_type != $post_type['title'] ): ?>

		</div>

		<div id="<?php echo $post_type['slug'] ?>" class="post-type-block">
			<?php $group_counter = 0; ?>
			<?php foreach ( self::$main_fields as $main_field ): ?>

				<div class="row">
					<div class="field-block">
						<?php echo $main_field['label']; ?>
					</div>
					<div class="csv-block">
						<select name="<?php echo $post_type['slug'] . '[' . $main_field['name'] . ']' ?>"
						        class="group-<?php echo $post_type['slug']; ?>"
						        id="group_<?php echo $group_counter; ?>">
							<option value=""></option>
							<?php foreach ( self::$csv[0] as $field => $value ): ?>
								<option value="<?php echo $field ?>"><?php echo $field ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="defaults-block">
						<input type="text"
						       name="<?php echo $post_type['slug'] . '[def-' . $main_field['name'] . ']' ?>" />
					</div>
				</div>
				<?php $group_counter ++; ?>
			<?php endforeach; ?>

			<hr />

			<div class="add-field-block">

				<?php endif; ?>

				<?php echo $current_post_type != $post_type['slug'] ? '<button class="button btn-add-field" data-post-type="' . $post_type['slug'] . '">' . __( 'Add field',
						'csv-importer-posts' ) . '</button>' : '' ?>

				<select class="hidden">
					<?php if ( $current_post_type != $post_type['title'] ): ?>
				</select>

				<select name="all_fields[]" id="all_fields_<?php echo $post_type['slug']; ?>">
					<option value=""></option>
					<?php endif; ?>

					<?php foreach ( $post_type['fields'] as $group_title => $field_group ): ?>

						<optgroup label="<?php echo $group_title ?>">

							<?php foreach ( $field_group as $field ): ?>
								<option
									value="<?php echo isset( $field['key'] ) ? $field['key'] : $field['name'] ?>"><?php echo $field['label'] ?>
								</option>
							<?php endforeach; ?>

						</optgroup>

					<?php endforeach; ?>

				</select>

			</div>

			<?php $current_post_type = $post_type['title']; ?>

			<?php endforeach; ?>

		</div>
	</div>

</form>

<div class="import-progress-block hidden">
	<?php include( CIP_PLUGIN_DIR . '/views/progress.php' ) ?>
</div>
