<?php
/* WooCommerce skin-specific functions
------------------------------------------------------------------------------- */


/* Skin-specific WooCommerce utils
------------------------------------------------------------------- */

// Theme init priorities:
// 3 - add/remove Theme Options elements
if ( ! function_exists( 'edifice_woocommerce_skin_theme_setup3' ) ) {
	add_action( 'after_setup_theme', 'edifice_woocommerce_skin_theme_setup3', 3 );
	function edifice_woocommerce_skin_theme_setup3() {
		if ( edifice_exists_woocommerce() ) {
			// Panel 'Shop' with skin-specific options
			// Add color_sheme
			edifice_storage_set_array_after( 'options', 'shop_general', edifice_options_get_list_cpt_options_color( 'shop', esc_html__( 'Product', 'edifice' ) ) );
			edifice_storage_set_array_after( 'options', 'shop_single', edifice_options_get_list_cpt_options_body( 'shop', esc_html__( 'Product', 'edifice' ), 'single' ) );
			// Hide 'shop_mode'
			edifice_storage_set_array2( 'options', 'shop_mode', 'type', 'hidden' );
			// Hide 'single_product_gallery_thumbs'
			edifice_storage_set_array2( 'options', 'single_product_gallery_thumbs', 'std', 'left' );
			edifice_storage_set_array2( 'options', 'single_product_gallery_thumbs', 'type', 'hidden' );
			// Remove hover 'shop_buttons'
			edifice_storage_set_array2( 'options', 'shop_hover', 'std', 'shop' );
			edifice_storage_set_array2( 'options', 'shop_hover', 'options', apply_filters( 'edifice_filter_shop_hover', array(
				'none' => esc_html__( 'None', 'edifice' ),
				'shop' => esc_html__( 'Icons', 'edifice' ),
				)
			) );
		}
	}
}


// Theme init priorities:
// Remove\Register Action\filters
if ( ! function_exists( 'edifice_woocommerce_skin_woocommerce_remove_action' ) ) {
	add_action( 'init', 'edifice_woocommerce_skin_woocommerce_remove_action', 11 );
	function edifice_woocommerce_skin_woocommerce_remove_action() {
		if ( edifice_exists_woocommerce() ) {

			add_filter( 'edifice_filter_woocommerce_sale_flash', 'edifice_change_woocommerce_sale_flash', 10, 3 );

            remove_action( 'woocommerce_get_price_html', 'edifice_woocommerce_get_price_html' );

            remove_action( 'woocommerce_loop_add_to_cart_link', 'edifice_woocommerce_add_to_cart_link', 10 );
			add_filter( 'woocommerce_loop_add_to_cart_link', 'edifice_woocommerce_skin_add_to_cart_link', 10, 2 );

			remove_action( 'woocommerce_before_shop_loop', 'edifice_woocommerce_before_shop_loop', 10 );

			remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
			add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 30);

			add_action('woocommerce_after_add_to_cart_button', 'edifice_woocommerce_add_wishlist');

			remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
			add_action('woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 4);

			// Status Bar
			add_action('woocommerce_before_cart', 'woocommerce_show_product_status_bar');
			add_action('woocommerce_before_checkout_form', 'woocommerce_show_product_status_bar');
			add_action('woocommerce_before_thankyou', 'woocommerce_show_product_status_bar');
		}
	}
}

// Status Bar
if ( ! function_exists( 'woocommerce_show_product_status_bar' ) ) {
	function woocommerce_show_product_status_bar() {
		if (is_cart() || is_checkout()) { ?>
			<div class="woocommerce_status_bar">
				<div class="bar_cart active"><span class="num">1</span><?php esc_html_e('Shopping Cart', 'edifice'); ?></div>
				<div class="bar_payment<?php echo esc_attr(is_checkout() ? ' active': ''); ?>"><span class="num">2</span><?php esc_html_e('Payment & Delivery Options', 'edifice'); ?></div>
				<div class="bar_order"><span class="num">3</span><?php esc_html_e('Order Received', 'edifice'); ?></div>
			</div>
		<?php
		}
	}
}


