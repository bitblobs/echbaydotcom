<?php



//
function WGR_echo_sitemap_css () {
	echo '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="' . EB_URL_OF_PLUGIN . 'css/main-sitemap.xsl?v=' . date_time . '"?>' . "\n";
/*
<?xml-stylesheet type="text/css" href="' . EB_URL_OF_PLUGIN . 'css/xml.css?v=' . date_time . '"?>';
*/
}


function WGR_echo_sitemap_urlset () {
	// v2 -> Yoast SEO style
	return '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	
	// v1
	return '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
}


function WGR_echo_sitemap_node ( $loc, $lastmod ) {
	return '
<sitemap>
	<loc>' . $loc . '</loc>
	<lastmod>' . $lastmod . '</lastmod>
</sitemap>';
}

function WGR_sitemap_part_page ( $type = 'post', $file_name = 'sitemap-post', $file_2name = 'sitemap-images' ) {
	global $limit_post_get;
	global $sitemap_current_time;
	
	$count_post_post = WGR_get_sitemap_total_post( $type );
//	echo $count_post_post . '<br>' . "\n";
	
	$str = '';
	if ( $count_post_post > $limit_post_get ) {
		$j = 0;
		for ( $i = 2; $i < 100; $i++ ) {
			$j += $limit_post_get;
			
			if ( $j < $count_post_post ) {
				// cho phần bài viết
				$str .= WGR_echo_sitemap_node( web_link . $file_name . '?trang=' . $i, $sitemap_current_time );
				
				// cho phần ảnh
//				$str .= WGR_echo_sitemap_node( web_link . $file_2name . '?trang=' . $i, $sitemap_current_time );
			}
		}
	}
	
	return $str;
}


/*
* changefreq = hourly
*/
function WGR_echo_sitemap_url_node ( $loc, $priority, $lastmod, $op = array() ) {
	
	//
	$str_list_img = '';
	
	//
	if ( ! empty( $op ) ) {
		// set thời gian nạp dữ liệu mặc định
		if ( ! isset( $op['changefreq'] ) ) {
			$op['changefreq'] = 'daily';
		}
		
		// nếu có lệnh tìm ảnh trong bài viết
		if ( isset( $op['get_images'] ) && $op['get_images'] > 0 ) {
			$str_list_img .= WGR_create_sitemap_image_node( WGR_get_sitemap_post( 'attachment', array(
				'post_parent' => $op['get_images']
			) ) );
		}
	}
	else {
		$op['changefreq'] = 'daily';
	}
	
	//
	return '
<url>
	<loc>' . $loc . '</loc>
	<lastmod>' . $lastmod . '</lastmod>
	<changefreq>' . $op['changefreq'] . '</changefreq>
	<priority>' . $priority . '</priority>
	' . $str_list_img . '
</url>';
	
}

// tạo sitemap mặc định trong trường hợp không tìm thấy sitemap
function WGR_create_sitemap_default_node () {
	global $sitemap_date_format;
	
	return WGR_echo_sitemap_url_node(
		web_link,
		1,
		date( $sitemap_date_format, date_time )
	);
}


function WGR_create_sitemap_image_node ( $sql ) {
	
//	print_r( $sql );
	
	$str = '';
	foreach ( $sql as $v ) {
		$img = $v->guid;
		
		$name = $v->post_excerpt;
		if ( $name == '' && $v->post_title != '' ) {
			$name = str_replace( '-', ' ', $v->post_title );
		}
		
		$url = $img;
		if ( $v->post_parent > 0 ) {
			$url = _eb_p_link( $v->post_parent );
		}
		
		$str .= WGR_echo_sitemap_image_node(
			$url,
			$img,
			$name
		);
	}
	
	return $str;
	
}

function WGR_echo_sitemap_image_node ( $loc, $img, $title ) {
	if ( $img == '' ) {
		return '';
	}
	
	//
	if ( substr( $img, 0, 2 ) == '//' ) {
		$img = eb_web_protocol . ':' . $img;
	}
	
	//
	return '
<url>
	<loc>' . $loc . '</loc>
	<image:image>
		<image:loc>' . $img . '</image:loc>
		<image:title><![CDATA[' . $title . ']]></image:title>
	</image:image>
</url>';
}

