<?php

class Bwt_Csv_Importer {

	const PLUGIN_PHP_VERSION = '5.4';

	/**
	 * An array of messages as [$type => $text]
	 *
	 * @var array
	 */
	private static $messages = [];

	/**
	 * The data from CSV as [$field => $value]
	 *
	 * @var array
	 */
	private static $csv = [];

	/**
	 * Path to uploaded csv file
	 *
	 * @var string
	 */
	private static $csv_file_hash = '';

	/**
	 * All fields grouped by Post Types
	 *
	 * @var array
	 */
	private static $fields = [];

	/**
	 * Default fields
	 *
	 * @var array
	 */
	private static $default_fields = [];

	/**
	 * Main fields
	 *
	 * @var array
	 */
	private static $main_fields = [];

	/**
	 * Posts import progress data
	 *
	 * @var string
	 */
	private static $progress_data = '';

	/**
	 * Init plugin
	 */
	public static function init() {
		set_time_limit( 0 );
		add_action( 'admin_menu', [ 'Bwt_Csv_Importer', 'create_menu' ] );
		add_action( 'plugins_loaded', [ 'Bwt_Csv_Importer', 'lang_init' ] );
		add_action( 'wp_ajax_create_posts', [ 'Bwt_Csv_Importer', 'ajax_create_posts' ] );
		add_action( 'wp_ajax_get_import_progress', [ 'Bwt_Csv_Importer', 'ajax_get_import_progress' ] );
		add_action( 'wp_ajax_create_posts_error', [ 'Bwt_Csv_Importer', 'ajax_create_posts_error' ] );

		self::set_default_values();
	}

	/**
	 * Set default values
	 */
	public static function set_default_values() {
		self::$default_fields = [
			[ 'label' => __( 'ID', 'bwt-csv-importer' ), 'name' => 'import_id' ],
			[ 'label' => __( 'Post Date', 'bwt-csv-importer' ), 'name' => 'post_date' ],
			[ 'label' => __( 'Post Date GTM', 'bwt-csv-importer' ), 'name' => 'post_date_gmt' ],
			[ 'label' => __( 'Post Content Filtered', 'bwt-csv-importer' ), 'name' => 'post_content_filtered' ],
			[ 'label' => __( 'Post Excerpt', 'bwt-csv-importer' ), 'name' => 'post_excerpt' ],
			[ 'label' => __( 'Post Status', 'bwt-csv-importer' ), 'name' => 'post_status' ],
			[ 'label' => __( 'Ping Status', 'bwt-csv-importer' ), 'name' => 'ping_status' ],
			[ 'label' => __( 'Post Password', 'bwt-csv-importer' ), 'name' => 'post_password' ],
			[ 'label' => __( 'Slug', 'bwt-csv-importer' ), 'name' => 'post_name' ],
			[ 'label' => __( 'To Ping', 'bwt-csv-importer' ), 'name' => 'to_ping' ],
			[ 'label' => __( 'Pinged', 'bwt-csv-importer' ), 'name' => 'pinged' ],
			[ 'label' => __( 'Post Parent', 'bwt-csv-importer' ), 'name' => 'post_parent' ],
			[ 'label' => __( 'Menu Order', 'bwt-csv-importer' ), 'name' => 'menu_order' ],
			[ 'label' => __( 'GUID', 'bwt-csv-importer' ), 'name' => 'guid' ],
			[ 'label' => __( 'Taxonomy', 'bwt-csv-importer' ), 'name' => 'tax_input' ],
			[ 'label' => __( 'Meta', 'bwt-csv-importer' ), 'name' => 'meta_input' ]
		];
		self::$main_fields    = [
			[ 'label' => __( 'Post Content', 'bwt-csv-importer' ), 'name' => 'post_content' ],
			[ 'label' => __( 'Post Title', 'bwt-csv-importer' ), 'name' => 'post_title' ],
			[ 'label' => __( 'Comment Status', 'bwt-csv-importer' ), 'name' => 'comment_status' ],
			[ 'label' => __( 'Post Category', 'bwt-csv-importer' ), 'name' => 'post_category' ],
			[ 'label' => __( 'Featured Image', 'bwt-csv-importer' ), 'name' => 'featured_image' ]
		];
	}

