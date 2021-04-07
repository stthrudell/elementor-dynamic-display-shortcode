<?php
/**
 * Dynamic Display Sections Elementor With Shortcode
 *
 * @package ElementorDynamicDisplay
 *
 * Plugin Name: Dynamic Display Sections
 * Description: Display elementor sections and columns dynamic with shortcode.
 * Plugin URI:  https://github.com/stthrudell/elementor-dynamic-display-shortcode
 * Version:     1.0.0
 * Author:      Jean Stthrudell
 * Author URI:  https://stthrudell.github.io/
 * Text Domain: elementor-dynamic-display-shortcode
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define('ELEMENTOR_DYNAMIC_DISPLAY_PATH', __FILE__);
 
final class Dynamic_Display_SElementor {
 
    const VERSION = '1.0';
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';
    const MINIMUM_PHP_VERSION = '7.0';
 
    private static $_instance = null;
 
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
 
    public function __construct() {
        add_action( 'init', [ $this, 'i18n' ] );
        add_action( 'plugins_loaded', [ $this, 'init' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
    }
 
    public function init() {
        // Check if Elementor installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return;
        }
         
        // Check for required Elementor version
        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return;
        }
         
        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return;
        }
 
        // Add class css for display none
        add_action('wp_head', function() {
            echo "<style>.display_dynamic_shortcode_display_none {
                display: none !important;
            }</style>";
        }, 100);

        // Add Plugin controls
        add_action( 'elementor/init', [ $this, 'init_controls' ] );
    }
     
    public function i18n() {
        load_plugin_textdomain( 'elementor-dynamic-display-shortcode' );
    }
     
    public function admin_notice_missing_main_plugin() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
 
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( 'This plugin requires "%1$s" to be installed and activated.', 'elementor-dynamic-display-shortcode' ),
            '<strong>' . esc_html__( 'Elementor', 'elementor-dynamic-display-shortcode' ) . '</strong>'
        );
 
        printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
        deactivate_plugins( plugin_basename( ELEMENTOR_DYNAMIC_DISPLAY_PATH ) );
    }
     
    public function admin_notice_minimum_elementor_version() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
 
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-dynamic-display-shortcode' ),
            '<strong>' . esc_html__( 'Elementor', 'elementor-dynamic-display-shortcode' ) . '</strong>',
             self::MINIMUM_ELEMENTOR_VERSION
        );
 
        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
        deactivate_plugins( plugin_basename( ELEMENTOR_DYNAMIC_DISPLAY_PATH ) );
    }
     
    public function admin_notice_minimum_php_version() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
 
        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-dynamic-display-shortcode' ),
            '<strong>' . esc_html__( 'PHP 7.0', 'elementor-dynamic-display-shortcode' ) . '</strong>',
             self::MINIMUM_PHP_VERSION
        );
 
        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
        deactivate_plugins( plugin_basename( ELEMENTOR_DYNAMIC_DISPLAY_PATH ) );
    }
     
    public function init_controls() {
 

        //ADD CAMPO PRA INSERIR SHORTCODE NO PAINEL DO ELEMENTOR
        add_action('elementor/element/before_section_end', function( $section, $section_id, $args ) {
            if( $section_id == 'section_advanced' ){

                $section->add_control(
                    'display_dynamic_shortcode_condition' ,
                    [
                        'label' => __( 'Display for', 'elementor-dynamic-display-shortcode' ),
                        'type' => \Elementor\Controls_Manager::SELECT,
                        'default' => 'solid',
                        'options' => [
                            'all'  => __( 'All', 'elementor-dynamic-display-shortcode' ),
                            'user_logged'  => __( 'Logged Users', 'elementor-dynamic-display-shortcode' ),
                            'guests' => __( 'Guests', 'elementor-dynamic-display-shortcode' ),
                            'shortcode' => __( 'Custom shortcode', 'elementor-dynamic-display-shortcode' ),
                        ],
                    ]
                );

                // we are at the end of the "section_image" area of the "image-box"
                $section->add_control(
                    'display_dynamic_shortcode' ,
                    [
                        'label'        => __( 'Display dynamic with shortcode', 'elementor-dynamic-display-shortcode' ),
                        'type'         => Elementor\Controls_Manager::TEXT,
                        'label_block'  => true,
                        'condition' => [
                            'display_dynamic_shortcode_condition' => 'shortcode'
                        ],
                        'placeholder'  => '[your_shortcode]',
                    ]
                );

                $section->add_control(
                    'display_dynamic_shortcode_note',
                    [
                        'type' => Elementor\Controls_Manager::RAW_HTML,
                        'raw' => '<div class="elementor-descriptor">' . esc_html__(
                            'Instructions to use shortcode!',
                            'svg-divider-for-elementor'
                        ) . '</div>',
                        'condition' => [
                            'display_dynamic_shortcode_condition' => 'shortcode'
                        ]
                    ]
                );

                $section->add_control(
                    'display_dynamic_logged_note',
                    [
                        'type' => Elementor\Controls_Manager::RAW_HTML,
                        'raw' => '<div class="elementor-descriptor">' . esc_html__(
                            'This element displayed only logged users!',
                            'svg-divider-for-elementor'
                        ) . '</div>',
                        'condition' => [
                            'display_dynamic_shortcode_condition' => 'user_logged'
                        ]
                    ]
                );

                $section->add_control(
                    'display_dynamic_guest_note',
                    [
                        'type' => Elementor\Controls_Manager::RAW_HTML,
                        'raw' => '<div class="elementor-descriptor">' . esc_html__(
                            'This element displayed only unlogged users!',
                            'svg-divider-for-elementor'
                        ) . '</div>',
                        'condition' => [
                            'display_dynamic_shortcode_condition' => 'guests'
                        ]
                    ]
                );
                
            }
        }, 10, 3 );


        add_action( 'elementor/frontend/section/before_render', function ( \Elementor\Element_Base $element ) {
            $settings = $element->get_settings_for_display();

            switch ($settings['display_dynamic_shortcode_condition']) {
                case 'user_logged':
                    $this->display_for_logged_users($element, $settings);
                    break;
                case 'guests':
                    $this->display_for_guests($element, $settings);
                    break;
                case 'shortcode':
                    $this->display_for_shortcode($element, $settings);
                    break;
                
                default:
                    break;
            }
            
        } );
    }

    private function hide_element(\Elementor\Element_Base $element) {
        $element->add_render_attribute( '_wrapper', [
            'class' => 'display_dynamic_shortcode_display_none',
        ] );
    }

    public function display_for_logged_users(\Elementor\Element_Base $element, $settings) {
        $user = wp_get_current_user();
        if($user->ID == 0) $this->hide_element($element);
    }

    public function display_for_guests(\Elementor\Element_Base $element, $settings) {
        $user = wp_get_current_user();
        if($user->ID != 0) $this->hide_element($element);
    }

    public function display_for_shortcode(\Elementor\Element_Base $element, $settings) {
        $cond = do_shortcode($settings['display_dynamic_shortcode']);
        if($cond != 1 ) return;
        $this->hide_element($element);
    }
 
    public function enqueue_scripts() {
        // wp_register_style( "bootstrap-css", "https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css", array(), false, "all" );
        // wp_enqueue_style( "bootstrap-css" );
 
        // wp_register_script("bootstrap-js", "https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js", array(), false, true);
        // wp_enqueue_script("bootstrap-js");
    }
}
Dynamic_Display_SElementor::instance();