// Add WooCommerce-specific classes to the body
if ( ! function_exists( 'edifice_woocommerce_skin_add_body_classes' ) ) {
	add_filter( 'body_class', 'edifice_woocommerce_skin_add_body_classes' );
	function edifice_woocommerce_skin_add_body_classes( $classes ) {
		if ( function_exists( 'edifice_woocommerce_extensions_frontend_scripts' ) ) {
			$classes[] = 'woo_extensions_present';
		}
		return $classes;
	}
}


// Show/Hide title
if ( ! function_exists( 'edifice_woocommerce_skin_show_title' ) ) {
	add_filter( 'edifice_filter_show_woocommerce_title', 'edifice_woocommerce_skin_show_title' );
	function edifice_woocommerce_skin_show_title( $show ) {
		$tpl = edifice_storage_get('extended_products_tpl');
		if ( !empty($tpl) && ('info' == $tpl || 'info_2' == $tpl) ) {
			$show = true;
		} else {
			$show = false;
		}
		return $show;
	}
}


// Wrap 'Add to cart' button
if ( ! function_exists( 'edifice_woocommerce_skin_add_to_cart_link' ) ) {
	//Handler of the add_filter( 'woocommerce_loop_add_to_cart_link', 'edifice_woocommerce_skin_add_to_cart_link', 10, 2 );
	function edifice_woocommerce_skin_add_to_cart_link( $html, $product = '', $args = array() ) {
		$tpl = edifice_storage_get('extended_products_tpl');
		if ( isset($tpl) && 'simple' == $tpl ) {
			return sprintf( '<div class="add_to_cart_wrap">%s</div>', $html );
		} else if (is_object( $product ) && isset($tpl) && ('plain' == $tpl || 'pure' == $tpl))  { ?>
			<div class="add_to_cart_wrap">
						<?php
						$price = get_post_meta( get_the_ID(), '_price', true );
						edifice_show_layout(
							'<a rel="nofollow" href="' . esc_url($product->add_to_cart_url()) . '" 
													aria-hidden="true" 
													data-quantity="1" 
													data-product_id="' . esc_attr($product->is_type('variation') ? $product->get_parent_id() : $product->get_id()) . '"
													data-product_sku="' . esc_attr($product->get_sku()) . '"
													class="shop_cart icon-shopping-cart button add_to_cart_button'
							. ' product_type_' . $product->get_type()
							. ' product_' . ($product->is_purchasable() && $product->is_in_stock() ? 'in' : 'out') . '_stock'
							. ($product->supports('ajax_add_to_cart') ? ' ajax_add_to_cart' : '')
							. '">'
							. ( $product->is_type('variable') 
								? 
									esc_html__('Select Options', 'edifice')
								:
								( $product->is_in_stock() && $price
									? 
										esc_html__('Add to Cart', 'edifice')
									:
										esc_html__('Read More', 'edifice')
									)
								)
							. '</a>'
						);
						?>
					</div>
					<?php		
		} else if (isset($tpl) && 'hovered' == $tpl)  {
			return false;
		} else {
			return edifice_is_off( edifice_get_theme_option( 'shop_hover' ) ) ? sprintf( '<div class="add_to_cart_wrap">%s</div>', $html ) : $html;
		}
	}
}


if ( ! function_exists( 'edifice_woocommerce_add_wishlist' ) ) {
	function edifice_woocommerce_add_wishlist() {
		if (function_exists('edifice_exists_wishlist') && edifice_exists_wishlist()) {
			edifice_show_layout(do_shortcode("[ti_wishlists_addtowishlist]"));
		}
	}
}


/* Add parameter 'Product style' to the shop page settings
------------------------------------------------------------------- */

// Theme init priorities:
// 3 - add/remove Theme Options elements
if ( ! function_exists( 'edifice_woocommerce_extensions_add_product_style_theme_setup3' ) ) {
	add_action( 'after_setup_theme', 'edifice_woocommerce_extensions_add_product_style_theme_setup3', 3 );
	function edifice_woocommerce_extensions_add_product_style_theme_setup3() {
		if ( edifice_exists_woocommerce() ) {
			// Add parameter to the theme-specific options
			edifice_storage_set_array_after( 'options', 'shop_mode', apply_filters( 'edifice_filter_woocommerce_extensions_add_product_style_args', array(
				'product_style' => array(
					'title'      => esc_html__( 'Product style', 'edifice' ),
					'desc'       => wp_kses_data( __( 'Style of product items on the shop page.', 'edifice' ) ),
					'std'     => 'default',
					'options' => array(),
					'type'    => 'select',
				),
			) ) );
		}
	}
}


