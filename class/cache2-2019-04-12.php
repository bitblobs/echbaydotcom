<?php



// tách riêng phần cache config ra -> phần này chỉ có thay đổi khi người dùng update
include_once EB_THEME_CORE . 'cache_config.php';



//
$error_admin_log_cache = '';
$last_update = 0;
if (file_exists ( $__eb_cache_conf )) {
	
	// nếu có file kiểm tra lần update cuối
	if ( file_exists ( $file_last_update ) ) {
		
		//
		/*
		if ( function_exists('php_check_syntax') ) {
			echo 'php_check_syntax<br>';
		}
		*/
		
		/*
		if ( file_exists($__eb_cache_conf) && is_readable($__eb_cache_conf) ) {
			include_once $__eb_cache_conf;
		} else {
			throw new Exception('Include file does not exists or is not readable.');
		}
		*/
		
		$last_update = filemtime ( $file_last_update );
//		$last_update = file_get_contents ( $file_last_update );
		
		// tối đa 35 phút cache, quá thì tự dọn dẹp, hạn chế để web lỗi cache
		if ( $last_update > 0 && date_time - $last_update > 2100 ) {
			unlink ( $__eb_cache_conf );
			unlink ( $file_last_update );
			$error_admin_log_cache = 'Auto reset cache after 2100s';
		}
		// nạp conf
		else {
			// cứ 120s, kiểm tra lỗi code 1 lần
			if ( $last_update > 0 && date_time - $last_update > 120 ) {
				// kiểm tra lỗi cấu trúc file nếu có
				$return_check_syntax = 0;
				if ( function_exists('exec') ) {
					exec("php -l {$__eb_cache_conf}", $output, $return_check_syntax);
//					echo $return_check_syntax . '<br>';
					
					// khác 0 -> có lỗi
					if ( $return_check_syntax != 0 ) {
						//
//						echo 'ERROR code!'; exit();
						
						// xóa file cache đi để thử lại
						sleep( rand( 2, 10 ) );
						unlink ( $__eb_cache_conf );
						unlink ( $file_last_update );
						$error_admin_log_cache = 'Cache syntax ERROR by exec';
						
						//
//						echo eb_web_protocol . ':' . $func->full_url();
//						wp_redirect( eb_web_protocol . ':' . $func->full_url(), 302 ); exit();
					}
					// không lỗi thì dùng bình thường
					else {
						include_once $__eb_cache_conf;
					}
				}
				else {
					// kiểm tra ký tự cuối cùng của file
					$return_check_syntax = trim( file_get_contents( $__eb_cache_conf, 1 ) );
					// nếu là dấu ; -> ok
					if ( substr( $return_check_syntax, -1 ) == ';' ) {
						include_once $__eb_cache_conf;
					}
					else {
						// xóa file cache đi để thử lại
						sleep( rand( 2, 10 ) );
						unlink ( $__eb_cache_conf );
						unlink ( $file_last_update );
						$error_admin_log_cache = 'Cache syntax ERROR by substr';
					}
				}
			}
			else {
//				try {
					include_once $__eb_cache_conf;
					/*
				} catch ( Exception $e ) {
					echo 'Error include site config';
				}
				*/
			}
		}
	}
	// không có thì xóa luôn file conf cũ
	else {
		unlink ( $__eb_cache_conf );
	}
}
// chấp nhận lần đầu truy cập sẽ lỗi
//@include_once $__eb_cache_conf;

// lấy config theo thời gian thực nếu tài khoản đang đăng nhập
/*
if ( mtv_id > 0 ) {
	// không reset mảng này -> do 1 số config sẽ được tạo theo config của wp
//	$__cf_row = $__cf_row_default;
	
	// nạp lại config riêng
	_eb_get_config( true );
	EBE_get_lang_list();
}
*/




