<?php

/*
 * provides an update-safe place to put custom templates
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016  xnau webdesign
 * @license    GPL2
 * @version    0.4
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

class PDb_Custom_Templates extends PDb_Aux_Plugin {

  // plugin slug
  var $aux_plugin_name = 'pdb-custom-templates2';
  // shortname for the plugin
  var $aux_plugin_shortname = 'pdbcustemp';

  /**
   * 
   * @param string $plugin_file
   */
  public function __construct( $plugin_file )
  {
    register_activation_hook( $plugin_file, array('PDb_Custom_Templates', 'on_activate') );

    // no settings for this plugin
    $this->settings_API_status = false;

    parent::__construct( __CLASS__, $plugin_file );

    add_action( 'plugins_loaded', [ $this, 'initialize'] );
  }

  public function initialize()
  {
    $this->aux_plugin_title = __( 'Custom Templates', 'pdb_custom-templates' );

    add_action( 'pdb-template_select', [ $this, 'set_template'] );
  }

  /**
   * sets the plugin template
   * 
   * use this plugin's default template if a template named "multisearch" has been 
   * named in the shortcode and a custom override is not present
   * 
   * @var string $template name of the currently selected template
   * @return string template path
   */
  public function set_template( $template )
  {
    $custom_template_path = $this->template_base_path() . trailingslashit( $this->template_directory() );

    error_log( __METHOD__ . ' template path: ' . $custom_template_path );

    if ( !is_dir( $custom_template_path ) ) {
      $this->make_template_directory( $custom_template_path );
    }

    // add the template filename
    $custom_template_path .= $template;

    if ( is_file( $custom_template_path ) ) {
      return $custom_template_path;
    }

    return $template;
  }

  /**
   * provides the absolute base path to the custom templates directory
   * 
   * this is normally the defined content directory
   * 
   * @filter pdb-custemp_templates_path
   * 
   * @return string
   */
  public function template_base_path()
  {
    return trailingslashit( apply_filters( 'pdb-custemp_templates_path', trailingslashit( WP_CONTENT_DIR ) ) );
  }

  /**
   * supplies a template directory name
   * 
   * this is to provide multisite support
   * 
   * @global wpdb $wpdb
   * @return string
   */
  private function template_directory()
  {
    $template_dirname = apply_filters( 'pdb-custemp_template_directory_name', Participants_Db::PLUGIN_NAME . '-templates' );
    global $wpdb;
    $current_blog = $wpdb->blogid;
    if ( $current_blog > 0 ) {
      $template_dirname .= '/blog-' . $current_blog;
    }
    return $template_dirname;
  }

  /**
   * attempt to create the uploads directory
   *
   * sets an error if it fails
   * 
   * @param string $dir the name of the new directory
   */
  private function make_template_directory( $dir = '' )
  {

    $dir = empty( $dir ) ? $this->template_base_path() . trailingslashit( $this->template_directory() ) : $dir;
    $savedmask = umask( 0 );
    $status = true;
    if ( mkdir( $dir, 0755, true ) === false ) {

      error_log( __METHOD__ . sprintf( __( ' The template directory (%s) could not be created.', 'participants-database' ), $dir ) );

      $status = false;
    }
    umask( $savedmask );
    return $status;
  }

  /**
   * set up template directory
   */
  public static function on_activate( $networkwide = false )
  {
    if ( $networkwide && function_exists( 'is_multisite' ) && is_multisite() ) {
      $this->network_activate();
    } else {
      $this->set_template('');
    }
  }

  /**
   * performs the activation on a network
   * 
   * @global wpdb $wpdb
   */
  private function network_activate()
  {
    global $wpdb;

    // store the currently active blog id
    $current_blog = $wpdb->blogid;

    // Get all blog ids
    $blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    
    // cycle through all the blogs, creating the directories as needed
    foreach ( $blogids as $blog_id ) {
      switch_to_blog( $blog_id );
      $this->set_template('');
    }
    
    // restore the original blog pointer
    switch_to_blog( $current_blog );
  }

}
