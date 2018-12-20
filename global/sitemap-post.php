<?php


// thư viện dùng chung
include EB_THEME_PLUGIN_INDEX . 'global/sitemap_function.php';





/*
* Danh sách post (sản phẩm)
*/
$strCacheFilter = basename( __FILE__, '.php' );
if ( $time_for_relload_sitemap > 0 ) {
	$get_list_sitemap = _eb_get_static_html ( $strCacheFilter, '', '', $time_for_relload_sitemap );
}

if ( $get_list_sitemap == false || eb_code_tester == true ) {
	
	
	//
	$get_list_sitemap = '';
	
	
	
	/*
	* details
	*/
	
	// v2
	$sql = WGR_get_sitemap_post();
	foreach ( $sql as $v ) {
		$get_list_sitemap .= WGR_echo_sitemap_url_node(
			_eb_p_link( $v->ID ),
			0.5,
			date( $sitemap_date_format, strtotime( $v->post_modified ) ),
			array(
				'get_images' => $v->ID
			)
		);
	}
	
	/*
	// v1
	$sql = new WP_Query( array(
		'posts_per_page' => $limit_post_get,
//		'orderby' => 'menu_order',
		'orderby' => 'ID',
		'order' => 'DESC',
		'post_type' => 'post',
		'post_status' => 'publish'
	));
	//print_r( $sitemap_post );
	while ( $sql->have_posts() ) : $sql->the_post();
//		print_r($sql->post);
		
		$get_list_sitemap .= WGR_echo_sitemap_url_node(
			_eb_p_link( $sql->post->ID ),
			0.5,
			date( $sitemap_date_format, strtotime( $sql->post->post_modified ) )
		);
	endwhile;
	*/
	
	
	
	//
	$get_list_sitemap = trim($get_list_sitemap);
	
	//
	$get_list_sitemap = WGR_sitemap_fixed_old_content( $__cf_row['cf_replace_content'], $get_list_sitemap );
	
	// lưu cache
	_eb_get_static_html ( $strCacheFilter, $get_list_sitemap, '', 1 );
	
	
}





//
WGR_echo_sitemap_css();

echo WGR_echo_sitemap_urlset() . $get_list_sitemap . '
</urlset>';



exit();




