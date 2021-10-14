<?php

/**
 * Plugin Name: Auto SEO.
 * Plugin URI: The home page of the plugin, which should be a unique URL, preferably on your own website. This must be unique to your plugin. You cannot use a WordPress.org URL here.
 * Description: Automatically adds SEO tags to <head>. Does not display any field inputs in WordPress Editor. name="description" can be edited through post excerpts and taxonomy descriptions.
 * Version: 1.0.0
 * Requires at least: 5.8.1
 * Requires PHP: 7.4
 * Author: Carl David Brubaker
 * Author URI: The author’s website or profile on another website, such as WordPress.org.
 * License: GPLv2 (or later)
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: CDB_2021_AUTO_SEO
 * Domain Path: /languages    The domain path lets WordPress know where to find the translations. More information can be found in the Domain Path section of the How to Internationalize your Plugin page.
 * Network: Whether the plugin can only be activated network-wide. Can only be set to true, and should be left out when not needed.
 */

namespace cdb_2021_Auto_SEO;

defined( 'ABSPATH' ) or exit;

$prefix = 'CDB_2021_AUTO_SEO';

if ( ! defined( $prefix . '_PATH' ) ) {
	define( $prefix . '_PATH', plugin_dir_path(__FILE__) );
}

if ( ! defined($prefix . '_VERSION') ) {
	define( $prefix . '_VERSION', '0.0.1' );
}

if ( ! defined( $prefix . '_TEXT_DOMAIN') ) {
	define( $prefix . '_TEXT_DOMAIN', 'Text Domain' );
}

class CDB_2021_Auto_SEO
{	
	protected string $domain = CDB_2021_AUTO_SEO_TEXT_DOMAIN;
	protected string $version = CDB_2021_AUTO_SEO_VERSION;
	protected string $prefix;

	public function __construct( string $prefix )
	{
		$this->prefix = $prefix;

		add_action( 'init', array( $this, 'activateOrUpdate' ) );
		add_action( 'wp_head', array( $this, 'action_add_seo_meta_tags' ) );
	}
	
	/**
	 * Perform actions if wp_option(CDB_2021_AUTO_SEO_VERSION) does not match $this->version.
	 */
	public function activateOrUpdate()
	{
		if ( ! $this->pluginVersionOptionIsTheLatest() ) {
			// Do stuff if version has changed.
			
			flush_rewrite_rules();
		}
	}
	
	/**
	 * Checks stored version in the options table and updates as necessary.
	 * @return bool
	 * true if plugin version matches stored version.
	 * false if plugin version does not match or no option stored.
	 */
	public function pluginVersionOptionIsTheLatest()
	{
		$option_value = get_option( $this->prefix . '_VERSION' );
		
		if ( $option_value === $this->version ) {
			return true;
		}
		
		if ( $option_value === false ) {
			add_option( $this->prefix . '_VERSION', $this->version );
		} else {
			update_option( $this->prefix . '_VERSION', $this->version );
		}
		
		return false;
	}
	
	/**
	 * Adds SEO meta tags to head if current page. 
	 * 
	 * is_singular - will display entered excerpt or pull from page content.
	 * is_category, is_tag, is_author, is_post_type_archive, is_tax - will only display if description is set.
	 */
	public function action_add_seo_meta_tags()
	{
		if ( ! get_post_status() === 'public' ) {
			// return;
		}
		
		global $wp;
		$description = null;
		
		if ( is_singular() || is_front_page() ) {
			$description = explode( '&hellip;', get_the_excerpt() )[0] 
						   . '&hellip;';
		} else if ( is_category() || is_tag() || is_author()
		|| is_post_type_archive() || is_tax()) {
			$description = get_the_archive_description();
		}
		
		// Print meta description in head if $desc is not null.
		if ( $description ) {
			$description = strip_tags( $description );
			?>
			<meta name="description"
				  content="<?php echo esc_attr_e( $description ); ?>">
			<meta property="og:description"
				  content="<?php echo esc_attr_e( $description ); ?>">
			<?php
		}
		
		$title = is_front_page()
				 ? get_bloginfo( 'description' ) : get_the_title();
				 
		if ( $title ) {
			?>
			<meta property="og:title"
				  content="<?php echo esc_attr_e( $title ); ?>">
			<?php
		}
		?>
		<meta property="og:type" content="website">
		<meta property="og:url"
			  content="<?php echo esc_attr( home_url( $wp->request ) ); ?>">
		<meta property="og:site_name"
			  content="<?php echo esc_attr_e( get_bloginfo( 'name' ) ); ?>">
		<meta property="og:locale"
			  content="<?php echo esc_attr_e( get_bloginfo( 'language' ) ); ?>">
		<?php
	}
}

if ( class_exists( 'cdb_2021_Auto_SEO\CDB_2021_Auto_SEO' ) ) {
	new CDB_2021_Auto_SEO( $prefix );
}

if ( is_admin() ) {
	require_once CDB_2021_AUTO_SEO_PATH . 'admin.php';

	if ( class_exists(
		'cdb_2021_Auto_SEO\admin\CDB_2021_Auto_SEO_Admin' ) ) {
		new admin\CDB_2021_Auto_SEO_Admin();
	}
}
