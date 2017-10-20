<?php

/*
 * provides an update-safe place to put custom templates
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016  xnau webdesign
 * @license    GPL2
 * @version    0.2
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

class PDb_Custom_Templates extends PDb_Aux_Plugin {

  // plugin slug
  var $aux_plugin_name = 'pdb-custom-templates';
  // shortname for the plugin
  var $aux_plugin_shortname = 'pdbcustemp';
  
  /**
   * 
   * @param string $plugin_file
   */
  public function __construct( $plugin_file )
  {
    // no settings for this plugin
    $this->settings_API_status = false;
    
    parent::__construct( __CLASS__, $plugin_file );

    add_action( 'plugins_loaded', [$this, 'initialize' ] );
  }

  public function initialize()
  {
    $this->aux_plugin_title = __( 'Custom Templates', 'pdb_custom-templates' );
    
    add_action( 'pdb-template_select', array( $this, 'set_template' ) );
    
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
    
    $custom_template_uri = $this->template_base_path() . 'templates/' . $template;
    
    if ( is_file( $custom_template_uri ) ) {
      return $custom_template_uri;
    }
    
    return $template;
  }
  
  /**
   * provides the absolute base path to the custom templates directory
   * 
   * @filter pdb-custemp_templates_path
   * 
   * @return string
   */
  public function template_base_path()
  {
    $path = empty( $this->parent_path ) ? plugin_dir_path( $this->plugin_path ) : $this->parent_path;
    return apply_filters( 'pdb-custemp_templates_path', $path );
  }
}
