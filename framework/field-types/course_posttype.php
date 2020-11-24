<?php

// from heading
$from_heading = apply_filters( 'moowoodle_courses_heading', array(
    __( "Course ID", 'moowoodle' ),
    __( "Course Name", 'moowoodle' ),
    __( "Short Name", 'moowoodle' ),
    __( "Category", 'moowoodle' ),
    __( "Visibility", 'moowoodle' ),
    __( "Course ID Number", 'moowoodle' )
    
    ) );

?>

<table class="table table-bordered responsive-table moodle-linked-courses widefat">
    <thead>
        <tr>
        <?php

        $visibility_status = array( 'visible' => 'Visible',
	  								'hidden' => 'Hidden'
	  							);

        foreach ($from_heading as $key_heading => $value_heading) {
        ?>
            <th>
                <?php echo $value_heading; ?>
            </th>  
        <?php
        }
        ?>        
        </tr>
    </thead>
    <tbody>
    <?php
     $courses = woodle_get_posts( array( 'post_type' => 'course', 'post_status' => 'publish' ) );

        if( ! empty( $courses ) ) {
			foreach($courses as $course) {
				$id = get_post_meta( $course->ID, '_course_id', true );
				$course_short_name = get_post_meta( $course->ID, '_course_short_name', true );
				$course_name = $course->post_title;
				$visibility = get_post_meta( $course->ID, '_visibility', true );
				$course_idnumber = get_post_meta( $course->ID, '_course_idnumber', true );

				$category_id = get_post_meta( $course->ID, '_category_id', true );
				$term_id = woodle_get_term_by_moodle_id( $category_id, 'course_cat', 'woodle_term' );
				$course_category_path = get_woodle_term_meta( $term_id, '_category_path', true );
				$category_ids = explode( '/', $course_category_path );
				$course_path = array();
					
				if( ! empty( $category_ids ) ) {
					foreach( $category_ids as $cat_id ) {
						if( ! empty( $cat_id ) ) {
							$term_id = woodle_get_term_by_moodle_id( intval( $cat_id ), 'course_cat', 'woodle_term' );
							$term = get_term( $term_id, 'course_cat' );
							$course_path[] = $term->name;
						}
					}
				}
					
				if( ! empty( $course_path ) ) {
					$course_path = implode( ' / ', $course_path );
				}
				
				$term = get_term_by( 'id', $term_id, 'course_cat' );
				$sort_url = '';
				if( ! empty( $term ) ) {
					$sort_url = '<a href="' . admin_url( 'edit.php?course_cat=' . $term->slug . '&post_type=course' ) . '">' . $course_path . '</a>';
				}
				

       // foreach ($from_fields as $key => $value) {
        ?>
        <tr>
            <td >
                <?php
                	echo $id;
                ?>
            </td>
            <td>
                <?php 
                	echo $course_name;                
                ?>
            </td>
            <td>
                <?php
                	echo $course_short_name;
                ?> 
            </td>
            <td>
            	<?php
            		echo $sort_url;
            	?>
            </td>
            <td>
            	<?php
            		echo ( ! empty( $visibility ) ) ? $visibility_status[$visibility] : '';
            	?>
            </td>
            <td>
            	<?php
            		echo $course_idnumber;
            	?>
            </td>
        </tr>
         <?php
        }
    }
        ?>
    </tbody>
</table>
<br>

<?php

// echo __('Want to customise the form as per your need? To have a fully customizable form kindly upgrade to <a href="https://wc-marketplace.com/product/woocommerce-catalog-enquiry-pro/" target="_blank">WooCommerce Catalog Enquiry Pro</a>', 'woocommerce-catalog-enquiry', 'woocommerce-catalog-enquiry');