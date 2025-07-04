<?php
/**
 * RUP EDD Loader
 *
 * A reusable drop-in updater and license manager for EDD Software Licensing
 * 
 * How to Use:
 * ----------
 * 1. Place this file in your plugin's `inc/` folder.
 * 2. Include it from your main plugin file.
 * 3. Instantiate the class with your plugin-specific arguments (see examples below).
 * 
 * Required: You must include `EDD_SL_Plugin_Updater.php` (from EDD) in the same `inc/` folder.
 *
 * Constructor Arguments:
 * ----------------------
 * 'plugin_file'        => __FILE__,                  // Full path to main plugin file
 * 'plugin_version'     => '1.0.0',                   // Current version of your plugin
 * 'store_url'          => 'https://yourstore.com',   // Your EDD store URL
 * 'item_id'            => 1234,                      // Product ID in EDD
 * 'item_name'          => 'Plugin Name',             // Product name in EDD
 * 'author'             => 'Your Company',            // Author shown in updater
 * 'option_key'         => 'my_plugin_license_key',   // Option key for storing license (must be unique per plugin)
 * 'menu_slug'          => 'my-plugin-license',       // Slug for the admin license page
 * 'menu_type'          => 'settings',                // 'top', 'submenu', or 'settings'
 * 'parent_slug'        => 'tools.php',               // Only used if menu_type is 'submenu'
 * 'show_license_page'  => true,                      // OPTIONAL: set false to disable internal license admin page
 * 'page_title'         => 'My Plugin Settings',      // OPTIONAL: custom heading for admin license page
 * 'menu_title'         => 'License',                 // OPTIONAL: title for the menu item
 *
 * Render License Field Manually (if show_license_page is false or you use a custom settings page):
 * -----------------------------------------------------------------------------------------------
 * $loader = new RUP_EDD_Loader([...]);
 * $loader->rup_edd_loader_render_license_field();
 * 
 *
 * Bootstrap Examples:
 * -------------------
 *
 * 1. Add license page under “Settings” menu:
 *
 * new RUP_EDD_Loader([
 *     'plugin_file'    => __FILE__,
 *     'plugin_version' => '1.0.0',
 *     'store_url'      => 'https://yourstore.com',
 *     'item_id'        => 1234,
 *     'item_name'      => 'My Plugin',
 *     'author'         => 'Your Company',
 * ]);
 *
 * 2. Add license page as a top-level admin menu:
 *
 * new RUP_EDD_Loader([
 *     'plugin_file'    => __FILE__,
 *     'plugin_version' => '1.0.0',
 *     'store_url'      => 'https://yourstore.com',
 *     'item_id'        => 1234,
 *     'item_name'      => 'My Plugin',
 *     'author'         => 'Your Company',
 *     'menu_type'      => 'top',
 *     'menu_slug'      => 'my-plugin-license',
 *     'menu_title'     => 'My Plugin License',
 * ]);
 *
 * 3. Add license page under another plugin's menu:
 *
 * new RUP_EDD_Loader([
 *     'plugin_file'    => __FILE__,
 *     'plugin_version' => '1.0.0',
 *     'store_url'      => 'https://yourstore.com',
 *     'item_id'        => 1234,
 *     'item_name'      => 'My Plugin',
 *     'author'         => 'Your Company',
 *     'menu_type'      => 'submenu',
 *     'parent_slug'    => 'my-main-menu',
 *     'menu_slug'      => 'my-plugin-license',
 * ]);
 *
 * 4. Disable license page UI completely and render manually:
 *
 * $loader = new RUP_EDD_Loader([
 *     'plugin_file'        => __FILE__,
 *     'plugin_version'     => '1.0.0',
 *     'store_url'          => 'https://yourstore.com',
 *     'item_id'            => 1234,
 *     'item_name'          => 'My Plugin',
 *     'author'             => 'Your Company',
 *     'show_license_page'  => false,
 * ]);
 * echo '<div class="my-custom-settings-row">';
 * $loader->rup_edd_loader_render_license_field();
 * echo '</div>';
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'RUP_EDD_Loader' ) ) {

	class RUP_EDD_Loader {

		private $plugin_file, $plugin_version, $store_url, $item_id, $item_name, $author;
		private $option_key, $menu_slug, $menu_type, $parent_slug, $show_license_page, $page_title, $menu_title;

		public function __construct( $args = [] ) {

			$defaults = [
				'plugin_file'       => '',
				'plugin_version'    => '',
				'store_url'         => '',
				'item_id'           => '',
				'item_name'         => '',
				'author'            => '',
				'option_key'        => 'rup_edd_license_key',
				'menu_slug'         => 'rup-edd-license',
				'menu_type'         => 'settings',
				'parent_slug'       => '',
				'show_license_page' => true,
				'page_title'        => '',
				'menu_title'        => 'License',
			];

			$args = wp_parse_args( $args, $defaults );

			foreach ( $args as $key => $value ) {
				$this->$key = $value;
			}

			add_action( 'admin_init', [ $this, 'rup_edd_loader_plugin_updater' ] );
			add_action( 'admin_init', [ $this, 'rup_edd_loader_register_option' ] );
			add_action( 'admin_init', [ $this, 'rup_edd_loader_activate_license' ] );
			add_action( 'admin_init', [ $this, 'rup_edd_loader_deactivate_license' ] );
			add_action( 'admin_notices', [ $this, 'rup_edd_loader_admin_notices' ] );

			if ( $this->show_license_page ) {
				add_action( 'admin_menu', [ $this, 'rup_edd_loader_license_menu' ] );
			}
		}

		public function rup_edd_loader_plugin_updater() {
			if ( ! current_user_can( 'manage_options' ) && ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) return;
			if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'EDD_SL_Plugin_Updater.php';
			}

			$license_key = trim( get_option( $this->option_key ) );

			new EDD_SL_Plugin_Updater( $this->store_url, $this->plugin_file, [
				'version'   => $this->plugin_version,
				'license'   => $license_key,
				'item_id'   => $this->item_id,
				'author'    => $this->author,
				'beta'      => false,
			]);
		}

		public function rup_edd_loader_license_menu() {
			$page_title = $this->page_title ?: $this->item_name . ' License';
			switch ( $this->menu_type ) {
				case 'top':
					add_menu_page( $page_title, $this->menu_title, 'manage_options', $this->menu_slug, [ $this, 'rup_edd_loader_license_page' ] );
					break;
				case 'submenu':
					$parent = ! empty( $this->parent_slug ) ? $this->parent_slug : 'tools.php';
					add_submenu_page( $parent, $page_title, $this->menu_title, 'manage_options', $this->menu_slug, [ $this, 'rup_edd_loader_license_page' ] );
					break;
				default:
					add_options_page( $page_title, $this->menu_title, 'manage_options', $this->menu_slug, [ $this, 'rup_edd_loader_license_page' ] );
			}
		}

		public function rup_edd_loader_license_page() {
			$title = $this->page_title ? $this->page_title : $this->item_name . ' License';
			echo '<div class="wrap"><h1>' . esc_html( $title ) . '</h1><form method="post" action="options.php">';
			settings_fields( $this->option_key );
			echo '<table class="form-table"><tr><th scope="row">License Key</th><td>';
			$this->rup_edd_loader_render_license_field();
			echo '</td></tr></table>';
			submit_button();
			echo '</form></div>';
		}

		public function rup_edd_loader_render_license_field() {
			$license = get_option( $this->option_key );
			$status  = get_option( "{$this->option_key}_status" );

			printf( '<input type="text" class="regular-text" name="%1$s" value="%2$s" />',
				esc_attr( $this->option_key ), esc_attr( $license )
			);

			$button_name = ( 'valid' === $status ) ? 'edd_license_deactivate' : 'edd_license_activate';
			$button_label = ( 'valid' === $status ) ? __( 'Deactivate License' ) : __( 'Activate License' );

			wp_nonce_field( 'rup_edd_loader_nonce', 'rup_edd_loader_nonce' );
			echo '<p><input type="submit" class="button" name="' . esc_attr( $button_name ) . '" value="' . esc_attr( $button_label ) . '" /></p>';

			if ( $status ) {
				$label = ( 'valid' === $status ) ? '✔️ Valid' : '❌ Invalid';
				echo '<p>Status: <strong>' . $label . '</strong></p>';
			}
		}

		public function rup_edd_loader_register_option() {
			register_setting( $this->option_key, $this->option_key, [ $this, 'rup_edd_loader_sanitize_license' ] );
		}

		public function rup_edd_loader_sanitize_license( $new ) {
			$old = get_option( $this->option_key );
			if ( $old && $old !== $new ) delete_option( "{$this->option_key}_status" );
			return sanitize_text_field( $new );
		}

		public function rup_edd_loader_activate_license() {
			if ( ! isset( $_POST['edd_license_activate'] ) || ! check_admin_referer( 'rup_edd_loader_nonce', 'rup_edd_loader_nonce' ) ) return;
			$license = trim( get_option( $this->option_key ) );

			$response = wp_remote_post( $this->store_url, [
				'timeout' => 15,
				'sslverify' => false,
				'body' => [
					'edd_action' => 'activate_license',
					'license' => $license,
					'item_id' => $this->item_id,
					'item_name' => rawurlencode( $this->item_name ),
					'url' => home_url(),
				],
			] );

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( ! $license_data || 'valid' !== $license_data->license ) {
				$this->rup_edd_loader_redirect_with_message( __( 'License activation failed.' ) );
			}
			update_option( "{$this->option_key}_status", $license_data->license );
			wp_safe_redirect( admin_url( 'admin.php?page=' . $this->menu_slug ) );
			exit;
		}

		public function rup_edd_loader_deactivate_license() {
			if ( ! isset( $_POST['edd_license_deactivate'] ) || ! check_admin_referer( 'rup_edd_loader_nonce', 'rup_edd_loader_nonce' ) ) return;
			$license = trim( get_option( $this->option_key ) );

			$response = wp_remote_post( $this->store_url, [
				'timeout' => 15,
				'sslverify' => false,
				'body' => [
					'edd_action' => 'deactivate_license',
					'license' => $license,
					'item_id' => $this->item_id,
					'item_name' => rawurlencode( $this->item_name ),
					'url' => home_url(),
				],
			] );

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			if ( 'deactivated' === $license_data->license ) {
				delete_option( "{$this->option_key}_status" );
			}
			wp_safe_redirect( admin_url( 'admin.php?page=' . $this->menu_slug ) );
			exit;
		}

		private function rup_edd_loader_redirect_with_message( $message ) {
			$redirect = add_query_arg( [
				'page' => $this->menu_slug,
				'sl_activation' => 'false',
				'message' => rawurlencode( $message ),
			], admin_url( 'admin.php' ) );
			wp_safe_redirect( $redirect );
			exit;
		}

		public function rup_edd_loader_admin_notices() {
			if ( isset( $_GET['sl_activation'], $_GET['message'] ) ) {
				echo '<div class="error"><p>' . esc_html( urldecode( $_GET['message'] ) ) . '</p></div>';
			}
		}
	}
}