// kiểm tra thời gian tạo cache
$__eb_cache_time = date_time - $__eb_cache_time + rand ( 0, 20 );
//$__eb_cache_time += rand ( 0, 60 );
//echo $__eb_cache_time . '<br>';

//$time_for_update_cache = $cf_reset_cache;
$time_for_update_cache = $__cf_row['cf_reset_cache'];
//echo $time_for_update_cache . '<br>';


// nếu thành viên đang đăng nhập hoặc thời gian cache đã hết -> nạp cache theo thời gian thực
if ( mtv_id > 0 || $__eb_cache_time > $time_for_update_cache ) {
//if ( 1 == 2 ) {
	// nếu thời gian update cache nhỏ quá -> bỏ qua
//	if ( file_exists ( $file_last_update ) && file_exists ( $__eb_cache_conf ) ) {
	if ( $last_update > 0 ) {
		if ( date_time - $last_update < $time_for_update_cache / 2 ) {
			$__eb_cache_time = 0;
			if ( file_exists( $__eb_cache_conf ) ) {
				include_once $__eb_cache_conf;
			}
//			echo '<!-- __eb_cache_time: ' . $__eb_cache_time . ' -->' . "\n";
		}
	}
	
	
	// kiểm tra lại lần nữa cho chắc ăn
	if ( mtv_id > 0 || $__eb_cache_time > $time_for_update_cache ) {
		
		
		
		
		// Kiểm tra và tạo thư mục cache nếu chưa có
		if( ! is_dir( EB_THEME_CACHE ) ) {
//			echo EB_THEME_CACHE . '<br>' . "\n";
			
			// tự động tạo thư mục uploads nếu chưa có
			$dir_wp_uploads = dirname(EB_THEME_CACHE);
//			echo $dir_wp_uploads . '<br>' . "\n";
			if( !is_dir( $dir_wp_uploads ) ) {
				mkdir( $dir_wp_uploads, 0777 ) or die("ERROR create uploads directory: " . $dir_wp_uploads);
				// server window ko cần chmod
				chmod( $dir_wp_uploads, 0777 ) or die('chmod ERROR');
			}
			
			mkdir( EB_THEME_CACHE, 0777 ) or die("ERROR create cache directory");
			// server window ko cần chmod
			chmod( EB_THEME_CACHE, 0777 ) or die('chmod ERROR');
		}
		
		
		
		
		
		// dọn cache định kỳ -> chỉ dọn khi không phải thao tác thủ công
		if ( mtv_id > 0
//		&& strstr( $_SERVER['REQUEST_URI'], '/' . WP_ADMIN_DIR . '/' ) == true
//		&& is_admin ()
		&& ! isset( $_GET['tab'] ) ) {
			$_GET['time_auto_cleanup_cache'] = 6 * 3600;
			
			include_once EB_THEME_PLUGIN_INDEX . 'cronjob/cleanup_cache.php';
		}
		
		
		
		
		/*
		* Tự tạo các thư mục phục vụ cho cache nếu chưa có
		*/
		
		// các thư mục con của cache
		$arr_create_dir_cache = array(
			// lưu cache của các file css, js
			'static',
			'all',
			// thư mục mà các file trong này, chỉ bị reset khi hết hạn
			'noclean',
			// mail khi người dùng đặt hàng thành công sẽ gửi ở trang hoàn tất
			'booking_mail',
			'booking_mail_cache',
			'admin_invoice_product',
			'tv_mail',
			'post_meta',
			'post_img',
			'details'
		);
		foreach ( $arr_create_dir_cache as $v ) {
			$v = EB_THEME_CACHE . $v;
//			echo $v . '<br>';
			
			//
			if ( ! is_dir( $v ) ) {
				mkdir($v, 0777) or die('mkdir error');
				// server window ko cần chmod
				chmod($v, 0777) or die('chmod ERROR');
			}
		}
		
		
		
		
		function convert_arr_cache_to_parameter($arr) {
			$str = '';
			foreach ( $arr as $k => $v ) {
				$_get_type = gettype ( $v );
				if ($_get_type == 'array') {
					$_content = '';
					foreach ( $v as $k2 => $v2 ) {
						$_content .= ',"' . $k2 . '"=>"' . str_replace( '"', '\"', $v2 ) . '"';
					}
					$str .= '$' . $k . '=array(' . substr ( $_content, 1 ) . ');';
				} else if ($_get_type == 'integer' || $_get_type == 'double') {
					$str .= '$' . $k . '=' . $v . ';';
				} else {
					$v = str_replace ( '$', '\$', $v );
					$v = str_replace ( '"', '\"', $v );
					$str .= '$' . $k . '="' . $v . '";';
				}
				$str .= "\n";
			}
			
			return $str;
		}
		
		
		
		
		
		// tham số để lưu cache
		$__eb_cache_content = '$__eb_cache_time=' . date_time . ';';
		
		
		
		
		/*
		* Một số thông số khác
		*/
		
		
		
		
		
		/*
		* lấy các dữ liệu được tạo riêng cho config -> $post_id = -1;
		*/
		// reset lại cache
		$__cf_row = $__cf_row_default;
		
		//
		_eb_get_config();
		
		
		//
		if ( $__cf_row['cf_web_name'] == '' ) {
//			$web_name = get_bloginfo ( 'name' );
			$web_name = get_bloginfo ( 'blogname' );
//			$web_name = get_bloginfo ( 'sitename' );
		} else {
			$web_name = $__cf_row['cf_web_name'];
		}
		
		//
//		$__eb_cache_content .= '$web_name="' . str_replace( '"', '\"', $web_name ) . '";$web_link="' . str_replace( '"', '\"', $web_link ) . '";';
		$__eb_cache_content .= '$web_name="' . str_replace( '"', '\"', $web_name ) . '";';
			
		
		// chỉnh lại số điện thoại sang dạng html -> do safari lỗi hiển thị
		if ( $__cf_row['cf_call_dienthoai'] == '' && $__cf_row['cf_dienthoai'] != '' ) {
			$__cf_row['cf_call_dienthoai'] = '<a href="tel:' . $__cf_row['cf_dienthoai'] . '" rel="nofollow">' . $__cf_row['cf_dienthoai'] . '</a>';
		}
		
		if ( $__cf_row['cf_call_hotline'] == '' && $__cf_row['cf_hotline'] != '' ) {
			$__cf_row['cf_call_hotline'] = '<a href="tel:' . $__cf_row['cf_hotline'] . '" rel="nofollow">' . $__cf_row['cf_hotline'] . '</a>';
		}
		
		
		//
		$__cf_row['cf_reset_cache'] = (int)$__cf_row['cf_reset_cache'];
		
		// nếu thời gian update config lâu rồi, cache chưa set -> để cache mặc định
		// lần cập nhật config cuối là hơn 3 tiếng trước -> để mặc định
		if ( $localhost != 1
		&& $__cf_row ["cf_reset_cache"] <= 0 ) {
			// cho cache 120s mặc định
			if ( $__cf_row['cf_ngay'] < date_time - 3 * 3600 ) {
				$__cf_row ["cf_reset_cache"] = 120;
			}
			// hoặc tối thiểu 10s để còn test cache
			else {
				$__cf_row ["cf_reset_cache"] = 10;
			}
		}
//		print_r( $__cf_row );
		
		//
		/*
		$sql = _eb_q("SELECT option_value
		FROM
			" . $wpdb->options . "
		WHERE
			option_name = 'blog_public'
		ORDER BY
			option_id DESC
		LIMIT 0, 1");
//		print_r($sql);
//		$cf_blog_public = 1;
		if ( isset( $sql[0]->option_value ) ) {
//			$cf_blog_public = $sql[0]->option_value;
			$__cf_row ["cf_blog_public"] = $sql[0]->option_value;
		}
		*/
//		$__cf_row ["cf_blog_public"] = get_option( 'blog_public' );
		$__cf_row ["cf_blog_public"] = _eb_get_option( 'blog_public' );
		
		// định dạng ngày giờ
//		$__cf_row ["cf_date_format"] = get_option( 'date_format' );
		$__cf_row ["cf_date_format"] = _eb_get_option( 'date_format' );
//		$__cf_row ["cf_time_format"] = get_option( 'time_format' );
		$__cf_row ["cf_time_format"] = _eb_get_option( 'time_format' );
		
		// tên thư mục chứa theme theo tiêu chuẩn của echbay
		$__cf_row ["cf_theme_dir"] = basename( dirname( dirname( EB_THEME_HTML ) ) );
		
		// -> tạo chuỗi để lưu cache
		foreach ( $__cf_row as $k => $v ) {
			$__eb_cache_content .= '$__cf_row[\'' . $k . '\']="' . str_replace ( '"', '\"', str_replace ( '$', '\$', $v ) ) . '";';
		}
		
		
		
		// tạo file timezone nếu chưa có
		// chỉ với các website có ngôn ngữ không phải tiếng Việt
		if ( $__cf_row['cf_content_language'] != 'vi'
		// timezone phải tồn tại
		&& $__cf_row['cf_timezone'] != ''
		// file chưa được tạo
		&& ! file_exists ( EB_THEME_CACHE . '___timezone.txt' ) ) {
			_eb_create_file( EB_THEME_CACHE . '___timezone.txt', $__cf_row['cf_timezone'] );
		}
		
		
		
		
		/*
		* Danh sách bản dịch
		*/
		$___eb_lang = $___eb_default_lang;
		EBE_get_lang_list();
//		print_r( $___eb_lang );
		
		// -> tạo chuỗi để lưu cache
		foreach ( $___eb_lang as $k => $v ) {
			$__eb_cache_content .= '$___eb_lang[\'' . $k . '\']="' . str_replace ( '"', '\"', str_replace ( '$', '\$', $v ) ) . '";';
		}
		
		
		
		
		
		/*
		* Tối ưu thẻ META với mạng xã hội
		*/
		$arr_meta = array();
		
		// social
		if ( $__cf_row ['cf_google_plus'] != '' ) {
			$arr_meta[] = '<meta itemprop="author" content="' .$__cf_row ['cf_google_plus']. '?rel=author" />';
			$arr_meta[] = '<link rel="author" href="' .$__cf_row ['cf_google_plus']. '" />';
			$arr_meta[] = '<link rel="publisher" href="' .$__cf_row ['cf_google_plus']. '" />';
		}
		
		// https://developers.facebook.com/docs/plugins/comments/#settings
		if ( $__cf_row ['cf_facebook_id'] != '' ) {
			$arr_meta[] = '<meta property="fb:app_id" content="' . $__cf_row ['cf_facebook_id'] . '" />';
		}
		else if ( $__cf_row ['cf_facebook_admin_id'] != '' ) {
			// v2 -> fb có ghi chú, chỉ sử dụng 1 trong 2 (fb:app_id hoặc fb:admins)
			$fb_admins = explode( ',', $__cf_row ['cf_facebook_admin_id'] );
			foreach ( $fb_admins as $v ) {
				$v = trim( $v );
				if ( $v != '' ) {
					$arr_meta[] = '<meta property="fb:admins" content="' . $v . '" />';
				}
			}
			
			// v1
//			$arr_meta[] = '<meta property="fb:admins" content="' .$__cf_row ['cf_facebook_admin_id']. '" />';
		}
		
		/* https://developers.facebook.com/tools/debug/og/object/
		if ( $__cf_row ['cf_facebook_page'] != '' ) {
			$arr_meta[] = '<meta property="article:publisher" content="' . $__cf_row ['cf_facebook_page'] . '" />';
			$arr_meta[] = '<meta property="article:author" content="' . $__cf_row ['cf_facebook_page'] . '" />';
		}
		*/
		
		// seo local
		if ( $__cf_row ['cf_region'] != '' ) {
			$arr_meta[] = '<meta name="geo.region" content="' . $__cf_row ['cf_region'] . '" />';
		}
		
		if ( $__cf_row ['cf_placename'] != '' ) {
			$arr_meta[] = '<meta name="geo.placename" content="' . $__cf_row ['cf_placename'] . '" />';
		}
		
		if ( $__cf_row ['cf_position'] != '' ) {
			$arr_meta[] = '<meta name="geo.position" content="' . $__cf_row ['cf_position'] . '" />';
			$arr_meta[] = '<meta name="ICBM" content="' . $__cf_row ['cf_position'] . '" />';
		}
		
		//
//		print_r( $arr_meta );
		$dynamic_meta = '';
		/*
		foreach ( $arr_meta as $v ) {
			$dynamic_meta .= $v . "\n";
		}
		*/
		$dynamic_meta .= implode( "\n", $arr_meta );
		
		// save
		$__eb_cache_content .= '$dynamic_meta="' . str_replace( '"', '\"', $dynamic_meta ) . '";';
		
		
		
		
		/*
		* ID và tài khoản MXH
		*/
		$add_data_id = array (
//			'web_name' => '\'' . $__cf_row ['web_name'] . '\'',
//			'service_name' => '\'' . $service_name . '\'',
			
			'eb_disable_auto_get_thumb' => (int) $__cf_row ['cf_disable_auto_get_thumb'],
			
			'cf_facebook_page' => '\'' . str_replace( '/', '\/', $__cf_row ['cf_facebook_page'] ) . '\'',
			'__global_facebook_id' => '\'' . $__cf_row ['cf_facebook_id'] . '\'',
			'cf_instagram_page' => '\'' . str_replace( '/', '\/', $__cf_row ['cf_instagram_page'] ) . '\'',
			'cf_google_plus' => '\'' . str_replace( '/', '\/', $__cf_row ['cf_google_plus'] ) . '\'',
			'cf_youtube_chanel' => '\'' . str_replace( '/', '\/', $__cf_row ['cf_youtube_chanel'] ) . '\'',
			'cf_twitter_page' => '\'' . str_replace( '/', '\/', $__cf_row ['cf_twitter_page'] ) . '\'' 
		);
		$cache_data_id = '';
		foreach ( $add_data_id as $k => $v ) {
			$cache_data_id .= ',' . $k . '=' . $v;
		}
		$cache_data_id = substr ( $cache_data_id, 1 );
		$__eb_cache_content .= '$cache_data_id="' . $cache_data_id . '";';
		
		
		
		
		
		// danh sách menu đã được đăng ký
		$menu_locations = get_nav_menu_locations();
//		print_r($menu_locations);
		foreach ( $menu_locations as $k => $v ) {
			$__eb_cache_content .= '$menu_cache_locations[\'' . $k . '\']="' . $v . '";';
		}
		
		
		
		
		
		/*
		* lưu cache -> chỉ lưu khi thành viên chưa đăng nhập
		*/
		/*
		echo '<!-- aaaaaaaaaaaaa -->' . "\n";
		echo '<!-- ' . mtv_id . ' -->' . "\n";
		echo '<!-- ' . $__eb_cache_conf . ' (??????) -->' . "\n";
		if ( ! file_exists( $__eb_cache_conf ) ) {
			echo '<!-- file not exist -->' . "\n";
		}
		else {
			echo '<!-- file exist -->' . "\n";
		}
		if ( ! function_exists('file_exists') ) {
			echo '<!-- function not exists -->' . "\n";
		}
		*/
		// không cho tạo cache liên tục
		// chỉ tạo khi khách truy cập hoặc không có file
		if ( mtv_id == 0 || ! file_exists( $file_last_update ) || ! file_exists( $__eb_cache_conf ) ) {
			// tạo file cache
			_eb_create_file ( $file_last_update, date_time );
			
//			echo '<!-- ' . $__eb_cache_conf . ' (!!!!!) -->' . "\n";
			_eb_create_file ( $__eb_cache_conf, '<?php ' . str_replace( '\\\"', '\"', $__eb_cache_content ) );
			
			//
			_eb_log_user ( 'Update common_cache: ' . $_SERVER ['REQUEST_URI'] );
			
			// thông báo log cho admin
			if ( $error_admin_log_cache != '' ) {
				_eb_log_admin( $error_admin_log_cache );
			}
		}
		
		
		
		
		
		
		/*
		* tạo các page tự động nếu chưa có
		*/
		/*
		$strCacheFilter = 'auto_create_page';
		$check_Cleanup_cache = _eb_get_static_html ( $strCacheFilter, '', '', 3600 );
		if ($check_Cleanup_cache == false) {
//			_eb_create_page( 'cart', 'Giỏ hàng' );
			
//			_eb_create_page( 'contact', 'Liên hệ' );
			
//			_eb_create_page( 'landing-page', 'Landing page', 'templates/full-width.php' );
			
//			_eb_create_page( 'process', 'Process...' );
			
//			_eb_create_page( 'hoan-tat', 'Đặt hàng thành công' );
			
//			_eb_create_page( 'profile', 'Trang cá nhân' );
			
//			_eb_create_page( 'sitemap', 'Sitemap' );
			
			// ép lưu cache
			_eb_get_static_html ( $strCacheFilter, date( 'r', date_time ), '', 60 );
		}
		*/
		
		
		
		
		
		
		/*
		* Tự động dọn dẹp log sau một khoảng thời gian
		*/
		/*
		$strCacheFilter = 'auto_clean_up_log';
		$check_Cleanup_cache = _eb_get_static_html ( $strCacheFilter, '', '', 24 * 3600 );
		if ( $check_Cleanup_cache == false ) {
			// user log
			$a = _eb_get_log_user( " LIMIT 2000, 1 " );
//			print_r($a);
//			echo count($a);
			
			//
			if ( count($a) > 0 ) {
				$sql = "DELETE FROM
					`" . wp_postmeta . "`
				WHERE
					post_id = " . eb_log_user_id_postmeta . "
					AND meta_key = '__eb_log_user'
					AND meta_id < " . $a[0]->meta_id;
//				echo $sql . '<br>';
				
				//
				_eb_q($sql);
			}
			
			
			
			
			// admin log
			$a = _eb_get_log_admin( " LIMIT 2000, 1 " );
//			print_r($a);
//			echo count($a);
			
			//
			if ( count($a) > 0 ) {
				$sql = "DELETE FROM
					`" . wp_postmeta . "`
				WHERE
					post_id = " . eb_log_user_id_postmeta . "
					AND meta_key = '__eb_log_admin'
					AND meta_id < " . $a[0]->meta_id;
//				echo $sql . '<br>';
				
				//
				_eb_q($sql);
			}
			
			
			
			
			// Lưu thời gian dọn log
			_eb_get_static_html ( $strCacheFilter, date( 'r', date_time ), '', 60 );
		}
		*/
		
		
		
		
		// Xóa revision
		include_once EB_THEME_PLUGIN_INDEX . 'cronjob/revision_cleanup.php';
		
		// số bài viết tối đa trên web
		include_once EB_THEME_PLUGIN_INDEX . 'cronjob/max_post_cleanup.php';
		
		
		
		
	}
}





