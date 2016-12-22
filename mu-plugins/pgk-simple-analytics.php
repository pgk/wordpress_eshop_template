<?php
/*
Plugin Name:  Register Theme Directory
Plugin URI:   https://roots.io/bedrock/
Description:  Register default theme directory
Version:      1.0.0
Author:       Roots
Author URI:   https://roots.io/
License:      MIT License
*/

// if (!defined('WP_DEFAULT_THEME')) {
//     register_theme_directory(ABSPATH . 'wp-content/themes');
// }

class PgkSimpleAnalytics
{
    const SIMPLE_ANALYTICS = 'pgk_simple_analytics';
    const SIMPLE_ANALYTICS_GROUP = 'pgk_simple_analytics_group';
    const SIMPLE_ANALYTICS_PAGE = 'simple-analytics-admin';
    const SIMPLE_ANALYTICS_PAGE_TITLE = 'Simple Analytics Settings';
    const GA_ANALYTICS_ID = 'ga_analytics_id';
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
      
      if (is_admin()) {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
      }
      add_action('wp_footer', array($this, 'render_google_analytics'));
    }

    public function render_google_analytics() {
      if (is_admin()) {
        return;
      }
      $simple_analytics_option = get_option( self::SIMPLE_ANALYTICS );
      if (!isset( $simple_analytics_option[self::GA_ANALYTICS_ID]) || 
          empty($simple_analytics_option[self::GA_ANALYTICS_ID])) {
        return;
      }
      $tracking_code = $simple_analytics_option[self::GA_ANALYTICS_ID];

      ?>

      <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', '<?php echo $tracking_code ?>', 'auto');
      ga('send', 'pageview');

    </script>

      <?php
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            self::SIMPLE_ANALYTICS_PAGE_TITLE, 
            'manage_options', 
            self::SIMPLE_ANALYTICS_PAGE, 
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( self::SIMPLE_ANALYTICS );
        ?>
        <div class="wrap">
            <h1><?php echo self::SIMPLE_ANALYTICS_PAGE_TITLE; ?></h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( self::SIMPLE_ANALYTICS_GROUP );
                do_settings_sections( self::SIMPLE_ANALYTICS_PAGE );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
      // flush_rewrite_rules();
        register_setting(
            self::SIMPLE_ANALYTICS_GROUP, // Option group
            self::SIMPLE_ANALYTICS, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Google Analytics', // Title
            array( $this, 'print_section_info' ), // Callback
            self::SIMPLE_ANALYTICS_PAGE // Page
        );  

        add_settings_field(
            'ga_analytics_id', // ID
            'Google Analytics ID', // Title 
            array( $this, 'ga_analytics_id_callback' ), // Callback
            self::SIMPLE_ANALYTICS_PAGE, // Page
            'setting_section_id' // Section           
        );      

        // add_settings_field(
        //     'title', 
        //     'Title', 
        //     array( $this, 'title_callback' ), 
        //     self::SIMPLE_ANALYTICS_PAGE, 
        //     'setting_section_id'
        // );      
    }

    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['ga_analytics_id'] ) )
            $new_input['ga_analytics_id'] = sanitize_text_field( $input['ga_analytics_id'] );

        // if( isset( $input['title'] ) )
        //     $new_input['title'] = sanitize_text_field( $input['title'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your GA settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function ga_analytics_id_callback()
    {
        printf(
            '<input type="text" id="ga_analytics_id" name="%s[ga_analytics_id]" value="%s" />',
            self::SIMPLE_ANALYTICS, isset( $this->options['ga_analytics_id'] ) ? esc_attr( $this->options['ga_analytics_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }
}

$simple_analytics = new PgkSimpleAnalytics();
