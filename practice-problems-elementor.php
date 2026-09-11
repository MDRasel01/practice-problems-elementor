<?php
/**
 * Plugin Name: Practice Problems for Elementor
 * Plugin URI:  https://example.com/practice-problems-elementor
 * Description: A production-grade, 100% customizable Practice Problems widget with interactive filtering, step-by-step solution accordions, multi-style pagination, and dynamic data support.
 * Version:     1.0.2
 * Author:      Senior Elementor Plugin Engineer
 * Author URI:  https://example.com
 * Text Domain: practice-problems-el
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Elementor tested up to: 3.24.0
 * Elementor Pro tested up to: 3.24.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PRACTICE_PROBLEMS_VERSION', '1.0.2' );
define( 'PRACTICE_PROBLEMS_FILE', __FILE__ );
define( 'PRACTICE_PROBLEMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRACTICE_PROBLEMS_URL', plugin_dir_url( __FILE__ ) );
define( 'PRACTICE_PROBLEMS_MINIMUM_ELEMENTOR_VERSION', '3.5.0' );
define( 'PRACTICE_PROBLEMS_MINIMUM_PHP_VERSION', '7.4' );

/**
 * Check requirements and initialize the plugin.
 */
if ( ! function_exists( 'practice_problems_init' ) ) {
	function practice_problems_init() {
		// Check PHP version.
		if ( version_compare( PHP_VERSION, PRACTICE_PROBLEMS_MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', 'practice_problems_fail_php_version' );
			return;
		}

		// Check if Elementor is installed and activated.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', 'practice_problems_fail_load_elementor' );
			return;
		}

		// Check Elementor version.
		if ( version_compare( ELEMENTOR_VERSION, PRACTICE_PROBLEMS_MINIMUM_ELEMENTOR_VERSION, '<' ) ) {
			add_action( 'admin_notices', 'practice_problems_fail_elementor_version' );
			return;
		}

		// Include core plugin classes.
		require_once PRACTICE_PROBLEMS_PATH . 'includes/class-cpt.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/class-notes-cpt.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/admin/class-notes-meta-boxes.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/class-course-cpt.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/admin/class-course-meta-boxes.php';
		require_once PRACTICE_PROBLEMS_PATH . 'includes/class-plugin.php';

		// Run Plugin.
		\PracticeProblems\Plugin::instance();
	}
	add_action( 'plugins_loaded', 'practice_problems_init' );
}

/**
 * Check if Elementor plugin file exists.
 */
if ( ! function_exists( 'practice_problems_is_elementor_installed' ) ) {
	function practice_problems_is_elementor_installed() {
		$file_path = 'elementor/elementor.php';
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $file_path ] );
	}
}

/**
 * Admin notice for missing Elementor.
 */
if ( ! function_exists( 'practice_problems_fail_load_elementor' ) ) {
	function practice_problems_fail_load_elementor() {
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'elementor/elementor.php';
		if ( practice_problems_is_elementor_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
			$message = sprintf(
				/* translators: 1: Plugin name 2: Elementor 3: Activation URL */
				esc_html__( '"%1$s" requires "%2$s" to be activated. %3$sActivate Elementor%4$s', 'practice-problems-el' ),
				'<strong>' . esc_html__( 'Practice Problems for Elementor', 'practice-problems-el' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'practice-problems-el' ) . '</strong>',
				'<a href="' . esc_url( $activation_url ) . '">',
				'</a>'
			);
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}
			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
			$message = sprintf(
				/* translators: 1: Plugin name 2: Elementor 3: Install URL */
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated. %3$sInstall Elementor%4$s', 'practice-problems-el' ),
				'<strong>' . esc_html__( 'Practice Problems for Elementor', 'practice-problems-el' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'practice-problems-el' ) . '</strong>',
				'<a href="' . esc_url( $install_url ) . '">',
				'</a>'
			);
		}

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}

/**
 * Admin notice for incompatible Elementor version.
 */
if ( ! function_exists( 'practice_problems_fail_elementor_version' ) ) {
	function practice_problems_fail_elementor_version() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'practice-problems-el' ),
			'<strong>' . esc_html__( 'Practice Problems for Elementor', 'practice-problems-el' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'practice-problems-el' ) . '</strong>',
			PRACTICE_PROBLEMS_MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}

/**
 * Admin notice for incompatible PHP version.
 */
if ( ! function_exists( 'practice_problems_fail_php_version' ) ) {
	function practice_problems_fail_php_version() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'practice-problems-el' ),
			'<strong>' . esc_html__( 'Practice Problems for Elementor', 'practice-problems-el' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'practice-problems-el' ) . '</strong>',
			PRACTICE_PROBLEMS_MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', wp_kses_post( $message ) );
	}
}