// Return lists with choises when its need in the admin mode
if ( ! function_exists( 'edifice_woocommerce_extensions_add_product_style_get_list_choises' ) ) {
	add_filter( 'edifice_filter_options_get_list_choises', 'edifice_woocommerce_extensions_add_product_style_get_list_choises', 10, 2 );
	function edifice_woocommerce_extensions_add_product_style_get_list_choises( $list, $id ) {
		if ( is_array( $list ) && count( $list ) == 0 ) {
			if ( strpos( $id, 'product_style' ) === 0 && function_exists( 'trx_addons_woocommerce_extended_products_get_list_styles' ) ) {
				$list = trx_addons_woocommerce_extended_products_get_list_styles();
			}
		}
		return $list;
	}
}


// Substitute default template in the products loop with selected in Theme Options
if ( ! function_exists( 'edifice_woocommerce_extensions_add_product_style_wc_get_template_part' ) ) {
	add_filter( 'wc_get_template_part', 'edifice_woocommerce_extensions_add_product_style_wc_get_template_part', 200, 3 );
	function edifice_woocommerce_extensions_add_product_style_wc_get_template_part( $template, $slug, $name ) {
		if ( $slug == 'content' && $name == 'product'
			&& function_exists( 'trx_addons_woocommerce_extended_products_get_layouts' )
			&& ( ! function_exists( 'trx_addons_sc_stack_check' ) || ! trx_addons_sc_stack_check( 'trx_sc_extended_products' ) )
			&& ! is_product()
		) {
			$style = edifice_get_theme_option( 'product_style' );
			if ( 'default' != $style ) {
				$layouts = trx_addons_woocommerce_extended_products_get_layouts();
				if ( isset( $layouts[ $style ] ) && ! empty( $layouts[ $style ]['template'] ) ) {
					$template = $layouts[ $style ]['template'];
				}
			}
		}
		return $template;
	}
}


// add products layouts
if ( !function_exists( 'edifice_add_woocommerce_products_layouts' ) ) {
	add_filter('trx_addons_filter_woocommerce_products_layouts', 'edifice_add_woocommerce_products_layouts');
	function edifice_add_woocommerce_products_layouts() {
		$arr = array(
				'default' => array(
					'title' => esc_html__( 'Default', 'edifice' ),
					'template' => ''
				),
				'centered' => array(
					'title' => esc_html__( 'Centered', 'edifice' ),
					'template' => ''
				),
				'simple' => array(
					'title' => esc_html__( 'Simple', 'edifice' ),
					'template' => edifice_get_file_dir('woocommerce/content-product-simple.php')
				),
				'hovered' => array(
					'title' => esc_html__( 'Hovered', 'edifice' ),
					'template' => edifice_get_file_dir('woocommerce/content-product-hovered.php')
				),
				'info' => array(
					'title' => esc_html__( 'Info', 'edifice' ),
					'template' => edifice_get_file_dir('woocommerce/content-product-info.php')
				),
				'info_2' => array(
					'title' => esc_html__( 'Info 2', 'edifice' ),
					'template' => edifice_get_file_dir('woocommerce/content-product-info-2.php')
				),
				'creative' => array(
					'title' => esc_html__( 'Creative', 'edifice' ),
					'template' => edifice_get_file_dir('woocommerce/content-product-creative.php')
				),
				'plain' => array(
					'title' => esc_html__( 'Plain', 'edifice' ),
					'template' => edifice_get_file_dir('woocommerce/content-product-plain.php')
				),
				'pure' => array(
					'title' => esc_html__( 'Pure', 'edifice' ),
					'template' => edifice_get_file_dir('woocommerce/content-product-pure.php')
				),
		);
		return $arr;
	}
}