	/**
	 * Display error ajax create posts
	 */
	public static function ajax_create_posts_error() {
		self::$messages = [
			'error' => __( 'The script was interrupted. Try to import fewer posts',
				'bwt-csv-importer' )
		];
		include( CIP_PLUGIN_DIR . '/views/main.php' );
		wp_die();
	}

	/**
	 * Display posts import progress
	 */
	public static function ajax_get_import_progress() {
		echo get_option( 'bwt_cip_progress' );
		wp_die();
	}

	/**
	 * Start posts import
	 */
	public static function ajax_create_posts() {
		check_admin_referer( 'cip_settings_form_save' );
		self::create_posts();
		update_option( 'bwt_cip_status', '' );
		echo json_encode( self::$progress_data );
		wp_die();
	}

	/**
	 * Verify PHP version
	 */
	public static function on_activation() {
		if ( version_compare( phpversion(), self::PLUGIN_PHP_VERSION ) < 0 ) {
			wp_die( sprintf( __( 'This plugin requires PHP version %s or higher.', 'bwt-csv-importer' ),
				self::PLUGIN_PHP_VERSION ) );
		}
	}

	/**
	 * Delete options from DB
	 */
	public static function on_deactivate() {
		delete_option( 'bwt_cip_status' );
		delete_option( 'bwt_cip_progress' );
	}

	/**
	 * Loads the plugin's translated strings.
	 */
	public static function lang_init() {
		load_plugin_textdomain( 'bwt-csv-importer', false, plugin_basename( dirname( __FILE__ ) . '/languages' ) );
	}

	/**
	 * Create admin menu
	 */
	public static function create_menu() {
		add_menu_page(
			__( 'CSV Importer Page', 'bwt-csv-importer' ),
			__( 'CSV Importer', 'bwt-csv-importer' ),
			'manage_options',
			'bwt_csv_importer',
			[ 'Bwt_Csv_Importer', 'view_page' ]
		);
	}

	/**
	 * Include HTML templates
	 */
	public static function view_page() {
		echo '<style>';
		include( CIP_PLUGIN_DIR . '/css/style.css' );
		echo '</style>';
		echo '<div class="wrap cip-main">';
		echo '<h1>';
		_e( 'Import CSV', 'csv-importer-posts' );
		echo '</h1>';
		if ( get_option( 'bwt_cip_status' ) ) {
			include( CIP_PLUGIN_DIR . '/views/progress.php' );
			echo '<script>';
			include( CIP_PLUGIN_DIR . '/js/progress.js' );
			echo '</script>';
		} elseif ( isset( $_POST['upload_file'] ) ) {
			check_admin_referer( 'cip_settings_form_save' );
			self::csv_upload();
			if ( isset( self::$messages['error'] ) ) {
				include( CIP_PLUGIN_DIR . '/views/main.php' );
			} else {
				self::get_all_fields();
				include( CIP_PLUGIN_DIR . '/views/table.php' );
				echo '<script>';
				include( CIP_PLUGIN_DIR . '/js/main.js' );
				echo '</script>';
			}
		} else {
			include( CIP_PLUGIN_DIR . '/views/main.php' );
		}
		echo '</div>';
	}

	/**
	 * Upload CSV
	 */
	public static function csv_upload() {
		$file = $_FILES['file']['tmp_name'];
		if ( $file !== '' ) {
			$arr_csv = self::csv_to_array( $file );
			if ( ! $arr_csv ) {
				self::$messages = [ 'error' => __( 'Invalid file', 'bwt-csv-importer' ) ];
			} else {
				self::$csv           = $arr_csv;
				self::$csv_file_hash = hash_file( 'md5', $file );
				$csv_file            = CIP_PLUGIN_DIR . '/tmp/' . self::$csv_file_hash;
				if ( ! move_uploaded_file( $file, $csv_file ) ) {
					self::$messages = [ 'error' => __( 'File upload error', 'bwt-csv-importer' ) ];
				}
			}
		} else {
			self::$messages = [ 'error' => __( 'Please select file', 'bwt-csv-importer' ) ];
		}
	}

