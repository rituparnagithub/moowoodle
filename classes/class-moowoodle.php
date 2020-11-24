<?php
class MooWoodle {

	public $plugin_url;

	public $plugin_path;

	public $version;

	public $token;
	
	public $text_domain;
	
	public $library;

	public $admin;

	public $sync;

	public $template;

	public $posttype;
	
	public $taxonomy;

	public $shortcode;

	private $file;
	
	public $settings;
	
	public $enrollment;
	
	public $emails;
	
	public $moodle_core_functions;

	public $ws_has_error;

	public function __construct($file) {

		$this->file = $file;
		$this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
		$this->plugin_path = trailingslashit(dirname($file));
		$this->token = MooWOODLE_PLUGIN_TOKEN;
		$this->text_domain = MooWOODLE_TEXT_DOMAIN;
		$this->version = MooWOODLE_PLUGIN_VERSION;
		
		// default general setting
		$this->options_general_settings = get_option('moowoodle_general_settings');
		// synchronize settings
		$this->options_synchronize_settings = get_option('moowoodle_synchronize_settings');
		
		add_action('init', array(&$this, 'init'), 0);

	}
	
	/**
	 * initilize plugin on WP init
	 */
	function init() {
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		// Init library
		$this->load_class('library');
		$this->library = new MooWoodle_Library();

		if (is_admin()) {
			$this->load_class('admin');
			$this->admin = new MooWoodle_Admin();
		}

		$this->load_class('sync');
		$this->sync = new MooWoodle_Sync();
			
				
		// init templates
		$this->load_class('template');
		$this->template = new MooWoodle_Template();
		
		// init posttype
		$this->load_class('posttype');
		$this->posttype = new MooWoodle_Posttype();
		
		// init taxonomy
		$this->load_class('taxonomy');
		$this->taxonomy = new MooWoodle_Toxonomy();
		
		// init enrollment
		$this->load_class('enrollment');
		$this->enrollment = new MooWoodle_Enrollment();
		
		// init emails
		$this->load_class('emails');
		$this->emails = new MooWoodle_Emails();

		$this->load_class('shortcode');
		$this->shortcode = new MooWoodle_shortcode();

		$this->moodle_core_functions = woodle_get_moodle_core_functions();
		
	}
	
	/**
   * Load Localisation files.
   *
   * Note: the first-loaded translation file overrides any following ones if the same translation is present
   *
   * @access public
   * @return void
   */
  public function load_plugin_textdomain() {
    $locale = apply_filters( 'plugin_locale', get_locale(), $this->token );

    load_textdomain( $this->text_domain, WP_LANG_DIR . "/moowoodle/moowoodle-$locale.mo" );
    load_textdomain( $this->text_domain, $this->plugin_path . "/languages/moowoodle-$locale.mo" );
  }

  /**
	 * Load class file
	 *
	 * @access public
	 * @param string $class_name (default: null)
	 * @return void
	 */
	public function load_class($class_name = '') {
		if ('' != $class_name && '' != $this->token) {
			require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}// End load_class()
	
	/** Cache Helpers *********************************************************/

	/**
	 * Sets a constant preventing some caching plugins from caching a page. Used on dynamic pages
	 *
	 * @access public
	 * @return void
	 */
	function nocache() {
		if (!defined('DONOTCACHEPAGE'))
			define("DONOTCACHEPAGE", "true");
		// WP Super Cache constant
	}

}