<?php 

add_shortcode('qa', 'qahome_shortcode');

/* Define the shortcode function */

function qahome_shortcode( $atts ) {
	
	global $qaplus_options, $catname;
	$catname = (get_query_var('category_name'));
	
	STATIC $i = 0;

	$qaplus_shortcode_output = '';
	
	extract(shortcode_atts(array(
		'id' => '',
		'cat' => '',
		'limit' => $qaplus_options['limit'],
		'search' => $qaplus_options['search'],
		'searchpos' => $qaplus_options['searchpos'],
		'catlink' => $qaplus_options['catlink'],
		'postnumber' => $qaplus_options['postnumber'],
		'excerpts' => $qaplus_options['excerpts'],
		'permalinks' => $qaplus_options['permalinks'],
		'accordion'	=> $qaplus_options['accordion'],
		'collapsible'	=> $qaplus_options['collapsible'],
		'sort' => 'menu_order',
		'animation' => $qaplus_options['animation'],
		'exclude' => '',
		'orderby' => 'name',
	), $atts));
	
	$args = array(
		'orderby' => $orderby,
		'exclude' => $exclude,
		'taxonomy' => 'faq_category',
	);
	
	if ( $id ) { // Show a single entry //

		$qaplus_shortcode_output .= '<div class="qa-faqs qa-single cf';
			
			switch( $animation ) {
				
				case 'none' :
				$qaplus_shortcode_output .= ' animation-none';
				break;
				
				case 'slide' :
				$qaplus_shortcode_output .= ' animation-slide';
				break;
				
				default :
				$qaplus_shortcode_output .= ' animation-fade';
				break;
			}	

		if ( $accordion == "true" )	$qaplus_shortcode_output .= ' accordion';

		if ( $collapsible == "true" ) $qaplus_shortcode_output .= ' collapsible';
		 						
		$qaplus_shortcode_output .= '">
		';
		
		$args = array(
			'p'	=> $id,
			'post_type'     => 'qa_faqs',
			'post_status'   => 'publish',
			'posts_per_page' => 1,
		);
				
		$qa_faqs = new WP_Query( $args );
		
		while( $qa_faqs->have_posts() ): $qa_faqs->the_post();
			
			global $post;
			$qaplus_shortcode_output .= '<div id="qa-faq' . $i . '" class="qa-faq">
			';
			$qaplus_shortcode_output .= '<h3 class="qa-faq-title"><a class="qa-faq-anchor" href="' . get_permalink() . '">'. get_the_title().'</a></h3>
			';

			$qaplus_shortcode_output .= '<div class="qa-faq-answer">' . apply_filters( 'the_content', get_the_content() );

			if ( $permalinks == "true" ) { 
				$qaplus_shortcode_output .= '<p class="qa-faq-meta qa-post-like">';
			}

			if ( $permalinks == "true" ) $qaplus_shortcode_output .= '<a class="qa-permalink" href="' . get_permalink() . '">' . __( 'Permalink' , 'qa-plus') . '.</a>';

			if ( $permalinks == "true" ) $qaplus_shortcode_output .= '</p>';

			$qaplus_shortcode_output .= '</div><!--.qa-faq-answer-->
			</div><!--.qa-faq-->
			';

		$i++;	

		endwhile; // end loop

		$qaplus_shortcode_output .= '</div><!--.qa-faqs -->';

		wp_reset_postdata();

	} elseif ( $catname || $cat ) { // Show a single category //

		if ( $cat ) {
			$catname = $cat;
		}

		if ( $searchpos == "top" ) { 
			if ( $search == "category" || $search == "both" ) $qaplus_shortcode_output .= qa_search();
		}

		$category = get_term_by( 'slug', $catname, 'faq_category' );

		$qaplus_shortcode_output .= '<div class="qa-faqs qa-category cf';
			
			switch( $animation ) {
				
				case 'none' :
				$qaplus_shortcode_output .= ' animation-none';
				break;
				
				case 'slide' :
				$qaplus_shortcode_output .= ' animation-slide';
				break;
				
				default :
				$qaplus_shortcode_output .= ' animation-fade';
				break;
			}	

		if ( $accordion == "true" )	$qaplus_shortcode_output .= ' accordion';

		if ( $collapsible == "true" ) $qaplus_shortcode_output .= ' collapsible';
		 						
		$qaplus_shortcode_output .= '">
		';

		$qaplus_shortcode_output .=  '<div class="qa-category">
			<h2 class="faq-catname">' . $category->name . '</h2>
			';	

		$args = array(
			'order'         => 'ASC',
			'orderby' 		=> 'menu_order',
			'post_type'     => 'qa_faqs',
			'post_status'   => 'publish',
			'posts_per_page' => -1,
			'faq_category'	=> $category->slug
		);
				
		$qa_faqs = new WP_Query( $args );
		
		while( $qa_faqs->have_posts() ): $qa_faqs->the_post();
			
			global $post;
			$qaplus_shortcode_output .= '<div id="qa-faq' . $i . '" class="qa-faq">
			';
			$qaplus_shortcode_output .= '<h3 class="qa-faq-title"><a class="qa-faq-anchor" href="' . get_permalink() . '">'. get_the_title().'</a></h3>
			';

			$qaplus_shortcode_output .= '<div class="qa-faq-answer">' . apply_filters( 'the_content', get_the_content() );
			
			if ( $permalinks == "true" ) $qaplus_shortcode_output .= '<p class="qa-faq-meta qa-post-like">';

			if ( $permalinks == "true" ) $qaplus_shortcode_output .= '<a class="qa-permalink" href="' . get_permalink() . '">' . __( 'Permalink' , 'qa-plus') . '.</a>';

			if ( $permalinks == "true" ) $qaplus_shortcode_output .= '</p>';

			$qaplus_shortcode_output .= '</div><!--.qa-faq-answer-->
				</div><!--.qa-faq-->
			';

		$i++;	

		endwhile; // end loop

		$qaplus_shortcode_output .= '</div><!--.qa-category-->
		</div><!--.qa-faqs -->';

		wp_reset_postdata();
		if ( $searchpos == "bottom" ) { 
			if ( $search == "category" || $search == "both" ) $qaplus_shortcode_output .= qa_search();
		}

	}
	
	else { // Show the Q&A Homepage //
		
		$args = array(
			'orderby'	=> 'term_order',
			'order'	=> 'ASC',
			'taxonomy'	=> 'faq_category'
		);

		$categories = get_categories( $args );	

		$categories = get_categories( $args );	
		
		if ( $categories ) {
			
			$qaplus_shortcode_output .= '<div class="qa-faqs qa-home cf';
			
			switch( $animation ) {
				
				case 'none' :
				$qaplus_shortcode_output .= ' animation-none';
				break;
				
				case 'slide' :
				$qaplus_shortcode_output .= ' animation-slide';
				break;
				
				default :
				$qaplus_shortcode_output .= ' animation-fade';
				break;
			}	

			if ( $accordion == "true" )	$qaplus_shortcode_output .= ' accordion';

			if ( $collapsible == "true" ) $qaplus_shortcode_output .= ' collapsible';
		 						
			$qaplus_shortcode_output .= '">
			';

			if ( $searchpos == "top" ) { 
				if ( $search == "home" || $search == "both" ) $qaplus_shortcode_output .= qa_search();
			}
						
			foreach ( $categories as $category ) {

				$catheader = $category->name;	
				if ( $postnumber == "true" ) {
					$catheader .= ' (' . $category->count . ')';
				} 

				$qaplus_shortcode_output .=  '<div class="qa-category">
				<h2 class="faq-catname">' . $catheader . '</h2>
				';
				
				if ( $sort == 'menu_order' ) {
					$args = array(
						'order'         => 'ASC',
						'orderby' 		=> 'menu_order',
						'post_type'     => 'qa_faqs',
						'post_status'   => 'publish',
						'posts_per_page' => $limit,
						'faq_category'	=> $category->slug
					);
				} else {
					$args = array(
						'meta_key'	=> 'votes_count',
						'order'         => 'DESC',
						'orderby' 		=> 'meta_value_num',
						'post_type'     => 'qa_faqs',
						'post_status'   => 'publish',
						'posts_per_page' => $limit,
						'faq_category'	=> $category->slug
					);
				}
						
				$qa_faqs = new WP_Query( $args );
				
				while( $qa_faqs->have_posts() ): $qa_faqs->the_post();
					
					global $post;
					$qaplus_shortcode_output .= '<div id="qa-faq' . $i . '" class="qa-faq cf">
					';
					$qaplus_shortcode_output .= '<h3 class="qa-faq-title"><a class="qa-faq-anchor" href="' . get_permalink() . '">'. get_the_title().'</a></h3>
					';
					
					if ( $excerpts == "true" ) {
						$qaplus_shortcode_output .= '<div class="qa-faq-answer">' . apply_filters( 'the_content', get_the_excerpt() );
					} else { 
						$qaplus_shortcode_output .= '<div class="qa-faq-answer">' . apply_filters( 'the_content', get_the_content() );
					}

					if ( $permalinks == "true" ) $qaplus_shortcode_output .= '<p class="qa-faq-meta qa-post-like">';

					if ( $permalinks == "true" ) $qaplus_shortcode_output .= '<a class="qa-permalink" href="' . get_permalink() . '">' . __( 'Permalink' , 'qa-plus') . '.</a>';

					if ( $permalinks == "true" ) $qaplus_shortcode_output .= '</p>';

					$qaplus_shortcode_output .= '</div><!--.qa-faq-answer-->
						</div><!--.qa-faq-->
					';

					$i++;
		
				endwhile; // end loop
				
				if ( $catlink == "true" ) {
					$url =  home_url() . '/' . $qaplus_options['faq_slug'] . '/category/' . $category->category_nicename;
					$qaplus_shortcode_output .= '<a class="qa-show-more" href="' . $url . '">' . __( 'View category&rarr;', 'qa-plus' ) . '</a>';	
				}
				
				$qaplus_shortcode_output .= '</div><!-- .qa-category-->';

			}
						
			$qaplus_shortcode_output .= '</div><!--.qa-faqs -->';

			if ( $searchpos == "bottom" ) { 
				if ( $search == "home" || $search == "both" ) $qaplus_shortcode_output .= qa_search();
			}

		} else { // no categories, just home
		
			$qaplus_shortcode_output .= '<div class="qa-faqs qa-home cf';
			
			switch( $animation ) {
				
				case 'none' :
				$qaplus_shortcode_output .= ' animation-none';
				break;
				
				case 'slide' :
				$qaplus_shortcode_output .= ' animation-slide';
				break;
				
				default :
				$qaplus_shortcode_output .= ' animation-fade';
				break;
			}	

			if ( $accordion == "true" )	$qaplus_shortcode_output .= ' accordion';

			if ( $collapsible == "true" ) $qaplus_shortcode_output .= ' collapsible';
		 					
			$qaplus_shortcode_output .= '">
			';

			if ( $searchpos == "top" ) { 
				if ( $search == "home" || $search == "both" ) $qaplus_shortcode_output .= qa_search();
			}

			$catheader = $category->name;	
			if ( $postnumber == "true" ) {
				$catheader .= ' (' . $category->count . ')';
			} 


			$args = array(
				'order'         => 'ASC',
				'orderby' 		=> 'menu_order',
				'post_type'     => 'qa_faqs',
				'post_status'   => 'publish',
				'posts_per_page' => -1,
			);

			$qa_faqs = new WP_Query( $args );
			
			while( $qa_faqs->have_posts() ): $qa_faqs->the_post();
				
				global $post;
				$qaplus_shortcode_output .= '<div id="qa-faq' . $i . '" class="qa-faq cf">
				';
				$qaplus_shortcode_output .= '<h3 class="qa-faq-title"><a class="qa-faq-anchor" href="' . get_permalink() . '">'. get_the_title().'</a></h3>
				';
				
				if ( $excerpts == "true" ) {
					$qaplus_shortcode_output .= '<div class="qa-faq-answer">' . apply_filters( 'the_content', get_the_excerpt() );
				} else { 
					$qaplus_shortcode_output .= '<div class="qa-faq-answer">' . apply_filters( 'the_content', get_the_content() );
				}

				if ( $permalinks == "true" ) $qaplus_shortcode_output .= '<p class="qa-faq-meta qa-post-like">';

				if ( $permalinks == "true" ) $qaplus_shortcode_output .= '<a class="qa-permalink" href="' . get_permalink() . '">' . __( 'Permalink' , 'qa-plus') . '.</a>';

				if ( $permalinks == "true" ) $qaplus_shortcode_output .= '</p>';

				$qaplus_shortcode_output .= '</div><!--.qa-faq-answer-->
				</div><!--.qa-faq-->
				';

				$i++;
	
			endwhile; // end loop
				
		$qaplus_shortcode_output .= '</div><!--.qa-faqs -->';

			if ( $searchpos == "bottom" ) { 
				if ( $search == "home" || $search == "both" ) $qaplus_shortcode_output .= qa_search();
			}

		}
			
	}	

	wp_reset_postdata();

	return $qaplus_shortcode_output;	
}

// functions and hooks go here 

function qa_search() { 
	global $qaplus_options;

    $searchform = '<form role="search" method="get" id="qaplus_searchform" action="' . home_url() . '">
		<input type="text" value="" placeholder="' . __('Search FAQs', 'qa-plus') . '" name="s" id="qasearch" class="qaplus_search" />
		<input type="hidden" name="search_link" id="qa_search_link" value="' . home_url() . '/' . $qaplus_options['faq_slug'] . '/search/"/>
		<input type="submit" id="qaplus_searchsubmit" value="Search" />
		</form>';

	return $searchform;
}