	/**
	 * Return data from CSV
	 *
	 * @param string $filename
	 * @param string $delimiter
	 *
	 * @return array|bool
	 */
	public static function csv_to_array( $filename = '', $delimiter = ',' ) {
		if ( ! file_exists( $filename ) || ! is_readable( $filename ) ) {
			return false;
		}

		$header     = null;
		$data       = [];
		$row_length = 0;
		if ( ( $handle = fopen( $filename, 'r' ) ) !== false ) {
			while ( ( $row = fgetcsv( $handle, null, $delimiter ) ) !== false ) {

				if ( ! $header ) {
					$header     = $row;
					$row_length = count( $row );
				} else {
					if ( $row_length != count( $row ) ) {
						return false;
					}
					$data[] = array_combine( $header, $row );
				}
			}
			fclose( $handle );
		}

		return $data;
	}

	/**
	 * Return post types
	 *
	 * @return array
	 */
	public static function get_post_types() {
		$post_types = [];
		foreach ( get_post_types( '', 'objects' ) as $post_type ) {
			$post_types[ $post_type->name ] = $post_type->label;
		}

		//Exclude Post Types: Media, Revisions, Nav Menu Items, Field Groups
		unset( $post_types['attachment'], $post_types['revision'], $post_types['nav_menu_item'], $post_types['acf'] );

		return $post_types;
	}

	/**
	 * Return ACF plugin custom fields for Post Type in param
	 *
	 * @param $post_type
	 *
	 * @return array
	 */
	public static function get_acf_fields( $post_type ) {
		if ( ! class_exists( 'acf_location' ) ) {
			return [];
		}
		$acf_group_ids = self::get_acf_groups( $post_type );
		$field_groups  = [];
		foreach ( $acf_group_ids as $id ) {
			$field_groups[ get_the_title( $id ) ] = apply_filters( 'acf/field_group/get_fields', array(), $id );
		}

		return $field_groups;
	}

	/**
	 * Return array with ACF plugin field groups Id's for Post Type in param
	 *
	 * @param $post_type
	 *
	 * @return array
	 */
	public static function get_acf_groups( $post_type ) {
		$options  = [ 'post_type' => $post_type ];
		$location = new acf_location();

		return $location->match_field_groups( [], $options );
	}

	/**
	 * Fill var self::$fields
	 */
	public static function get_all_fields() {
		$post_types = Bwt_Csv_Importer::get_post_types();
		foreach ( $post_types as $slug => $title ) {
			$fields                       = self::get_acf_fields( $slug );
			$fields['Standard WP fields'] = self::$default_fields;
			self::$fields[]               = [
				'slug'   => $slug,
				'title'  => $title,
				'fields' => $fields
			];
		}
	}

