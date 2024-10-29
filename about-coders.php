<?php
/**
 * Plugin Name:     About coders
 * Plugin URI:      Estimates the work of programmers who contribute to develop your Wordpress website plugins and themes.
 * Description:     This is a dashboard widget that uses AJAX to avoid load time excess. It displays the total and detailed estimations of bytes produced by each developers plugins and themes.
 * Author:          Romain GUILLAUME
 * Author URI:      www.blgg.fr
 * Text Domain:     about-coders
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         About_Coders
 */
 
new About_Coders;
class About_Coders{
	function __construct(){
		add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );
		add_action( 'wp_dashboard_setup', [$this, 'wp_dashboard_setup'] );
		add_action( 'wp_ajax_mon_action', [$this, 'callback_function'] );
		add_action( 'wp_ajax_nopriv_mon_action', [$this, 'callback_function'] );
	}
		
	/**
	 * admin_enqueue_scripts 'about-coders' with AJAX
	 *
	 * @return void
	 */
	function admin_enqueue_scripts() {
		wp_register_script( 'about-coders', plugins_url( '/js/about-coders-script.js', __FILE__ ), array( 'jquery' ), null, false );
		wp_enqueue_script( 'about-coders' );
		wp_localize_script( 'about-coders', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
	}
		
	/**
	 * wp_dashboard_setup sets the dashboard widget
	 *
	 * @return void
	 */
	function wp_dashboard_setup() {
		wp_add_dashboard_widget(
			'about_coders_dashboard_widget',                   	       // Widget slug.
			__( 'About coders: plugin and themes ', 'about-coders' ), // Title.
			[$this, 'about_coders_dashboard_widget_render']
		); 
	}	
	/**
	 * about_coders_dashboard_widget_render displays the empty box and calls the script
	 *
	 * @return void
	 */
	function about_coders_dashboard_widget_render() {
		echo '<div class="display_about_coders"></div>';
		echo '<script>filter_the_sizes()</script>';
	}	
	/**
	 * callback_function AJAX called function that displays the size of each author's plugins & themes 
	 *
	 * @return void AJAX ends the function passing die()
	 */
	function callback_function(){
		$all_themes = wp_get_themes();
		$authors = [];
		foreach( $all_themes as $key => $val ){
			$theme = get_theme_root() . '/' . $key;
			$obj_filter = $this->GetThe_Size( $theme );
			$Author = wp_strip_all_tags( $val->Author );
			$authors[$Author]['themes'][$key] = $obj_filter;
			$authors[$Author]['TOTAL_filter'] += $obj_filter;
			$authors[$Author]['TOTAL_filter_KB'] += ( $obj_filter / 1000 );
			$authors[$Author]['TOTAL_filter_MB'] += ( $obj_filter / 1000000 );
		}
		$all_plugins = get_plugins();
		foreach( $all_plugins as $key => $val ){
			$dir = dirname( $key );
			$Author = $val['Author'];
			$plugin = WP_PLUGIN_DIR . '/' . $dir;
			$obj_filter = $this->GetThe_Size( $plugin );
			$authors[$Author]['plugins'][$key] = $obj_filter;
			$authors[$Author]['TOTAL_filter'] += $obj_filter;
			$authors[$Author]['TOTAL_filter_KB'] += ( $obj_filter / 1000 );
			$authors[$Author]['TOTAL_filter_MB'] += ( $obj_filter / 1000000 );
		}
		$render = "";
		$render .= "";
		foreach ( $authors as $key => $val ){
			$render .= "<details>";
			$MB = round( $val['TOTAL_filter_MB'], 2 );
			$MB_unit = __( 'MB', 'about-coders' );
			$render .= "<summary>$key<span style='float: right'>$MB $MB_unit</span></summary>";
			$values = ['themes' => 'Themes', 'plugins' => 'Extensions'];
			foreach( $values as $type => $type_name ){
				if( $val[$type] ) {
					$render .= "<b>$type_name</b><br>";
					foreach ( $val[$type] as $name => $size ){
						$size = number_format( $size, 0, '', ' ' );
						$name = basename( $name );
						$B_unit = __( 'B', 'about-coders' );
						$render .= "<i>$name</i><span style='float: right'>$size $B_unit</span><br>";
					}
				}
			}
			$render .= "</details>";
		}
		echo $render;
		die();
	}
		
	/**
	 * GetThe_Size will compute the size of .php .js and .css files.
	 *
	 * @param  mixed $path of the main directory of plugin or theme
	 * @return int $bytestotal is computed by iteration in subdirectories
	 */
	function GetThe_Size( $path ){
		$bytestotal = 0;
		$path = realpath( $path );
		$iterator = new RecursiveDirectoryIterator( $path, FilesystemIterator::KEY_AS_PATHNAME );
		$recursiveIterator = new RecursiveIteratorIterator( $iterator );
		 
		foreach($recursiveIterator as $key => $file) {
			if( strpos( $key, 'node_modules' ) === false && ( strpos( $key, '.js' ) !== false || strpos( $key, '.php' ) !== false || strpos( $key, '.css' ) !== false ) )
				$bytestotal += $file->getSize();
				
		}
		return $bytestotal;
	}
}