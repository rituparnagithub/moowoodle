<?php

class DC_Woodle_Posttype {

  private $labels = array();
	public $course;

	public function __construct() {
		global $DC_Woodle;

		$this->setup_post_type_labels_base();
		$this->setup_course_post_type();

		$this->load_class( 'posttype-course' );
		$this->course = new DC_Woodle_Posttype_Course();
		
		if( is_admin() ) {
			$this->load_class( 'posttype-product' );
			$this->course = new DC_Woodle_Posttype_Product();
		}
		
		if ( is_admin() ) {
			global $pagenow;
			if ( ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) {
				add_filter( 'post_updated_messages', array( &$this, 'setup_post_type_messages' ) );
			}
		}
	}

	public function setup_course_post_type() {
		global $DC_Woodle;

		$args = array(
			'labels'             => $this->create_post_type_labels( 'course', $this->labels['course']['singular'], $this->labels['course']['plural'], $this->labels['course']['menu'] ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'query_var'          => true,
			'rewrite'            => true,
			'map_meta_cap'       => false,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-welcome-learn-more',
			'supports'           => array( 'title', 'editor' ),
			'capability_type' 	 => 'post',
			'capabilities'			 => array( 'create_posts'	=> false,
																		 'delete_posts' => false
																	 )
		);

		register_post_type( 'course', $args );
	}
	
	private function create_post_type_labels( $token, $singular, $plural, $menu ) {
	  global $DC_Woodle;
	  
		$labels = array(
			'name'               => sprintf( _x( '%s', 'post type general name', 'dc-woodle' ), $plural ),
			'singular_name'      => sprintf( _x( '%s', 'post type singular name', 'dc-woodle' ), $singular ),
			'add_new'            => sprintf( _x( 'Add New %s', $token, 'dc-woodle' ), $singular ),
			'add_new_item'       => sprintf( __( 'Add New %s', 'dc-woodle' ), $singular ),
			'edit_item'          => sprintf( __( 'Edit %s', 'dc-woodle' ), $singular ),
			'new_item'           => sprintf( __( 'New %s', 'dc-woodle' ), $singular ),
			'all_items'          => sprintf( __( '%s', 'dc-woodle' ), $plural ),
			'view_item'          => sprintf( __( 'View %s', 'dc-woodle' ), $singular ),
			'search_items'       => sprintf( __( 'Search %s', 'dc-woodle' ), $plural ),
			'not_found'          => sprintf( __( 'No %s found', 'dc-woodle' ), strtolower( $plural ) ),
			'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'dc-woodle' ), strtolower( $plural ) ),
			'parent_item_colon'  => '',
			'menu_name'          => sprintf( __( '%s', 'dc-woodle' ), $menu )
		);

		return $labels;
	}

	public function setup_post_type_messages ( $messages ) {
		global $post, $post_ID, $DC_Woodle;

		$messages['course'] = $this->create_post_type_messages( 'course' );

		return $messages;
	}

	private function create_post_type_messages( $post_type ) {
		global $post, $post_ID, $DC_Woodle;

		if ( ! isset( $this->labels[ $post_type ] ) ) {
			return array();
		}

		$messages = array(
			0  => '',
			1  => sprintf( __( '%s updated.' ), esc_attr( $this->labels[ $post_type ]['singular'] ) ),
			2  => __( 'Custom field updated.', 'dc-woodle' ),
			3  => __( 'Custom field deleted.', 'dc-woodle' ),
			4  => sprintf( __( '%s updated.', 'dc-woodle' ), esc_attr( $this->labels[ $post_type ]['singular'] ) ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( '%2$s restored to revision from %1$s', 'dc-woodle' ), 
																											 wp_post_revision_title( (int) $_GET['revision'], false ), 
																											 esc_attr( $this->labels[ $post_type ]['singular'] ) ) : false,
			6  => sprintf( __( '%2$s published.' ), esc_url( get_permalink( $post_ID ) ), esc_attr( $this->labels[ $post_type ]['singular'] ) ),
			7  => sprintf( __( '%s saved.', 'dc-woodle' ), esc_attr( $this->labels[ $post_type ]['singular'] ) ),
			8  => sprintf( __( '%2$s submitted.', 'dc-woodle' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), 
													esc_attr( $this->labels[ $post_type ]['singular'] ) ),
			9  => sprintf( __( '%s scheduled for: <strong>%1$s</strong>.', 'dc-woodle' ),
													date_i18n( __( ' M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), 
													strtolower( esc_attr( $this->labels[ $post_type ]['singular'] ) ) ),
			10 => sprintf( __( '%s draft updated.', 'dc-woodle' ), esc_attr( $this->labels[ $post_type ]['singular'] ) ),
		);

		return $messages;
	}

	private function setup_post_type_labels_base() {
	  global $DC_Woodle;
	  
		$this->labels['course'] = array( 'singular' => __( 'Course', 'dc-woodle' ),
																		 'plural' => __( 'Courses', 'dc-woodle' ), 
																		 'menu' => __( 'Courses', 'dc-woodle' ) );
	}
	
	/**
	 * Load class file
	 *
	 * @access public
	 * @param string $class_name (default: null)
	 * @return void
	 */
	public function load_class($class_name = '') {
		global $DC_Woodle;
		
		if ('' != $class_name && '' != $DC_Woodle->token) {
			require_once ('posttypes/class-' . esc_attr($DC_Woodle->token) . '-' . esc_attr($class_name) . '.php');
		}
	}
}