	/**
	 * Create posts
	 *
	 * @return bool
	 */
	public static function create_posts() {
		ignore_user_abort( true );
		update_option( 'bwt_cip_status', 'active' );

		if ( isset( $_POST['post_type'] ) && $_POST['post_type'] !== '' ) {
			$file      = CIP_PLUGIN_DIR . '/tmp/' . $_POST['csv_file'];
			self::$csv = self::csv_to_array( $file );

			if ( ! self::$csv ) {
				self::$messages = [ 'error' => __( 'File read error', 'bwt-csv-importer' ) ];

				return false;
			}

			$posts_data    = self::get_posts_data();
			$post_inserted = 0;
			$prev_percent  = 0;
			$posts_counter = 0;
			$curr_percent  = 0;
			$total_posts   = count( self::$csv );

			foreach ( $posts_data as $post_data ) {
				$post = [
					'import_id'             => isset( $post_data['import_id'] ) ? $post_data['import_id'] : ( isset( $post_data['def-import_id'] ) ? $post_data['def-import_id'] : '' ),
					'post_date'             => ( isset( $post_data['post_date'] ) && self::validate_date( $post_data['post_date'] ) ) ? $post_data['post_date'] : ( ( isset( $post_data['def-post_date'] ) && self::validate_date( $post_data['def-post_date'] ) ) ? $post_data['def-post_date'] : date( 'Y-m-d H:i:s' ) ),
					'post_date_gmt'         => isset( $post_data['post_date_gmt'] ) ? $post_data['post_date_gmt'] : ( isset( $post_data['def-post_date_gmt'] ) ? $post_data['def-post_date_gmt'] : '' ),
					'post_content_filtered' => isset( $post_data['post_content_filtered'] ) ? $post_data['post_content_filtered'] : ( isset( $post_data['def-post_content_filtered'] ) ? $post_data['def-post_content_filtered'] : '' ),
					'post_excerpt'          => isset( $post_data['post_excerpt'] ) ? $post_data['post_excerpt'] : ( isset( $post_data['def-post_excerpt'] ) ? $post_data['def-post_excerpt'] : '' ),
					'post_status'           => ( isset( $post_data['post_status'] ) && in_array( $post_data['post_status'],
							[
								'draft',
								'publish',
								'pending',
								'future',
								'trash',
								'private'
							],
							true ) ) ? $post_data['post_status'] : ( ( isset( $post_data['def-post_status'] ) && in_array( $post_data['def-post_status'],
							[
								'draft',
								'publish',
								'pending',
								'future',
								'trash',
								'private'
							],
							true ) ) ? $post_data['def-post_status'] : 'publish' ),
					'ping_status'           => ( isset( $post_data['ping_status'] ) && in_array( $post_data['ping_status'],
							[
								'closed',
								'open'
							] ) ) ? $post_data['ping_status'] : ( ( isset( $post_data['def-ping_status'] ) && in_array( $post_data['def-ping_status'],
							[
								'closed',
								'open'
							] ) ) ? $post_data['def-ping_status'] : '' ),
					'post_password'         => isset( $post_data['post_password'] ) ? $post_data['post_password'] : ( isset( $post_data['def-post_password'] ) ? $post_data['def-post_password'] : '' ),
					'post_name'             => isset( $post_data['post_name'] ) ? $post_data['post_name'] : ( isset( $post_data['def-post_name'] ) ? $post_data['def-post_name'] : '' ),
					'to_ping'               => isset( $post_data['to_ping'] ) ? $post_data['to_ping'] : ( isset( $post_data['def-to_ping'] ) ? $post_data['def-to_ping'] : '' ),
					'pinged'                => isset( $post_data['pinged'] ) ? $post_data['pinged'] : ( isset( $post_data['def-pinged'] ) ? $post_data['def-pinged'] : '' ),
					'post_parent'           => isset( $post_data['post_parent'] ) ? $post_data['post_parent'] : ( isset( $post_data['def-post_parent'] ) ? $post_data['def-post_parent'] : 0 ),
					'menu_order'            => isset( $post_data['menu_order'] ) ? $post_data['menu_order'] : ( isset( $post_data['def-menu_order'] ) ? $post_data['def-menu_order'] : 0 ),
					'guid'                  => isset( $post_data['guid'] ) ? $post_data['guid'] : ( isset( $post_data['def-guid'] ) ? $post_data['def-guid'] : '' ),
					'tax_input'             => isset( $post_data['tax_input'] ) ? $post_data['tax_input'] : ( isset( $post_data['def-tax_input'] ) ? $post_data['def-tax_input'] : [] ),
					'meta_input'            => isset( $post_data['meta_input'] ) ? $post_data['meta_input'] : ( isset( $post_data['def-meta_input'] ) ? $post_data['def-meta_input'] : [] ),
					'post_content'          => isset( $post_data['post_content'] ) ? $post_data['post_content'] : ( isset( $post_data['def-post_content'] ) ? $post_data['def-post_content'] : '' ),
					'post_title'            => isset( $post_data['post_title'] ) ? $post_data['post_title'] : ( isset( $post_data['def-post_title'] ) ? $post_data['def-post_title'] : '' ),
					'comment_status'        => ( isset( $post_data['comment_status'] ) && in_array( $post_data['comment_status'],
							[
								'closed',
								'open'
							] ) ) ? $post_data['comment_status'] : ( ( isset( $post_data['def-comment_status'] ) && in_array( $post_data['def-comment_status'],
							[
								'closed',
								'open'
							] ) ) ? $post_data['def-comment_status'] : '' ),
					'post_category'         => isset( $post_data['post_category'] ) ? $post_data['post_category'] : ( isset( $post_data['def-post_category'] ) ? $post_data['def-post_category'] : [] ),
					'post_type'             => $_POST['post_type']
				];

				$post_id = @wp_insert_post( $post );
				if ( $post_id ) {
					$post_inserted ++;
					$featured_image = isset( $post_data['featured_image'] ) ? $post_data['featured_image'] : ( isset( $post_data['def-featured_image'] ) ? $post_data['def-featured_image'] : false );
					if ( $featured_image ) {
						self::set_featured_image( $post_id, $featured_image );
					}
				}

				$posts_counter ++;
				$curr_percent = intval( $posts_counter / $total_posts * 100 );
				if ( ( $curr_percent - $prev_percent ) > 1 ) {
					$progress_options = [
						'curr_percent'  => $curr_percent,
						'total_posts'   => $total_posts,
						'post_inserted' => $post_inserted
					];
					update_option( 'bwt_cip_progress', json_encode( $progress_options ) );
					self::ob_ignore( $curr_percent, true );
					$prev_percent = $curr_percent;
				}

			}
			self::$progress_data = [ 'total_posts' => $total_posts, 'post_inserted' => $post_inserted ];
			@unlink( $file );

			$progress_options = [
				'curr_percent'  => $curr_percent,
				'total_posts'   => $total_posts,
				'post_inserted' => $post_inserted
			];
			update_option( 'bwt_cip_progress', json_encode( $progress_options ) );

			return true;
		}

		return false;
	}

