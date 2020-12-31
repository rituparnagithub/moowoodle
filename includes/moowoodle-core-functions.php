<?php

if(!function_exists('moowoodle_alert_notice')) {
   function moowoodle_alert_notice() {
    ?>
    <div id="message" class="error">
      <p><?php printf( __( '%sMooWoodle is inactive.%s The %sWooCommerce plugin%s must be active for the MooWoodle to work. Please %sinstall & activate WooCommerce%s', 'moowoodle' ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugins.php' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
    </div>
    <?php
  }
}

/**
 * Required moodle core functions.
 *
 * @param string $key (default: null)
 * @return array/string
 */
if ( ! function_exists( 'moowoodle_get_moodle_core_functions' ) ) {
  function moowoodle_get_moodle_core_functions( $key = '' ) {
    $moodle_core_functions = array( 'get_categories' => 'core_course_get_categories',
                                    'get_courses'  => 'core_course_get_courses',
                                    'get_moodle_users'    => 'core_user_get_users',
                                    'create_users'   => 'core_user_create_users',
                                    'update_users'   => 'core_user_update_users',
                                    'enrol_users'  => 'enrol_manual_enrol_users'
                                  );
    
    if ( empty( $key ) ) {
      return $moodle_core_functions;
    } else if ( array_key_exists( $key, $moodle_core_functions ) ) {
      return $moodle_core_functions[ $key ];
    }    
    return null;
  }
}

/**
 * Call to moodle core functions.
 *
 * @param string $function_name (default: null)
 * @param string $request_param (default: null)
 * @return mixed
 */
if ( ! function_exists( 'moowoodle_moodle_core_function_callback' ) ) {
  function moowoodle_moodle_core_function_callback( $function_name = '', $request_param = array() ) {
    global $MooWoodle;
    
    $response = null;

    $conn_settings = $MooWoodle->options_general_settings;
    $url = $conn_settings[ 'moodle_url' ];
    $token = $conn_settings[ 'moodle_access_token' ];
   
    if ( $function_name == 'core_user_get_users' ) {
      $request_url = $url . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $function_name . '&moodlewsrestformat=json&criteria[0][key]=email&criteria[0][value]=%%';
    } else{
      $request_url = $url . '/webservice/rest/server.php?wstoken=' . $token . '&wsfunction=' . $function_name . '&moodlewsrestformat=json';
    }
        
    if ( ! empty( $url )  && ! empty( $token ) && $function_name != '' ) {
      $request_query = http_build_query( $request_param );
      $response = wp_remote_post( $request_url, array( 'body' => $request_query ) );
    } 
    
    if ( ! is_wp_error( $response ) && $response != null && $response[ 'response' ][ 'code' ] == 200 ) {
      if ( is_string( $response[ 'body' ] ) ) {
        $response_arr = json_decode( $response[ 'body' ], true );
        
        if ( json_last_error() === JSON_ERROR_NONE ) {
          if ( is_null( $response_arr ) || ! array_key_exists( 'exception', $response_arr ) ) {
            $MooWoodle->ws_has_error = false;
            return $response_arr;
          } else {
            $MooWoodle->ws_has_error = true;
          }
        } else {
          $MooWoodle->ws_has_error = true;
        }
      } else {
        $MooWoodle->ws_has_error = true;
      }
    } else {
      $MooWoodle->ws_has_error = true;
    }    
    return null;
  }
}

/**
 * Returns term id by moodle category id
 *
 * @param int $category_id
 * @param string $taxonomy (default: null)
 * @param string $meta_key (default: null)
 * @return int
 */
function moowoodle_get_term_by_moodle_id( $category_id, $taxonomy = '', $meta_key = '' ) {
  if ( empty( $category_id ) || ! is_numeric( $category_id ) || empty( $taxonomy ) || ! taxonomy_exists( $taxonomy ) || empty( $meta_key ) ) {
    return 0;
  }

  $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
  if ( $terms ) {
    foreach ( $terms as $term ) {
      // if ( apply_filters( "moowoodle_get_{$meta_key}_meta", $term->term_id, '_category_id', true ) == $category_id ) {
      if ( get_term_meta( $term->term_id, '_category_id', true ) == $category_id ) {
        return $term->term_id;
      }
    }
  }
  return 0;
}

/**
 * Returns post id by moodle category id.
 *
 * @param int $course_id
 * @param string $post_type (default: null)
 * @return int
 */
function moowoodle_get_post_by_moodle_id( $course_id, $post_type = '' ) {
  if ( empty( $course_id ) || ! is_numeric( $course_id ) || empty( $post_type ) || ! post_type_exists( $post_type ) ) {
    return 0;
  }
  $posts = get_posts( array( 'post_type' => $post_type, 'numberposts' => -1 ) );

  if ( $posts ) {
    foreach ( $posts as $post ) {
      if ( get_post_meta( $post->ID, 'moodle_course_id', true ) == $course_id ) {
        return $post->ID;
      }
    }
  }
  return 0;
}

/**
 * Woodle Term Meta API - set table name
 *
 * @return void
 */
function moowoodle_taxonomy_metadata_wpdbfix() {
  global $wpdb;
  
  $termmeta_name = 'woodle_termmeta';
  $wpdb->woodle_termmeta = $wpdb->prefix . $termmeta_name;
  $wpdb->tables[] = 'woodle_termmeta';
  
}

add_action( 'init', 'moowoodle_taxonomy_metadata_wpdbfix', 0 );
add_action( 'switch_blog', 'moowoodle_taxonomy_metadata_wpdbfix', 0 );

// Old version to new migration
if ( ! function_exists( 'moowoodle_option_migration_2_to_3' ) ) {
  function moowoodle_option_migration_2_to_3() {

    global $MooWoodle, $wpdb;
    if( !get_option( 'moowoodle_migration_completed' ) ) :

      $old_setting = get_option( 'dc_dc_woodle_general_settings_name' );

      $conn_settings = $MooWoodle->options_general_settings;
      $conn_settings[ 'moodle_url' ] = $old_setting[ 'access_url' ];
      $conn_settings[ 'moodle_access_token' ] = $old_setting[ 'ws_token' ];
      update_option( 'moowoodle_general_settings', $conn_settings );

      $display_settings = $MooWoodle->options_display_settings;
      if ( isset( $old_setting[ 'wc_product_dates_display' ] ) && $old_setting[ 'wc_product_dates_display' ] == "yes" ) {
        $display_settings[ 'start_end_date' ] = "Enable";
        update_option( 'moowoodle_display_settings', $display_settings );
      }

      $sync_settings = $MooWoodle->options_synchronize_settings;
      if ( isset( $old_setting[ 'create_wc_product' ] ) && $old_setting[ 'create_wc_product' ] == "yes" ) {
        $sync_settings[ 'sync_products' ] = "Enable";
        update_option( 'moowoodle_synchronize_settings', $sync_settings );
      }            
      
      delete_option( 'dc_dc_woodle_general_settings_name' );
      delete_post_meta_by_key( '_cohert_id' );
      delete_post_meta_by_key( '_group_id' );

      $wpdb->update( $wpdb->postmeta, array( 'meta_key' => 'linked_course_id' ), array( 'meta_key' => 'product_course_id' ) );
      $wpdb->update( $wpdb->postmeta, array( 'meta_key' => 'moodle_course_id' ), array( 'meta_key' => '_course_id' ) );
      
      delete_option( 'woodle_version' );
      delete_option( 'woodle_db_version' );

      $terms = retrieve_term( 'course_cat' );
      if ( $terms ) {
        foreach ( $terms as $term ) {
          add_meta_value( $term->term_id );
        }
      }
      
      update_option( 'moowoodle_migration_completed', 'migrated' );
    endif;
  }    
}

if( ! function_exists( 'retrieve_term' ) ) {
  function retrieve_term( $taxonomy ) {
    global $wpdb;
     
    $query = "SELECT terms.term_id, terms.name, terms.slug, term_taxonomy.term_taxonomy_id, term_taxonomy.parent, term_taxonomy.description
            FROM {$wpdb->terms} AS terms
            INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy
            ON terms.term_id = term_taxonomy.term_id
            WHERE term_taxonomy.taxonomy = '$taxonomy'";
    $terms = $wpdb->get_results( $query );
    $terms = ( is_wp_error( $terms ) || empty( $terms ) ) ? false : $terms;
    return $terms; 
  }
}

if( ! function_exists( 'add_meta_value' ) ) {
  function add_meta_value( $term_id ) {
    global $wpdb;
    $query_id = "SELECT meta_value FROM {$wpdb->prefix}woodle_termmeta WHERE woodle_term_id = $term_id and meta_key = '_category_id' ";
    $query_parent = "SELECT meta_value FROM {$wpdb->prefix}woodle_termmeta WHERE woodle_term_id = $term_id and meta_key = '_parent' ";
    $query_path = "SELECT meta_value FROM {$wpdb->prefix}woodle_termmeta WHERE woodle_term_id = $term_id and meta_key = '_category_path' ";
    
    $category_id = $wpdb->get_row( $query_id );
    $parent = $wpdb->get_row( $query_parent );
    $category_path = $wpdb->get_row( $query_path );

    $insert_id = add_term_meta ( $term_id, '_category_id', $category_id->meta_value, false );
    $insert_parent = add_term_meta ( $term_id, '_parent', $parent->meta_value, false );
    $insert_path = add_term_meta ( $term_id, '_category_path', $category_path->meta_value, false );

  }
}

//Adds my-courses endpoints
add_action( 'init', 'add_my_courses_endpoint' );
function add_my_courses_endpoint() {
  add_rewrite_endpoint( 'my-courses', EP_ROOT | EP_PAGES );
  flush_rewrite_rules();
}

//Adds the menu item to my-account WooCommerce menu 
add_filter ( 'woocommerce_account_menu_items', 'my_courses_page_link' );
function my_courses_page_link( $menu_links ){
 
  global $MooWoodle;
  $new = array( 'my-courses' => 'My Courses' );

  $display_settings = $MooWoodle->options_display_settings;
  if ( isset( $display_settings[ 'my_courses_priority' ] ) ) {
    $priority_below = $display_settings[ 'my_courses_priority' ];
  } else {
    $priority_below = 0;
  }

  if( $priority_below == 0 ) {
    $menu_links = array_slice( $menu_links, $priority_below, 1, true ) 
    + $new 
    + array_slice( $menu_links, $priority_below + 1, NULL, true ); 
  } else {
    $menu_links = array_slice( $menu_links, 0, $priority_below + 1, true ) 
    + $new 
    + array_slice( $menu_links, $priority_below + 1, NULL, true ); 
  }  
 
  return $menu_links; 
}

add_action( 'woocommerce_account_my-courses_endpoint', 'woocommerce_account_my_courses_endpoint' );
function woocommerce_account_my_courses_endpoint() {
   // _e('Your Courses are', 'moowoodle');
  global $wpdb;
  $i = 0;
  $customer = get_current_user_id();
  $customer_orders = get_posts( array(
    'numberposts' => -1,
    'meta_key' => '_customer_user',
    'orderby' => 'date',
    'order' => 'DESC',
    'meta_value' => $customer,
    'post_type' => 'shop_order',
    'post_status' => 'any'
  ) );
  if ( count( $customer_orders ) > 0 ) {
        ?> <p> <?php
        global $current_user;
        echo '<div class="instraction-tri">';
        echo 'Use this username and password for first time login to your moodle site.<br>';
        echo 'Username : ' . $current_user->user_login . '<br>';
        echo 'Password : 1Admin@23 <br>';
        echo 'To enroll and access your course please click on the course link given below:<br>';
        echo '</div>';
      ?> </p> <?php
        foreach ( $customer_orders as $customer_order ) {
        $order = wc_get_order( $customer_order->ID );
        foreach ( $order->get_items() as $enrolment ) {
        $course_id = get_post_meta( $enrolment->get_product_id(), 'moodle_course_id', true );
        $post_id = moowoodle_get_post_by_moodle_id( $course_id, 'course' );
        $course = get_post( $post_id );
        $enrollment_data = array();
        $enrollment_data[ 'course_name' ] = $course->post_title;

        $course_id_meta = get_post_meta( $post_id , 'moodle_course_id', true );

        // WP_Query arguments
        $args = array (
            'post_type'              => array( 'course', 'product' ),
            'post_status'            => array( 'publish' ),
            'meta_query'             => array(
                array(
                    'key'       => 'moodle_course_id',
                    'value'     => $course_id_meta,
                ),
            ),
        );
        $post_product_id = 0;
        // The Query
        $query = new WP_Query( $args );
        // The Loop
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                if ( get_post_type( get_the_ID() ) == 'product' ) {
              $post_product_id = get_the_ID();              
            }       

            }
        }

        // Restore original Post Data
        wp_reset_postdata();
        
        $linked_course_id = ! empty( get_post_meta( $post_product_id, 'linked_course_id', true ) ) ? get_post_meta( $post_product_id, 'linked_course_id', true ) : '';
        $enrollment_list[] = apply_filters( 'moowoodle_course_url', $linked_course_id, $enrollment_data[ 'course_name' ] );
        if ( $order->get_status() == 'completed' ) {
          ?> <p> <?php echo '<button type="button" class="button-tri">' . $enrollment_list[ $i ] . '</button> <br>'; ?> </p> <?php 
        } else {
          ?> <p> <?php echo '<div class="payment-tri">' . esc_html_e("You can not access your course : ", 'moowoodle') . esc_html( $enrollment_data[ 'course_name' ] ) . esc_html_e( " ( Payment ", 'moowoodle' ) . $order->get_status() . ' ) </div>'; ?> </p> <?php 
        }
        $i++;

        $enrollment_data_arr[] = $enrollment_data;
        }
        }
    }
}

add_filter( 'moowoodle_course_url', 'set_moowoodle_course_url',10, 2 );
function set_moowoodle_course_url( $linked_course_id, $course_name ) {
  global $MooWoodle;
  $course = $linked_course_id;
  $class = 'moowoodle';
  $target = '_self';
  $authtext = '';
  $activity = 0;
  $content = $course_name;

  $url = '<a target="' . esc_attr( $target ) . '" class="' . esc_attr( $class ) . '" href="' . $MooWoodle->enrollment->moowoodle_generate_hyperlink( $course, $activity ) . '">' . $content . '</a>';
  return $url;
}