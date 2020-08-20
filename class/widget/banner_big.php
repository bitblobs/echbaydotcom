<?php



/*
* Widget blog ngẫu nhiên
*/
class ___echbay_widget_banner_big extends WP_Widget {
	function __construct() {
		parent::__construct ( 'echbay_banner_big', 'zEchBay Big Banner', array (
				'description' => 'Nhúng banner loại lớn vào website' 
		) );
	}
	
	function form($instance) {
		$default = array (
			'title' => 'Big Banner',
			'width' => '',
			'custom_style' => '',
			'full_mobile' => '',
			'hide_mobile' => '',
			'cat_ids' => 0,
			'for_home' => ''
		);
		$instance = wp_parse_args ( ( array ) $instance, $default );
		foreach ( $instance as $k => $v ) {
			$$k = esc_attr ( $v );
		}
		
		//
		$arr_field_name = array();
		foreach ( $default as $k => $v ) {
			$arr_field_name[ $k ] = $this->get_field_name ( $k );
		}
		
		
		// form dùng chung cho phần top, footer
		_eb_top_footer_form_for_widget( $instance, $arr_field_name );
		
		
		
		//
		__eb_widget_load_cat_select ( array(
			'cat_ids_name' => $this->get_field_name ( 'cat_ids' ),
			'cat_ids' => $cat_ids,
			'cat_type_name' => $this->get_field_name ( 'cat_type' ),
			'cat_type' => 'post_options'
		), 'post_options', false );
		
		echo '<p class="small">* <em>Khi muốn lấy banner trong một <strong>post_options</strong> cụ thể nào đó, thì có thể chọn tại đây. Câu lệnh chỉ lấy các banner có trạng thái là: <strong>1</strong></em></p>';
		
		
		//
		_eb_widget_echo_widget_input_checkbox( $arr_field_name['for_home'], $for_home, 'For home', 'Khi sử dụng template page, chức năng load big banner không hoạt động, khi đó cần phải kích hoạt chức năng này để nó có thể lấy big banner theo tiêu chuẩn mặc định.' );
		
	}
	
	function update($new_instance, $old_instance) {
		$instance = _eb_widget_parse_args ( $new_instance, $old_instance );
		return $instance;
	}
	
	function widget($args, $instance) {
		global $str_big_banner;
		
		extract ( $args );
		
//		$title = apply_filters ( 'widget_title', $instance ['title'] );
		$title = isset( $instance ['title'] ) ? $instance ['title'] : '';
		$width = isset( $instance ['width'] ) ? $instance ['width'] : '';
		if ( $width != '' ) $width .= ' lf';
		
		$custom_style = isset( $instance ['custom_style'] ) ? $instance ['custom_style'] : '';
		
		$hide_mobile = isset( $instance ['hide_mobile'] ) ? $instance ['hide_mobile'] : 'off';
//		$hide_mobile = $hide_mobile == 'on' ? ' hide-if-mobile' : '';
		if ( $hide_mobile == 'on' ) $width .= ' hide-if-mobile';
		
		$full_mobile = isset( $instance ['full_mobile'] ) ? $instance ['full_mobile'] : 'off';
		if ( $full_mobile == 'on' ) $width .= ' fullsize-if-mobile';
		
		$cat_ids = isset( $instance ['cat_ids'] ) ? $instance ['cat_ids'] : 0;
		
		$for_home = isset( $instance ['for_home'] ) ? $instance ['for_home'] : 'off';
		
		
		//
//		_eb_echo_widget_name( $this->name, $before_widget );
		echo '<!-- ' . $this->name . ' -->';
		
		
		// nếu có lấy theo ID của nhóm -> lấy luôn theo ID nhóm đó
//		echo $cat_ids;
		if ( $cat_ids > 0 ) {
			// tắt chế độ lấy theo trang chủ
//			$for_home = 'off';
			
			//
			$str_big_banner = EBE_get_big_banner( EBE_get_lang('bigbanner_num'), array(
				'tax_query' => array(
					array (
						'taxonomy' => 'post_options',
						'field' => 'term_id',
						'terms' => $cat_ids,
						'operator' => 'IN'
					)
				)
			) );
		}
		
		
		//
		if ( $str_big_banner == '' ) {
			if ( $for_home == 'on' ) {
				global $__cf_row;
				
				if ( $__cf_row['cf_global_big_banner'] != 1 ) {
					$str_big_banner = EBE_get_big_banner( EBE_get_lang('bigbanner_num'), array(
						'category__not_in' => ''
					) );
				}
				
				//
				if ( $str_big_banner == '' ) {
					return false;
				}
			}
			else {
				return false;
			}
		}
		
		//
		echo '<div class="' . str_replace( '  ', ' ', trim( 'top-footer-css ' . $width ) ) . '">';
		
		//
//		_eb_echo_widget_title( $title, 'echbay-widget-blogs-title', $before_title );
		
		
		//
//		echo '<div class="' . str_replace( '  ', ' ', trim( 'oi_big_banner ' . $custom_style ) ) . '">' . $str_big_banner . '</div>';
		echo '<div class="' . $custom_style . '">' . $str_big_banner . '</div>';
		
		
		//
		echo '</div>';
		
		//
//		echo $after_widget;
	}
}