	/**
	 * Returns array with data from CSV
	 *
	 * @return array
	 */
	public static function get_posts_data() {
		$post_type = $_POST['post_type'];
		$data      = $_POST[ $post_type ];

		$posts_data = [];
		for ( $i = 0; $i < count( self::$csv ); $i ++ ) {
			$posts_data = self::get_posts_data_main( $data, $i, $posts_data );
			$posts_data = self::get_posts_data_default( $data, $i, $posts_data );
			$posts_data = self::get_posts_data_acf( $data, $i, $posts_data, $post_type );
		}

		return $posts_data;
	}

	/**
	 * Returns array with main fields data from CSV
	 *
	 * @param $data
	 * @param $i
	 * @param $posts_data
	 *
	 * @return mixed
	 */
	public static function get_posts_data_main( $data, $i, $posts_data ) {
		foreach ( self::$main_fields as $field ) {
			if ( isset( $data[ $field['name'] ] ) && $data[ $field['name'] ] !== '' ) {
				if ( $field['name'] == 'post_category' ) {
					$posts_data[ $i ][ $field['name'] ] = self::create_categories( self::$csv[ $i ][ $data[ $field['name'] ] ] );
				} else {
					$posts_data[ $i ][ $field['name'] ] = self::$csv[ $i ][ $data[ $field['name'] ] ];
				}
			}
			if ( isset( $data[ 'def-' . $field['name'] ] ) && $data[ 'def-' . $field['name'] ] !== '' ) {
				if ( $field['name'] == 'post_category' ) {
					$posts_data[ $i ][ 'def-' . $field['name'] ] = self::create_categories( $data[ 'def-' . $field['name'] ] );
				} else {
					$posts_data[ $i ][ 'def-' . $field['name'] ] = $data[ 'def-' . $field['name'] ];
				}
			}
		}

		return $posts_data;
	}

