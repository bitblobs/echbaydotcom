<?php
/*
* Mọi code dùng chung cho trang chủ sản phẩm, lấy hay không sẽ dựa vào config của khách
*/



//
$dynamic_meta .= '<link rel="canonical" href="' . web_link . '" />';
$url_og_url = web_link;




// Nếu config không tạo menu -> không load sidebar
if ( $__cf_row['cf_home_column_style'] == '' ) {
	$id_for_get_sidebar = '';
} else {
	$id_for_get_sidebar = 'home_sidebar';
}




// Chỉ lấy banner riêng khi chế độ global không được kích hoạt
if ( $__cf_row['cf_global_big_banner'] != 1 ) {
	$str_big_banner = EBE_get_big_banner( EBE_get_lang('bigbanner_num'), array(
		'category__not_in' => ''
	) );
}




// cache
/*
$strCacheFilter = 'home';
$main_content = _eb_get_static_html ( $strCacheFilter );
if ($main_content == false) {
	*/
	
	
	
	//
	$home_content_top_sidebar = _eb_echbay_get_sidebar( 'home_content_top_sidebar' );
	
	
	
	// lấy template theo từng trang
//	echo EB_THEME_PLUGIN_INDEX . 'html/<br>';
	
//	$main_content = EBE_str_template( 'home.html', $arr_main_content );
//	$html_v2_file = EBE_get_html_file_addon( 'home', $__cf_row['cf_home_column_style'] );
	$html_v2_file = 'home';
	$custom_home_flex_css = EBE_get_html_file_addon( 'home', $__cf_row['cf_home_column_style'] );
	
	/*
	* Gắn widget vào trước
	*/
	$main_content = EBE_html_template( EBE_get_page_template( $html_v2_file ), array(
		'tmp.home_content_top_sidebar' => $home_content_top_sidebar
	) );
	
	
	
	
	
	/*
	* Những cái khác gắn sau -> nếu có code riêng thì sẽ không bị ảnh hưởng
	*/
	$arr_main_content = array(
		'tmp.cf_home_class_style' => $__cf_row ['cf_home_class_style'],
		'tmp.home_cf_title' => $__cf_row ['cf_title'],
		'tmp.custom_home_flex_css' => $custom_home_flex_css,
	);
	
	// tìm và tạo sidebar luôn
//	$arr_main_content['tmp.str_sidebar'] = _eb_echbay_sidebar( $id_for_get_sidebar );
	
	
	// lấy HTML riêng của từng site
	if ( function_exists('eb_home_for_current_domain') ) {
		$arr_main_new_content = eb_home_for_current_domain();
		
		// -> chạy vòng lặp, ghi đè lên mảng cũ
		foreach ( $arr_main_new_content as $k => $v ) {
			$arr_main_content[$k] = $v;
		}
	}
	
	
	//
	$main_content = EBE_arr_tmp( $arr_main_content, $main_content, '' );
	
	
	
	// lưu cache
	/*
	_eb_get_static_html ( $strCacheFilter, $main_content );
	

} // end cache
*/




// loại bỏ chức năng bỏ qua sản phẩm đã lấy, để custom code còn hoạt động được
$___eb_post__not_in = '';