// tạo sitemap mặc định trong trường hợp không tìm thấy sitemap
function WGR_create_sitemap_image_default_node () {
	global $__cf_row;
	
	if ( strstr( $__cf_row['cf_logo'], '//' ) == false ) {
		$__cf_row['cf_logo'] = web_link . $__cf_row['cf_logo'];
	}
	
	return WGR_echo_sitemap_image_node(
		web_link,
		$__cf_row['cf_logo'],
		web_name
	);
}


function WGR_get_sitemap_post ( $type = 'post', $op = array() ) {
	global $wpdb;
	global $limit_post_get;
//	echo wp_posts;
	
	$status = 'publish';
	if ( $type == 'attachment' ) {
		$status = 'inherit';
	}
	
	//
	$strFilter = "";
	if ( ! empty( $op ) ) {
		if ( isset( $op['post_parent'] ) && $op['post_parent'] > 0 ) {
			$strFilter .= " AND post_parent = " . $op['post_parent'] . " ";
		}
	}
	
	// phân trang
	$trang = isset( $_GET['trang'] ) ? (int)$_GET['trang'] : 1;
	
	$totalThread = WGR_get_sitemap_total_post( $type );
	$threadInPage = $limit_post_get;
	
	$totalPage = ceil ( $totalThread / $threadInPage );
	if ( $totalPage < 1 ) {
		$totalPage = 1;
	}
	
	if ($trang > $totalPage) {
		$trang = $totalPage;
	}
	else if ( $trang < 1 ) {
		$trang = 1;
	}
	
	$offset = ($trang - 1) * $threadInPage;
	
	//
	$sql = _eb_q("SELECT *
	FROM
		`" . wp_posts . "`
	WHERE
		post_type = '" . $type . "'
		AND post_status = '" . $status . "'
		" . $strFilter . "
	ORDER BY
		ID DESC
	LIMIT " . $offset . ", " . $threadInPage);
//	print_r( $sql );
	
	return $sql;
}

function WGR_get_sitemap_total_post ( $type = 'post' ) {
	global $wpdb;
//	echo wp_posts;
	
	$status = 'publish';
	if ( $type == 'attachment' ) {
		$status = 'inherit';
	}
	
	return _eb_c("SELECT COUNT(ID) as a
	FROM
		`" . wp_posts . "`
	WHERE
		post_type = '" . $type . "'
		AND post_status = '" . $status . "'");
}


function WGR_get_sitemap_taxonomy ( $taxx = 'category', $priority = 0.9, $cat_ids = 0 ) {
	global $wpdb;
	global $sitemap_current_time;
	
	// v1
	/*
	$categories = _eb_q("SELECT *
	FROM
		`" . $wpdb->terms . "`
	WHERE
		term_id IN ( select term_id
					from
						`" . $wpdb->term_taxonomy . "`
					where
						taxonomy = '" . $taxx . "' )
	ORDER BY
		name");
	*/
	
	// v2
	$categories = get_categories( array(
		'taxonomy' => $taxx,
		'parent' => $cat_ids
	) );
	
//	print_r( $categories );
	
	//
	$str = '';
//	if ( count( $categories ) > 0 ) {
	if ( ! empty( $categories ) ) {
		foreach ( $categories as $cat ) {
			if ( _eb_get_cat_object( $cat->term_id, '_eb_category_hidden', 0 ) != 1 ) {
				$str .= WGR_echo_sitemap_url_node(
					_eb_c_link( $cat->term_id, $taxx ),
					$priority,
					$sitemap_current_time,
					array(
						'changefreq' => 'always'
					)
				)
				. WGR_get_sitemap_taxonomy ( $taxx, $priority, $cat->term_id );
			}
		}
	}
	
	return $str;
}


function WGR_sitemap_fixed_old_content ( $a, $b ) {
	if ( $a != '' ) {
		$b = WGR_replace_for_all_content( $a, $b );
	}
	return $b;
}




// định dạng ngày tháng
$sitemap_date_format = 'c';
$sitemap_current_time = date( $sitemap_date_format, date_time );

// giới hạn số bài viết cho mỗi sitemap map
$limit_post_get = 1000;
//$limit_post_get = 10;

// giới hạn tạo sitemap cho hình ảnh -> google nó limit 1000 ảnh nên chỉ lấy thế thôi
$limit_image_get = $limit_post_get;

// thời gian nạp lại cache cho file, để = 0 -> disable
$time_for_relload_sitemap = 0;
//$time_for_relload_sitemap = 3 * 3600;
$get_list_sitemap = false;




//
header("Content-type: text/xml");