	/**
	 * Returns array with default fields data from CSV
	 *
	 * @param $data
	 * @param $i
	 * @param $posts_data
	 *
	 * @return mixed
	 */
	public static function get_posts_data_default( $data, $i, $posts_data ) {
		foreach ( self::$default_fields as $field ) {
			if ( isset( $data[ $field['name'] ] ) && $data[ $field['name'] ] !== '' ) {
				if ( $field['name'] == 'meta_input' || $field['name'] == 'tax_input' ) {
					$meta_array = @json_decode( stripcslashes( self::$csv[ $i ][ $data[ $field['name'] ] ] ) );
					if ( $meta_array ) {
						$posts_data[ $i ][ $field['name'] ] = $meta_array;
					}
				} else {
					$posts_data[ $i ][ $field['name'] ] = self::$csv[ $i ][ $data[ $field['name'] ] ];
				}
			}
			if ( isset( $data[ 'def-' . $field['name'] ] ) && $data[ 'def-' . $field['name'] ] !== '' ) {
				if ( $field['name'] == 'meta_input' || $field['name'] == 'tax_input' ) {
					$meta_array = @json_decode( stripcslashes( $data[ 'def-' . $field['name'] ] ), true );
					if ( $meta_array ) {
						$posts_data[ $i ][ 'def-' . $field['name'] ] = $meta_array;
					}
				} else {
					$posts_data[ $i ][ 'def-' . $field['name'] ] = $data[ 'def-' . $field['name'] ];
				}
			}
		}

		return $posts_data;
	}

	/**
	 * Returns array with ACF fields data from CSV
	 *
	 * @param $data
	 * @param $i
	 * @param $posts_data
	 * @param $post_type
	 *
	 * @return mixed
	 */
	public static function get_posts_data_acf( $data, $i, $posts_data, $post_type ) {
		foreach ( self::get_acf_fields( $post_type ) as $acf_groups ) {
			foreach ( $acf_groups as $acf_field ) {
				if ( isset( $data[ $acf_field['key'] ] ) && $data[ $acf_field['key'] ] !== '' ) {
					$posts_data[ $i ]['meta_input'][ '_' . $acf_field['_name'] ] = $acf_field['key'];
					$posts_data[ $i ]['meta_input'][ $acf_field['name'] ]        = self::$csv[ $i ][ $data[ $acf_field['key'] ] ];
				}
				if ( isset( $data[ 'def-' . $acf_field['key'] ] ) && $data[ 'def-' . $acf_field['key'] ] !== '' ) {
					$posts_data[ $i ]['def-meta_input'][ '_' . $acf_field['_name'] ] = $acf_field['key'];
					$posts_data[ $i ]['def-meta_input'][ $acf_field['name'] ]        = $data[ 'def-' . $acf_field['key'] ];
				}
			}
		}

		return $posts_data;
	}

	/**
	 * Create categories and returns array with category id's
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function create_categories( $data ) {
		$category_ids = [];
		foreach ( explode( ',', $data ) as $category ) {
			$category_ids[] = wp_create_category( $category );
		}

		return $category_ids;
	}

	/**
	 * Return true if $date match date format $format
	 *
	 * @param $date
	 * @param string $format
	 *
	 * @return bool
	 */
	public static function validate_date( $date, $format = 'Y-m-d H:i:s' ) {
		$d = DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) == $date;
	}

	/**
	 * Set Featured Image
	 *
	 * @param $post_id
	 * @param $file
	 */
	public static function set_featured_image( $post_id, $file ) {
		$tmp = download_url( $file );
		preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $file, $matches );
		$file_array['name']     = basename( $matches[0] );
		$file_array['tmp_name'] = $tmp;
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}
		$id = media_handle_sideload( $file_array, $post_id );
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
		} else {
			update_post_meta( $post_id, '_thumbnail_id', $id );
		}
		@unlink( $file_array['tmp_name'] );
	}

	/**
	 * Send the contents of the output buffer
	 *
	 * @param $data
	 * @param bool $flush
	 *
	 * @return int
	 */
	public static function ob_ignore( $data, $flush = false ) {
		$ob = array();
		while ( ob_get_level() ) {
			array_unshift( $ob, ob_get_contents() );
			ob_end_clean();
		}
		echo str_pad( $data, 4096, ' ', STR_PAD_RIGHT );
		if ( $flush ) {
			flush();
			@ob_flush();
		}
		foreach ( $ob as $ob_data ) {
			ob_start();
			echo $ob_data;
		}

		return count( $ob );
	}
}
