<?php

   /**
   * Plugin Name:   Divi Popup
   * Plugin URI:    https://github.com/ReQ78Pi
   * Description:   Simple and lightweight plugin for creating popups. Plugin requires Divi theme or plugin Divi Builder.
   * Version:       1.0.0
   * Author:        Alexey Ozhogin
   * Author URI:    https://github.com/ReQ78Pi
   * License:       GPL v2 or later
   * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
   * Text Domain:   divi-popup
   * Domain Path:   /languages
   */

   if ( !defined( 'ABSPATH' ) ) { 
   	exit; 
   }


  /*------------------------------------------------------------------------------------
  Plugin Define
  -------------------------------------------------------------------------------------*/

  define( 'DIVI_POPUP_PLUGIN_NAME', 'Divi Popup' );
  define( 'DIVI_POPUP_PLUGIN_SLUG', 'divi-popup' );
  define( 'DIVI_POPUP_PLUGIN_VERSION', '1.0.0' );
  define( 'DIVI_POPUP_PLUGIN_DIR', plugin_dir_path(__FILE__) );
  define( 'DIVI_POPUP_PLUGIN_URL', plugins_url('', __FILE__) );


  /*------------------------------------------------------------------------------------
  Checking active theme Divi or plugin Divi Builder
  -------------------------------------------------------------------------------------*/

  include_once( ABSPATH .'wp-admin/includes/plugin.php' );

  $active_divi_theme  = wp_get_theme();
  $active_divi_plugin = is_plugin_active( 'divi-builder/divi-builder.php' );

  if ( $active_divi_theme -> display( 'Name', FALSE ) === "Divi" || $active_divi_plugin ) {


  /*------------------------------------------------------------------------------------
  Load translate
  -------------------------------------------------------------------------------------*/

  add_action( 'plugins_loaded', 'divi_popup_lang' );

  function divi_popup_lang() {
    load_plugin_textdomain( DIVI_POPUP_PLUGIN_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
  }


  /*------------------------------------------------------------------------------------
  Enable Visual Builder for CPT divi-popup
  -------------------------------------------------------------------------------------*/

  function enable_builder_divi_popup( $post_types ) {
    $post_types[] = 'divi-popup';
    return $post_types;
  }

  add_filter( 'et_builder_post_types','enable_builder_divi_popup' );


  /*------------------------------------------------------------------------------------
  Register and enqueue admin style and script
  -------------------------------------------------------------------------------------*/

  function divi_popup_admin_scripts( $hook ) {
   global $post;
   if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
    if ( 'divi-popup' === $post->post_type ) {

      // Register and enqueue admin style
      wp_register_style( 'divi-popup-admin-style', DIVI_POPUP_PLUGIN_URL . '/assets/css/divi-popup-admin-style.min.css', DIVI_POPUP_PLUGIN_VERSION );
      wp_enqueue_style( 'divi-popup-admin-style' );

      // Enqueue color picker style/script
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_script( 'wp-color-picker' );

      // Register and enqueue alpha color picker script:
      // Link: https://github.com/kallookoo/wp-color-picker-alpha
      wp_enqueue_script( 'divi-popup-admin-script', DIVI_POPUP_PLUGIN_URL . '/assets/js/divi-popup-admin-js.min.js', array( 'wp-color-picker' ), DIVI_POPUP_PLUGIN_VERSION, true );
      wp_enqueue_script( 'divi-popup-admin-script' );

    }
  }
}

add_action( 'admin_enqueue_scripts', 'divi_popup_admin_scripts', 100 );


  /*------------------------------------------------------------------------------------
  Detect activated Divi Visual Builder
  -------------------------------------------------------------------------------------*/

  function is_activated_divi_visual_builder() {
   if ( isset( $_GET['et_fb'] ) ) {
     $divi_builder_enabled = htmlspecialchars( $_GET['et_fb'] );
     if ( $divi_builder_enabled === '1' ) {
       return TRUE;
     }
   }
   return FALSE;
 }


  /*------------------------------------------------------------------------------------
  Register and enqueue Style and Script
  -------------------------------------------------------------------------------------*/

  function divi_popup_vb_style() {
    if ( is_activated_divi_visual_builder() ) {
      wp_enqueue_style( 'divi-popup-vb-style', DIVI_POPUP_PLUGIN_URL . '/assets/css/divi-popup-vb-style.min.css', DIVI_POPUP_PLUGIN_VERSION );
    }
  }
  
  add_action( 'wp_head', 'divi_popup_vb_style' );


  /*------------------------------------------------------------------------------------
  Register Custom Post Type
  -------------------------------------------------------------------------------------*/

  function register_divi_popup_cpt() {

    $labels = array(
     'name'                  => __( 'Divi Popup', DIVI_POPUP_PLUGIN_SLUG ),
     'singular_name'         => __( 'Divi Popup', DIVI_POPUP_PLUGIN_SLUG ),
     'menu_name'             => __( 'Divi Popup', DIVI_POPUP_PLUGIN_SLUG ),
     'name_admin_bar'        => __( 'Add New Popup', DIVI_POPUP_PLUGIN_SLUG ),
     // 'parent_item_colon'     => __( 'Parent Popup:', DIVI_POPUP_PLUGIN_SLUG ),
     'all_items'             => __( 'All Popups', DIVI_POPUP_PLUGIN_SLUG ),
     'add_new_item'          => __( 'Add New Popup', DIVI_POPUP_PLUGIN_SLUG ),
     'add_new'               => __( 'Add New', DIVI_POPUP_PLUGIN_SLUG ),
     'new_item'              => __( 'New Item', DIVI_POPUP_PLUGIN_SLUG ),
   );

    $args = array(
     'label'                 => $labels,
     'labels'                => $labels,
     'supports'              => array( 'title', 'editor', 'thumbnail' ),
     'hierarchical'          => false,
     'public'                => true,
     'show_ui'               => true,
     'show_in_menu'          => true,
     'menu_position'         => 20,
     'show_in_admin_bar'     => true,
     'show_in_nav_menus'     => false,
     'can_export'            => false,
     'has_archive'           => false,
     'exclude_from_search'   => true,
     'publicly_queryable'    => true,
     'show_in_rest'          => true,
     //'query_var'             => true,
     'capability_type'       => 'post',
     //'rewrite'               => $rewrite,
     'menu_icon'             => 'dashicons-welcome-add-page',
   );
    register_post_type( 'divi-popup', $args );

  }

  add_action( 'init', 'register_divi_popup_cpt', 0 );


  /*------------------------------------------------------------------------------------
  Custom metabox
  -------------------------------------------------------------------------------------*/

  class Divi_Popup_Metabox {

    public function __construct() {
     if ( is_admin() ) {
      add_action( 'load-post.php', array( $this, 'init_metabox' ) );
      add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
    }
  }

  public function init_metabox() {
   add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
   add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
 }

 public function add_metabox() {
   add_meta_box(
    'divi_popup_metabox',
    __( 'Divi Popup Settings', DIVI_POPUP_PLUGIN_SLUG ),
    array( $this, 'divi_popup_callback' ),
    'divi-popup',
    'side',
    'low'
  );
 }


 public function divi_popup_callback( $post ) {


  // Add nonce for security and authentication
  wp_nonce_field( 'nonce_action', 'nonce' );


  // Retrieve an existing value from the database
  $divi_popup_bg_color   = get_post_meta( $post->ID, 'divi_popup_bg_color', true );
  $divi_popup_blur       = get_post_meta( $post->ID, 'divi_popup_blur', true );
  $divi_popup_id         = get_post_meta( $post->ID, 'divi_popup_id', true );


  // Set default values
  if ( empty( $divi_popup_bg_color ) ) $divi_popup_bg_color   = 'rgba(0,0,0,0.65)';
  if ( empty( $divi_popup_blur ) ) $divi_popup_blur           = '0';
  if ( empty( $divi_popup_id ) ) $divi_popup_id               = '';


  // Form field: Background Color
  echo '<div class="components-base-control" style="margin-top:20px;">';
  echo '<div class="components-base-control__field">';
  echo '<strong>' . __( 'Background Color', DIVI_POPUP_PLUGIN_SLUG ) . '</strong>';
  echo '<p style="margin: 5px 0 12px 0;">' . __( 'Set the background color of the popup', DIVI_POPUP_PLUGIN_SLUG ) . '</p>';
  echo '<input type="text" id="divi_popup_bg_color" class="color-picker divi_popup_bg_color_field" name="divi_popup_bg_color" data-alpha-enabled="true" data-default-color="rgba(0,0,0,0.65)" value="' . esc_attr__( $divi_popup_bg_color ) . '"  placeholder="' . esc_attr__( '' ) . '"></input>';
  echo '</div>';  // .components-base-control__field
  echo '</div>';  // .components-base-control


  // Form field: Background Blur Effect
  echo '<div class="components-base-control" style="margin-top:9px;">';
  echo '<div class="components-base-control__field">';
  echo '<strong>' . __('Background Blur Effect', DIVI_POPUP_PLUGIN_SLUG) . '</strong>';
  echo '<p style="margin: 5px 0 12px 0;">' . __( 'Set the level of background blur', DIVI_POPUP_PLUGIN_SLUG ) . '</p>';
  echo '<input type="number" id="divi_popup_blur" name="divi_popup_blur" step="0.01" min="0" max="10" value="' . $divi_popup_blur . '" class="divi_popup_blur_field" placeholder="' . esc_attr__( '' ) . '"></input>';
  echo '</div>';  // .components-base-control__field
  echo '</div>';  // .components-base-control


	// Form field: Popup ID
  echo '<div class="components-base-control" style="margin-top:9px;">';
  echo '<div class="components-base-control__field">';
  echo '<strong>' . __('Popup class', DIVI_POPUP_PLUGIN_SLUG) . '</strong>';
  echo '<p style="margin: 5px 0 12px 0;">' . __( 'Set a unique class for the popup', DIVI_POPUP_PLUGIN_SLUG ) . '</p>';
  echo '<input type="text" id="divi_popup_id" name="divi_popup_id" value="' . $divi_popup_id . '" class="divi_popup_id_field" placeholder="' . __( 'E.g. new-popup-one', DIVI_POPUP_PLUGIN_SLUG ) . '"></input>';
  echo '</div>';  // .components-base-control__field
  echo '</div>';  // .components-base-control


  // Form field: Text
  echo '<div class="components-base-control" style="margin-top:15px;">';
  echo '<div class="components-base-control__field">';
  echo '<p style="margin: 5px 0 12px 0;">' . __( 'Use this class as link (menu link, button) url e.g., #new-popup-one', DIVI_POPUP_PLUGIN_SLUG ) . '</p>';
  echo '</div>';  // .components-base-control__field
  echo '</div>';  // .components-base-control

}


public function save_metabox( $post_id, $post ) {

  // Add nonce for security and authentication
  $nonce_name   = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
  $nonce_action = 'nonce_action';

  // Check if a nonce is set
  if ( ! isset( $nonce_name ) )
   return;

 // Check if a nonce is valid
 if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) )
   return;

 // Check if the user has permissions to save data
 if ( ! current_user_can( 'edit_post', $post_id ) )
   return;

 // Sanitize user input
 $new_divi_popup_bg_color  = isset( $_POST[ 'divi_popup_bg_color' ] ) ? sanitize_text_field( $_POST[ 'divi_popup_bg_color' ] ) : '';
 $new_divi_popup_blur      = isset( $_POST[ 'divi_popup_blur' ] ) ? sanitize_text_field( $_POST[ 'divi_popup_blur' ] ) : '';
 $new_divi_popup_id        = isset( $_POST[ 'divi_popup_id' ] ) ? sanitize_text_field( $_POST[ 'divi_popup_id' ] ) : '';

 // Update the meta field in the database
 update_post_meta( $post_id, 'divi_popup_bg_color', $new_divi_popup_bg_color );
 update_post_meta( $post_id, 'divi_popup_blur', $new_divi_popup_blur );
 update_post_meta( $post_id, 'divi_popup_id', $new_divi_popup_id );

}

}