// chuyển đơn vị tiền tệ từ sau ra trước
$custom_css_for_price = '';
if ( $__cf_row['cf_current_price_before'] == 1 ) {
	
	//
	$custom_css_for_price .= '.ebe-currency:after{display:none}.ebe-currency:before{display:inline-block}';
	
	
	// đổi đơn vị tiền tệ
	if ( $__cf_row['cf_current_price'] != '' ) {
		$custom_css_for_price .= '.ebe-currency:before{content:"' . str_replace( '/', '\\', $__cf_row['cf_current_price'] ) . '"}';
	}
}
// đổi đơn vị tiền tệ
else if ( $__cf_row['cf_current_price'] != '' ) {
	$custom_css_for_price .= '.ebe-currency:after{content:"' . str_replace( '/', '\\', $__cf_row['cf_current_price'] ) . '"}';
}
$__cf_row['cf_default_css'] .= $custom_css_for_price;



// tạo mặt nạ cho nội dung
/*
if ( $__cf_row['cf_set_mask_for_details'] == 1 ) {
	$__cf_row['cf_default_css'] .= '.thread-content-mask{right:0;bottom:0;display:block}';
}
*/



// chỉnh lại CSS cho phần thread-home-c2
if ( $__cf_row['cf_home_sub_cat_tag'] != '' ) {
	//
//	$__cf_row['cf_default_css'] .= '.thread-home-c2 a{color:' . $__cf_row['cf_default_color'] . '}.thread-home-c2 ' . $__cf_row['cf_home_sub_cat_tag'] . ':first-child{background-color:' . $__cf_row['cf_default_bg'] . '}.thread-home-c2 a:first-child {background:none !important}';
	
	//
	$__cf_row['cf_default_css'] = str_replace( '.thread-home-c2 a:first-child', '.thread-home-c2 ' . $__cf_row['cf_home_sub_cat_tag'] . ':first-child', $__cf_row['cf_default_css'] );
}




