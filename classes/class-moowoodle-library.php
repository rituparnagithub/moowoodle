<?php
class MooWoodle_Library {

  public $lib_url;

  public $jquery_lib_url;

  public function __construct() {
	global $MooWoodle;
	  
    $this->lib_url = $MooWoodle->plugin_url . 'lib/';
    $this->jquery_lib_url = $this->lib_url . 'jquery/';
	
    }
	
	public function moowoodle_get_options(){

        global $MooWoodle;
    /**
     * Create new menus
     */

    $moowoodle_options[ ] = array(
        "type" => "menu",
        "menu_type" => "add_menu_page",
        "page_name" => __( "MooWoodle", 'moowoodle' ),
        "menu_slug" => "moowoodle",
        "layout" => "2-col"
    );

    /**
     * Settings Tab
     */
    $moowoodle_options[ ] = array(
        "type" => "tab",
        "id" => "moowoodle-general",
        "label" => __( "General", 'moowoodle' ),
        "font_class" => "fa-cogs"
    );

    // setting box
    $moowoodle_options[ ] = array(
        "type" => "setting",
        "id" => "moowoodle_general_settings",
    );

    //Connection Settings
    $moowoodle_options[ ] = array(
        "type" => "section",
        "id" => "moowoodle-connection",
        "label" => __( "Connection Settings", 'moowoodle' ),
    );

    // Moodle URL
    $moowoodle_options[ ] = array(
        "type" => "textbox",
        "id" => "moodle-url",
        'name' => "moodle_url",
        "label" => __( "Moodle Site URL", 'moowoodle' ),
        "desc" => __('Enter the Moodle Site URL', 'moowoodle' ),
    );

    //Moodle Access Token
    $moowoodle_options[ ] = array(
        "type" => "textbox",
        "id" => "moodle-access-token",
        "name" => "moodle_access_token",
        "label" => __( "Moodle Access Token", 'moowoodle' ),
        "desc" => __('Enter Moodle Access Token. You can generate the Access Token from - Dashboard => Site administration => Plugins => Web services => Manage tokens', 'moowoodle' ),
    );

    /**
     * Create new menus
     */

    $moowoodle_options[ ] = array(
        "type" => "menu",
        "menu_type" => "add_menu_page",
        "page_name" => __( "Courses", 'moowoodle' ),
        "menu_slug" => "moowoodle-courses",
        "layout" => "2-col"
    );

    $moowoodle_options[ ] = array(
        "type" => "tab",
        "id" => "moowoodle-linked-courses",
        "label" => __( "Moodle Courses", 'moowoodle' ),
        "font_class" => "fa-graduation-cap"
    );

    $moowoodle_options[ ] = array(
        "type" => "setting",
        "id" => "moowoodle_course_settings"
    );
 
    $moowoodle_options[ ] = array(
        "type" => "section",
        "id" => "moowoodle-link-course-table",
        "label" => __( "Courses", 'moowoodle' )
    );
    
    // synchronize courses categories
    $moowoodle_options[ ] = array(
        "type" => "course_posttype",
        "id" => "course-posttype",
        "name" => "course_posttype",
        "label" => __( "", 'moowoodle' ),
        "desc" => __("", '' ),
        "option_values" => array(
             'Enable' => __( '', 'moowoodle' ),
        )
    );


    /**
     * Create new menus
     */

    $moowoodle_options[ ] = array(
        "type" => "menu",
        "menu_type" => "add_menu_page",
        "page_name" => __( "Synchronization", 'moowoodle' ),
        "menu_slug" => "moowoodle-synchronization",
        "layout" => "2-col"
    );

    $moowoodle_options[ ] = array(
        "type" => "tab",
        "id" => "moowoodle-courses",
        "label" => __( "Course Synchronization", 'moowoodle' ),
        "font_class" => "fa-user"
    );

    $moowoodle_options[ ] = array(
        "type" => "setting",
        "id" => "moowoodle_synchronize_settings"
    );
 
    $moowoodle_options[ ] = array(
        "type" => "section",
        "id" => "moowoodle-sync-courses",
        "label" => __( "Synchronization Settings", 'moowoodle' )
    );
    
    //synchonise courses 
    $moowoodle_options[ ] = array(
        "type" => "checkbox",
        "id" => "draft-courses",
        "name" => "draft_courses",
        "label" => __( "Synchronize Courses", 'moowoodle' ),
        "desc" => __('If enabled courses will be synchronized from moodle.', 'moowoodle' ),
        "option_values" => array(
             'Enable' => __( '', 'moowoodle' ),
        )
    );

    // synchronize courses categories
    $moowoodle_options[ ] = array(
        "type" => "checkbox",
        "id" => "sync-course-category",
        "name" => "sync_courses_category",
        "label" => __( "Synchronize Course Categories", 'moowoodle' ),
        "desc" => __('If enabled course categories will be synchronized from moodle along with courses.', 'moowoodle' ),
        "option_values" => array(
             'Enable' => __( '', 'moowoodle' ),
        )
    );

    // synchronize products
    $moowoodle_options[ ] = array(
        "type" => "checkbox",
        "id" => "sync-products",
        "name" => "sync_products",
        "label" => __( "Automatically Creates Products From Courses", 'moowoodle' ),
        "desc" => __('If enabled products will be created while syncing courses from moodle.', 'moowoodle' ),
        "option_values" => array(
             'Enable' => __( '', 'moowoodle' ),
        )
    );

  	return apply_filters( 'moowoodle_fileds_options', $moowoodle_options);
	}
}