new divi_Popup_Metabox;


  /*------------------------------------------------------------------------------------
  Remove et_settings_meta_box for CPT divi-popup
  -------------------------------------------------------------------------------------*/

  function hide_et_setting_meta_boxes_divi_popup() {

    remove_meta_box( 'et_settings_meta_box', 'divi-popup', 'side' );
  }

  add_filter( 'add_meta_boxes', 'hide_et_setting_meta_boxes_divi_popup', 100 );

  /*------------------------------------------------------------------------------------
  Register and enqueue Style and Script
  -------------------------------------------------------------------------------------*/

  function divi_popup_enque() {

    // Register and enqueue admin script
    $loop = new WP_Query( array( 'post_type' => 'divi-popup', 'no_found_rows' => true ) ); 
    while ( $loop->have_posts() ) : $loop->the_post();

      wp_register_style( 'divi_popup_style', DIVI_POPUP_PLUGIN_URL . '/assets/css/divi-popup-style.min.css', DIVI_POPUP_PLUGIN_VERSION );
      wp_enqueue_style( 'divi_popup_style' );

    endwhile;
    wp_reset_query();
  }

  add_action( 'wp_head', 'divi_popup_enque' );


  /*------------------------------------------------------------------------------------
  Custom template for CPT divi-popup
  -------------------------------------------------------------------------------------*/

  function load_divi_popup_template( $template ) {
    global $post;

    if ( 'divi-popup' === $post->post_type && locate_template( array( 'single-divi-popup.php' ) ) !== $template ) {
      return DIVI_POPUP_PLUGIN_DIR . 'single-divi-popup.php';
    }

    return $template;
  }

  add_filter( 'single_template', 'load_divi_popup_template' );


  /*------------------------------------------------------------------------------------
  Display Popup
  -------------------------------------------------------------------------------------*/

  function display_divi_popup() {

    require_once( DIVI_POPUP_PLUGIN_DIR . 'single-divi-popup.php' );
  }

  add_action( 'wp_footer', 'display_divi_popup' );


  /*------------------------------------------------------------------------------------
  Display script
  -------------------------------------------------------------------------------------*/

  function divi_popup_render_script() {

   $loop = new WP_Query( array( 'post_type' => 'divi-popup', 'no_found_rows' => true  ) ); 
   while ( $loop->have_posts() ) : $loop->the_post();

    $divi_popup_blur      = get_post_meta( get_the_ID(), 'divi_popup_blur', true );
    $divi_popup_id        = get_post_meta( get_the_ID(), 'divi_popup_id', true );

    if ( !empty( $divi_popup_id ) ) { ?>

      <script type="text/javascript">

        // Divi Popup script
        jQuery( document ).ready( function($) {

          // Close button
          var button_close_divi_popup = $('<button type="button" id="divi-popup-close" class="divi-popup-close-<?php echo $divi_popup_id; ?>"><span></span></button>');
          $("#divi-popup.<?php echo $divi_popup_id; ?> .et_pb_section:first()").append(button_close_divi_popup);

          // Open popup
          $(document).on("click", 'a[href="#<?php echo $divi_popup_id; ?>"]', function(event) {
            event.preventDefault();
            $("html").addClass("divi-popup-open");
            $("body").addClass("divi-popup-<?php echo $divi_popup_id; ?>");
            $(".<?php echo $divi_popup_id; ?>").addClass("open");
          });

          // Close popup when clicking on background
          $(document).on("click", "#divi-popup.<?php echo $divi_popup_id; ?>", function(event) {
            $("html").removeClass("divi-popup-open");
            $("body").removeClass("divi-popup-<?php echo $divi_popup_id; ?>");
            $(this).removeClass("open");
          });

        // Fix popup closing bug
        $(document).on("click", ".<?php echo $divi_popup_id; ?> .et_pb_section", function(event) {
          event.stopPropagation();
        });

        // Close popup when clicking close button
        $(document).on("click", "button.divi-popup-close-<?php echo $divi_popup_id; ?>", function(event) {
          event.stopPropagation();
          $("html").removeClass("divi-popup-open");
          $("body").removeClass("divi-popup-<?php echo $divi_popup_id; ?>");
          $(".<?php echo $divi_popup_id; ?>").removeClass("open");
        });

      });

    </script>

  <?php }

endwhile;
wp_reset_query();

}

add_action( 'wp_footer', 'divi_popup_render_script', 100 );

}