// Add class with a "product style" to the wrap ul.products
// ( if we are not inside a shortcode 'trx_sc_extended_products' )
if ( ! function_exists( 'edifice_woocommerce_extensions_add_product_style_to_products_wrap' ) ) {
	add_filter( 'woocommerce_product_loop_start', 'edifice_woocommerce_extensions_add_product_style_to_products_wrap', 200, 1 );
	function edifice_woocommerce_extensions_add_product_style_to_products_wrap( $template ) {
		if ( function_exists( 'trx_addons_woocommerce_extended_products_get_layouts' )
			&& ( ! function_exists( 'trx_addons_sc_stack_check' ) || ! trx_addons_sc_stack_check( 'trx_sc_extended_products' ) )
			&& ! is_product()	// To prevent add class for the wrap of related products in the single product page
		) {
			$style = edifice_get_theme_option( 'product_style' );
			$new_classes = array(
				sprintf( 'products_style_%s', $style )
			);
			$layouts = trx_addons_woocommerce_extended_products_get_layouts();
			if ( isset( $layouts[ $style ] ) && ! empty( $layouts[ $style ]['products_classes'] ) ) {
				$new_classes = array_merge(
									$new_classes, 
									is_array( $layouts[ $style ]['products_classes'] )
										? $layouts[ $style ]['products_classes']
										: explode( ' ', $layouts[ $style ]['products_classes'] )
									);
			}
			$template = preg_replace( 
									'/(<ul[^>]*class="products )/',
									'$1' . esc_attr( join( ' ', $new_classes ) ) . ' ',
									$template
									);
		}
		return $template;
	}
}


// Add class with a "product style" to each product item
if ( ! function_exists( 'edifice_woocommerce_extensions_add_product_style_to_product_items' ) ) {
	add_filter( 'woocommerce_post_class', 'edifice_woocommerce_extensions_add_product_style_to_product_items', 200, 2 );
	function edifice_woocommerce_extensions_add_product_style_to_product_items( $classes, $product ) {
		if ( function_exists( 'trx_addons_woocommerce_extended_products_get_layouts' )
			&& ( ! function_exists( 'trx_addons_sc_stack_check' ) || ! trx_addons_sc_stack_check( 'trx_sc_extended_products' ) )
			&& ! is_product()	// To prevent add class for the wrap of related products in the single product page
		) {
			if ( is_array( $classes ) ) {
				$style = edifice_get_theme_option( 'product_style' );
				$new_classes = array(
									sprintf( 'product_style_%s', esc_attr( $style ) )
								);
				$layouts = trx_addons_woocommerce_extended_products_get_layouts();
				if ( isset( $layouts[ $style ] ) && ! empty( $layouts[ $style ]['product_classes'] ) ) {
					$new_classes = array_merge(
										$new_classes, 
										is_array( $layouts[ $style ]['product_classes'] )
											? $layouts[ $style ]['product_classes']
											: explode( ' ', $layouts[ $style ]['product_classes'] )
										);
				}
				foreach( $new_classes as $c ) {
					$c = trim( $c );
					if ( ! empty( $c ) && ! in_array( $c, $classes ) ) {
						$classes[] = $c;
					}
				}
			}
		}
		return $classes;
	}
}

// Add label "UP TO"
if ( ! function_exists( 'edifice_change_woocommerce_sale_flash' ) ) {
	function edifice_change_woocommerce_sale_flash($new_sale, $percent, $product) {
		if( 'variable' === $product->get_type() ){
			$new_sale = '<span class="onsale"><span class="onsale_up">'. esc_html__('Up to', 'edifice') .'</span> - '. esc_html( $percent ) . '%</span>';
			
		}
		return $new_sale;
	}
}

// Show all body style 
if ( ! function_exists( 'edifice_change_filter_get_list_cpt_options_body' ) ) {
	add_filter( 'edifice_filter_get_list_cpt_options_body', 'edifice_change_filter_get_list_cpt_options_body', 10, 2 );
	function edifice_change_filter_get_list_cpt_options_body($arr, $cpt) {
		if ($cpt == 'shop') {
			$arr['body_style_shop']['options'] = edifice_get_list_body_styles( true, true );
		}	
		return $arr;
	}
}

// Show/Hide attributes
if ( ! function_exists( 'edifice_woocommerce_skin_extensions_show_attributes' ) ) {
	add_filter( 'edifice_filter_woocommerce_extensions_show_attributes', 'edifice_woocommerce_skin_extensions_show_attributes' );
	function edifice_woocommerce_skin_extensions_show_attributes($show) {
		$tpl = edifice_storage_get('extended_products_tpl');
		if ( !empty($tpl) && ('creative' == $tpl || 'plain' == $tpl) ) {
			$show = true;
		} else {
			$show = false;
		}
		return $show;
	}
}