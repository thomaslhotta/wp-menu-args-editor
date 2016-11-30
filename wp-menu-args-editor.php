<?php

/**
 * Allows editing menu args from the customizer
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           WP_Menu_Args_Editor
 *
 * @wordpress-plugin
 * Plugin Name:       WP Menu-Args Editor
 * Plugin URI:        http://github.com/thomaslhotta/wp-menu-args-editor
 * Description:       Allows editing menu args from the customizer as well as adding a "only_current_branch" option
 * Version:           1.0.0
 * Author:            Your Name or Your Company
 * Author URI:        http://github.com/thomaslhotta
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       WP_Menu_Args_Editor
 */

class WP_Menu_Args_Editor {
	protected static $instance;

	/**
	 * Returns the plugin instance
	 *
	 * @return WP_Menu_Args_Editor
	 */
	public static function get_instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
		add_action( 'customize_register', array( $this, 'customize_register' ), 9999 );

		add_filter( 'wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ) );
		add_filter( 'wp_nav_menu_objects', array( $this, 'wp_nav_menu_objects' ), 10 , 2 );
	}

	/**
	 * Registers inputs in the customizer
	 *
	 * @param WP_Customize_Manager $manager
	 */
	public function customize_register( WP_Customize_Manager $manager ) {
		foreach ( get_registered_nav_menus() as $id => $name ) {
			$manager->add_setting(
				$id . '_args',
				array(
				)
			);

			$manager->add_control(
				$id . '_args',
				array(
					'label'   => $name,
					'type'    => 'textarea',
					'section' => 'menu_locations',
				)
			);
		}
	}

	/**
	 * Merges the menu args with any configured ones
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function wp_nav_menu_args( array $args ) {
		if ( empty( $args['theme_location'] ) ) {
			return $args;
		}

		$overwrites = json_decode( get_theme_mod( $args['theme_location'] . '_args' ), true );

		if ( is_array( $overwrites ) ) {
			$args = array_merge( $args, $overwrites );
		}

		return $args;
	}

	/**
	 * Adds the "only_current_branch" feature
	 *
	 * @param $objects
	 * @param $args
	 *
	 * @return array
	 */
	public function wp_nav_menu_objects( $objects, $args ) {
		if ( empty( $args->only_current_branch ) ) {
			return $objects;
		}

		$new_root_id = null;
		foreach ( $objects as $object ) {
			if ( in_array( 'current-menu-item', $object->classes ) ) {
				$new_root_id = intval( $object->menu_item_parent );
			}
		}


		if ( empty( $new_root_id ) ) {
			return $objects;
		}

		$parent_ids = array( $new_root_id );
		$return     = array();
		foreach ( $objects as $object ) {
			if ( in_array( $object->menu_item_parent, $parent_ids ) ) {
				$parent_ids[] = intval( $object->ID );
				$return[] = $object;
			}
		}

		return $return;
	}
}

WP_Menu_Args_Editor::get_instance();