<?php
/**
 * Highly customizable functions to get and / or display a pagination menu, without using a plugin.
 *
 * Based on the logic provided by Kriesi.at
 * (http://www.kriesi.at/archives/how-to-build-a-wordpress-post-pagination-without-plugin)
 *
 * @package WordPress
 */

/**
 * Get an array of page objects with text, class and link for each page.
 *
 * @param  integer  $range  Range of pages to show before and after the current page.
 * @param  array    $args   See the full list beside the defaults.
 * @return array            An array of page objects.
 *
 * @uses  $wp_query and $paged globals
 */
if ( !function_exists( 'get_pagination' ) ) :
function get_pagination( $range = 3, $args = array() ) {

  $defaults = array(
    'total_pages'  => '',    // Total number of pages ( $wp_query->max_num_pages ).
    'class_prefix' => '',    // String to prepend to default classes ('first', 'next', 'current', ect. ).
    'count'        => true,  // Show page count ( current page and total number of pages ).
    'directional'  => true,  // Show previous and next page links.
    'edges'        => true   // Show first and last page links.
  );
  $args = wp_parse_args( $args, $defaults );

  // Turn the arguments into single variables, for better handling.
  extract( $args );

  global $wp_query, $paged;

  // Get the current page number.
  if( empty( $paged ) ) { $paged = 1; }

  // If total number of pages was not passed, get it from the WP_Query object.
  if ( !$total_pages ) {
    $total_pages = $wp_query->max_num_pages;
  }

  // If the total number of pages is less than two, there is no need for pagination.
  if ( $total_pages < 2 ) { return; }

  // Initialize pagination array and required vars.
  $pagination = array();
  $showitems  = ( $range * 2 ) + 1;
  $theme = wp_get_theme();
  $theme_textdomain = ( $theme->get( 'TextDomain' ) ) ? $theme->get( 'TextDomain' ) : 'wordpress';

  // The pagination index.
  if ( $count ) :
    $pagination['count'] = ( object ) array(
      'current_page' => $paged,
      'total_pages'  => $total_pages,
      'class'        => $class_prefix . 'count'
    );
  endif;

  // First page link.
  if ( $edges && $paged > 2 && $paged > ( $range + 1 ) && $showitems < $total_pages ) :
    $pagination['first'] = ( object ) array(
      'text'  => __( 'First', $theme_textdomain ),
      'class' => $class_prefix . 'first',
      'link'  => get_pagenum_link( 1 )
    );
  endif;

  // Previous page link.
  if ( $directional && $paged > 1 && $showitems < $total_pages ) :
  $pagination['prev'] = ( object ) array(
    'text'  => __( 'Previous', $theme_textdomain ),
    'class' => $class_prefix . 'prev',
    'link'  => ( $paged - 1 ) ? get_pagenum_link( $paged - 1 ) : ''
  );
  endif;

  // The page links.
  for ( $i = 1; $i <= $total_pages; $i++ ) :

    // Current page link.
    if ( $paged == $i ) {
      $pagination['current'] = ( object ) array(
        'text'  => $i,
        'class' => $class_prefix . 'current is-active',
        'link'  => ''
      );
    }

    else if ( !( $i >= ( $paged + $range ) + 1 || $i <= ( $paged - $range ) - 1 ) || $total_pages <= $showitems ) {
      $pagination[$i] = ( object ) array(
        'text'  => $i,
        'class' => '',
        'link'  => get_pagenum_link( $i )
      );
    }

  endfor;

  // Next page link.
  if ( $directional && $paged < $total_pages && $showitems < $total_pages ) :
  $pagination['next'] = ( object ) array(
    'text'  => __( 'Next', $theme_textdomain ),
    'class' => $class_prefix . 'next',
    'link'  => get_pagenum_link( $paged + 1 )
  );
  endif;

  // Last page link.
  if ( $edges && $paged < $total_pages - 1 && $paged + $range - 1 < $total_pages && $showitems < $total_pages ) :
  $pagination['last'] = ( object ) array(
    'text'  => __( 'Last', $theme_textdomain ),
    'class' => $class_prefix . 'last',
    'link'  => get_pagenum_link( $total_pages )
  );
  endif;

  return $pagination;
}
endif;

/**
 * Display a pagination nav. Accepts a pagination array {@link get_pagination()} as the first parameter.
 * 
 * @param  int | object  $range  Pages to show before and after current page, or a pagination array.
 * @param  array  $args  See get_pagination() function for the full list of args.
 * 
 * @uses  get_pagination()
 */
if ( !function_exists( 'the_pagination' ) ) :
function the_pagination( $range = 3, $args = array() ) {
  
  // If an array of pages is passed as the first parameter,
  // set it as the paginaion array. Else, get array of pagination links.
  $pagination = ( is_array( $range ) ) ? $range : get_pagination( $range, $args );

  if ( empty( $pagination ) ) { return; }

  // Get theme texdomain for string translation.
  $theme = wp_get_theme();
  $theme_textdomain = ( $theme->get( 'TextDomain' ) ) ? $theme->get( 'TextDomain' ) : 'wordpress';

  // Build the HTML output.
  $html = array();
  $html[] = '<nav class="pagination" role="navigation">';
  $html[] =   "\t" . '<ul>';

  // Display the index, if needed.
  if ( !empty( $pagination['count'] ) ) {

    $item   = $pagination['count'];

    $part   = array();
    $part[] = "\t\t" . '<li class="' . $item->class . '"><span>';
    $part[] = '<span>' . __( 'Page', $theme_textdomain ) . '</span> ';
    $part[] = '<span class="current_page">' . $item->current_page . '</span> ';
    $part[] = '<span>' . __( 'of', $theme_textdomain ) . '</span> ';
    $part[] = '<span class="total_pages">' . $item->total_pages . '</span>';
    $part[] = "\t\t" . '</span></li>';

    $html[] = implode( '', $part );
    unset( $pagination['count'] );
  }

  // Display all the other links.
  foreach ( $pagination as $key => $item ) {

    $part   = array();
    $part[] = "\t\t" . '<li';
    if ( $item->class ) { $part[] = ' class="' . $item->class . '"'; }
    $part[] = '>';

    if ( !$item->link ) {
      $part[] = '<span>' . $item->text . '</span>';
    }
    else {
      $part[] = '<a href="' . $item->link . '">' . $item->text . '</a>';
    }

    $part[] = '</li>';

    $html[] = implode( '', $part );
  }

  $html[] =   "\t" . '</ul>';
  $html[] = '</nav>';

  echo implode( "\n", $html );
}
endif;