// thiết lập chiều rộng cho các module riêng lẻ
if ( $__cf_row['cf_blog_class_style'] != '' ) {
	if ( $__cf_row['cf_top_class_style'] == '' ) {
		$__cf_row['cf_top_class_style'] = $__cf_row['cf_blog_class_style'];
	}
	
	if ( $__cf_row['cf_footer_class_style'] == '' ) {
		$__cf_row['cf_footer_class_style'] = $__cf_row['cf_blog_class_style'];
	}
	
	if ( $__cf_row['cf_cats_class_style'] == '' ) {
		$__cf_row['cf_cats_class_style'] = $__cf_row['cf_blog_class_style'];
	}
	
	if ( $__cf_row['cf_post_class_style'] == '' ) {
		$__cf_row['cf_post_class_style'] = $__cf_row['cf_blog_class_style'];
	}
	
	if ( $__cf_row['cf_blogs_class_style'] == '' ) {
		$__cf_row['cf_blogs_class_style'] = $__cf_row['cf_blog_class_style'];
	}
	
	if ( $__cf_row['cf_blogd_class_style'] == '' ) {
		$__cf_row['cf_blogd_class_style'] = $__cf_row['cf_blog_class_style'];
	}
}




// cập nhật web version định kỳ
$auto_update_web_version = EB_THEME_CACHE . 'web_version_auto.txt';

//
$last_update_web_version = 0;
if ( file_exists( $auto_update_web_version ) ) {
	$last_update_web_version = filemtime( $auto_update_web_version );
}

//
if ( date_time - $last_update_web_version + rand( 0, 60 ) > 600 ) {
	_eb_set_config( 'cf_web_version', date( 'md.Hi', date_time ), 0 );
	
	_eb_create_file( $auto_update_web_version, date( 'r', date_time ) . ' - ' . date( 'r', $last_update_web_version ) );
}




//
//print_r( $__cf_row );
//print_r( $___eb_lang );


// TEST
//echo _eb_get_full_category_v2 ( 0, 'category', 1 );
/*
if ( mtv_id == 1 && is_home() ) {
	include_once EB_THEME_PLUGIN_INDEX . 'cronjob/revision_cleanup.php';
}
*/


