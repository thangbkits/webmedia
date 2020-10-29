<?php


// Add custom Theme Functions here
add_filter( 'gettext', 'change_woocommerce_text' );
function change_woocommerce_text( $translated )
{
    $translated = str_replace( 'WooCommerce', 'Quản lý đơn hàng', $translated );
    return $translated;
}
// Remove the product description Title
add_filter( 'woocommerce_product_description_heading', 'remove_product_description_heading' );
function remove_product_description_heading() {
    return '';
}
// add svg
function add_file_types_to_uploads($file_types){
    $new_filetypes = array();
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types = array_merge($file_types, $new_filetypes );
    return $file_types;
}
add_filter('upload_mimes', 'add_file_types_to_uploads');

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_postcode']);
    return $fields;
}

// add_action( 'phpmailer_init', function( $phpmailer ) {
//     if ( !is_object( $phpmailer ) )
//         $phpmailer = (object) $phpmailer;
//     $phpmailer->Mailer     = 'smtp';
//     $phpmailer->Host       = 'smtp.gmail.com';
//     $phpmailer->SMTPAuth   = 1;
//     $phpmailer->Port       = 587;
//     $phpmailer->Username   = 'daphongthuyhanoi.com@gmail.com';
//     $phpmailer->Password   = 'kjutcdofgxtbpfph';
//     $phpmailer->SMTPSecure = 'TLS';
//     $phpmailer->From       = 'daphongthuyhanoi.com@gmail.com';
//     $phpmailer->FromName   = 'Thiết kế website - Webwp.vn';
// });
// 
add_action( 'phpmailer_init', function( $phpmailer ) {
  if ( !is_object( $phpmailer ) )
  $phpmailer = (object) $phpmailer;
  $phpmailer->Mailer     = 'smtp';
  $phpmailer->Host       = 'smtp.gmail.com';
  $phpmailer->SMTPAuth   = 1;
  $phpmailer->Port       = 587;
  $phpmailer->Username   = 'wpemailsystem@gmail.com';
  $phpmailer->Password   = 'qkazntcqeufxjpfc';
  $phpmailer->SMTPSecure = 'TLS';
  $phpmailer->From       = 'wpemailsystem@gmail.com';
  $phpmailer->FromName   = 'Thông báo từ Website';
});


// Option ACF
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page();

}
//add_filter('acf/settings/show_admin', '__return_false');
if (function_exists('acf_set_options_page_menu')){
    acf_set_options_page_menu('Thông tin chung');
}


//Remove Default WordPress Image Sizes
function svl_remove_default_image_sizes( $sizes) {
    unset( $sizes['thumbnail']);
    unset( $sizes['medium']);
    unset( $sizes['large']);

    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'svl_remove_default_image_sizes');
add_filter('intermediate_image_sizes_advanced','__return_false');


// Nen anh dong bo - chonweb.net
add_theme_support( 'post-thumbnails' );
add_image_size( 'newsbox-thumb', 520, 9999 ); // masonry news box-images =260px (520 retina) and unlimited height
add_image_size( 'fprelease-thumb', 112, 9999 ); // fprelese feed logo, 56px (112px retina)
add_filter( 'jpeg_quality', create_function( '$quality', 'return 100;' ) );
add_action( 'added_post_meta', 'ad_update_jpeg_quality', 10, 4 );
function ad_update_jpeg_quality( $meta_id, $attach_id, $meta_key, $attach_meta ) {
    if ( $meta_key != '_wp_attachment_metadata' )
        return false;
    if ( ! $post = get_post( $attach_id ) )
        return false;
    if ( 'image/jpeg' != $post->post_mime_type )
        return false;
    $original = array(
        'original' => array(
            'file' => $attach_meta['file'],
            'width' => $attach_meta['width'],
            'height' => $attach_meta['height']
        )
    );
    $sizes = !empty( $attach_meta['sizes'] ) && is_array( $attach_meta['sizes'] )
        ? $attach_meta['sizes']
        : array();
    $sizes = array_merge( $sizes, $original );
    $pathinfo = pathinfo( $attach_meta['file'] );
    $uploads = wp_upload_dir();
    $dir = $uploads['basedir'] . '/' . $pathinfo['dirname'];
    foreach ( $sizes as $size => $value ) {
        $image = 'original' == $size
            ? $uploads['basedir'] . '/' . $value['file']
            : $dir . '/' . $value['file'];
        $resource = imagecreatefromjpeg( $image );
        if ( $size == 'original' )
            $q = 75; // quality for the original image
        elseif ( $size == 'newsbox-thumb' )
            $q = 65;
        elseif ( $size == 'fprelease-thumb' )
            $q = 85;
        else
            $q = 80;
        imagejpeg( $resource, $image, $q );
        imagedestroy( $resource );
    }
}







add_filter( 'woocommerce_product_single_add_to_cart_text' , 'woo_custom_cart_button_text');


function woo_custom_cart_button_text() {
    $Book_Now = " Mua ngay";
    return __($Book_Now, 'woocommerce');
}

//Bao mat - chonweb.net
function change_footer_admin () {return ' ';}
add_filter('admin_footer_text', 'change_footer_admin', 9999);
function change_footer_version() {return ' ';}
add_filter( 'update_footer', 'change_footer_version', 9999);


// Hien thi toan bo thuoc tinh sp
add_action( 'save_post', 'auto_add_product_attributes', 50, 3 );
function auto_add_product_attributes( $post_id, $post, $update  ) {

    ## --- Checking --- ##

    if ( $post->post_type != 'product') return; // Only products

    // Exit if it's an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return $post_id;

    // Exit if it's an update
    if( $update )
        return $post_id;

    // Exit if user is not allowed
    if ( ! current_user_can( 'edit_product', $post_id ) )
        return $post_id;

    ## --- The Settings for your product attributes --- ##

    $visible   = ''; // can be: '' or '1'
    $variation = ''; // can be: '' or '1'

    ## --- The code --- ##

    // Get all existing product attributes
    global $wpdb;
    $attributes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies" );

    $position   = 0;  // Auto incremented position value starting at '0'
    $data       = array(); // initialising (empty array)

    // Loop through each exiting product attribute
    foreach( $attributes as $attribute ){
        // Get the correct taxonomy for product attributes
        $taxonomy = 'pa_'.$attribute->attribute_name;
        $attribute_id = $attribute->attribute_id;

        // Get all term Ids values for the current product attribute (array)
        //$term_ids = get_terms(array('taxonomy' => $taxonomy, 'fields' => 'ids'));
        $term_ids = get_terms(array('taxonomy' => $taxonomy, 'fields' => 'ids', 'hide_empty' => false));

        // Get an empty instance of the WC_Product_Attribute object
        $product_attribute = new WC_Product_Attribute();

        // Set the related data in the WC_Product_Attribute object
        $product_attribute->set_id( $attribute_id );
        $product_attribute->set_name( $taxonomy );
        $product_attribute->set_options( $term_ids );
        $product_attribute->set_position( $position );
        $product_attribute->set_visible( $visible );
        $product_attribute->set_variation( $variation );

        // Add the product WC_Product_Attribute object in the data array
        $data[$taxonomy] = $product_attribute;

        $position++; // Incrementing position
    }
    // Get an instance of the WC_Product object
    $product = wc_get_product( $post_id );

    // Set the array of WC_Product_Attribute objects in the product
    $product->set_attributes( $data );

    $product->save(); // Save the product
}



/*

// Them text trươc ard -----------------------------------------
// 1. Show custom input field above Add to Cart

add_action( 'woocommerce_before_add_to_cart_button', 'bbloomer_product_add_on', 9 );

function bbloomer_product_add_on() {
    $value = isset( $_POST['_custom_text_add_on'] ) ? sanitize_text_field( $_POST['_custom_text_add_on'] ) : '';
    echo '<div><label>Nội dung yêu cầu<abbr class="required" title="required">*</abbr></label><p><input name="_custom_text_add_on" value="' . $value . '"></p></div>';
}

// -----------------------------------------
// 2. Throw error if custom input field empty

add_filter( 'woocommerce_add_to_cart_validation', 'bbloomer_product_add_on_validation', 10, 3 );

function bbloomer_product_add_on_validation( $passed, $product_id, $qty ){
   if( isset( $_POST['_custom_text_add_on'] ) && sanitize_text_field( $_POST['_custom_text_add_on'] ) == '' ) {
      wc_add_notice( 'Custom Text Add-On is a required field', 'error' );
      $passed = false;
   }
    return $passed;
}

// -----------------------------------------
// 3. Save custom input field value into cart item data

add_filter( 'woocommerce_add_cart_item_data', 'bbloomer_product_add_on_cart_item_data', 10, 2 );

function bbloomer_product_add_on_cart_item_data( $cart_item, $product_id ){
    if( isset( $_POST['_custom_text_add_on'] ) ) {
        $cart_item['custom_text_add_on'] = sanitize_text_field( $_POST['_custom_text_add_on'] );
    }
    return $cart_item;
}

// -----------------------------------------
// 4. Display custom input field value @ Cart

add_filter( 'woocommerce_get_item_data', 'bbloomer_product_add_on_display_cart', 10, 2 );

function bbloomer_product_add_on_display_cart( $_data, $cart_item ) {
    if ( isset( $cart_item['custom_text_add_on'] ) ){
        $data[] = array(
            'name' => 'Custom Text Add-On',
            'value' => sanitize_text_field( $cart_item['custom_text_add_on'] )
        );
    }
    return $data;
}

// -----------------------------------------
// 5. Save custom input field value into order item meta

add_action( 'woocommerce_add_order_item_meta', 'bbloomer_product_add_on_order_item_meta', 10, 2 );

function bbloomer_product_add_on_order_item_meta( $item_id, $values ) {
    if ( ! empty( $values['custom_text_add_on'] ) ) {
        wc_add_order_item_meta( $item_id, 'Custom Text Add-On', $values['custom_text_add_on'], true );
    }
}

// -----------------------------------------
// 6. Display custom input field value into order table

add_filter( 'woocommerce_order_item_product', 'bbloomer_product_add_on_display_order', 10, 2 );

function bbloomer_product_add_on_display_order( $cart_item, $order_item ){
    if( isset( $order_item['custom_text_add_on'] ) ){
        $cart_item_meta['custom_text_add_on'] = $order_item['custom_text_add_on'];
    }
    return $cart_item;
}

// -----------------------------------------
// 7. Display custom input field value into order emails

add_filter( 'woocommerce_email_order_meta_fields', 'bbloomer_product_add_on_display_emails' );

function bbloomer_product_add_on_display_emails( $fields ) {
    $fields['custom_text_add_on'] = 'Custom Text Add-On';
    return $fields;
}
*/

// Them mo ta cho danh muc sp---------------
// 1. Display field on "Add new product category" admin page

add_action( 'product_cat_add_form_fields', 'bbloomer_wp_editor_add', 10, 2 );

function bbloomer_wp_editor_add() {
    ?>
    <div class="form-field">
        <label for="seconddesc"><?php echo __( 'Second Description', 'woocommerce' ); ?></label>

        <?php
        $settings = array(
            'textarea_name' => 'seconddesc',
            'quicktags' => array( 'buttons' => 'em,strong,link' ),
            'tinymce' => array(
                'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                'theme_advanced_buttons2' => '',
            ),
            'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
        );

        wp_editor( '', 'seconddesc', $settings );
        ?>

        <p class="description"><?php echo __( 'This is the description that goes BELOW products on the category page', 'woocommerce' ); ?></p>
    </div>
    <?php
}

// ---------------
// 2. Display field on "Edit product category" admin page

add_action( 'product_cat_edit_form_fields', 'bbloomer_wp_editor_edit', 10, 2 );

function bbloomer_wp_editor_edit( $term ) {
    $second_desc = htmlspecialchars_decode( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="second-desc"><?php echo __( 'Second Description', 'woocommerce' ); ?></label></th>
        <td>
            <?php

            $settings = array(
                'textarea_name' => 'seconddesc',
                'quicktags' => array( 'buttons' => 'em,strong,link' ),
                'tinymce' => array(
                    'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
                    'theme_advanced_buttons2' => '',
                ),
                'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
            );

            wp_editor( $second_desc, 'seconddesc', $settings );
            ?>

            <p class="description"><?php echo __( 'This is the description that goes BELOW products on the category page', 'woocommerce' ); ?></p>
        </td>
    </tr>
    <?php
}

// ---------------
// 3. Save field @ admin page

add_action( 'edit_term', 'bbloomer_save_wp_editor', 10, 3 );
add_action( 'created_term', 'bbloomer_save_wp_editor', 10, 3 );

function bbloomer_save_wp_editor( $term_id, $tt_id = '', $taxonomy = '' ) {
    if ( isset( $_POST['seconddesc'] ) && 'product_cat' === $taxonomy ) {
        update_woocommerce_term_meta( $term_id, 'seconddesc', esc_attr( $_POST['seconddesc'] ) );
    }
}

// ---------------
// 4. Display field under products @ Product Category pages

add_action( 'woocommerce_after_shop_loop', 'bbloomer_display_wp_editor_content', 5 );

function bbloomer_display_wp_editor_content() {
    if ( is_product_taxonomy() ) {
        $term = get_queried_object();
        if ( $term && ! empty( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) ) ) {
            echo '<p class="term-description">' . wc_format_content( htmlspecialchars_decode( get_woocommerce_term_meta( $term->term_id, 'seconddesc', true ) ) ) . '</p>';
        }
    }
}

// Them category vao title

function ttit_add_element_ux_builder(){
    add_ux_builder_shortcode('title_with_cat', array(
        'name'      => __('Title With Category'),
        'category'  => __('Content'),
        'info'      => '{{ text }}',
        'wrap'      => false,
        'options' => array(
            'ttit_cat_ids' => array(
                'type' => 'select',
                'heading' => 'Categories',
                'param_name' => 'ids',
                'config' => array(
                    'multiple' => true,
                    'placeholder' => 'Select...',
                    'termSelect' => array(
                        'post_type' => 'product_cat',
                        'taxonomies' => 'product_cat'
                    )
                )
            ),
            'style' => array(
                'type'    => 'select',
                'heading' => 'Style',
                'default' => 'normal',
                'options' => array(
                    'normal'      => 'Normal',
                    'center'      => 'Center',
                    'bold'        => 'Left Bold',
                    'bold-center' => 'Center Bold',
                ),
            ),
            'text' => array(
                'type'       => 'textfield',
                'heading'    => 'Title',
                'default'    => 'Lorem ipsum dolor sit amet...',
                'auto_focus' => true,
            ),
            'tag_name' => array(
                'type'    => 'select',
                'heading' => 'Tag',
                'default' => 'h3',
                'options' => array(
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                ),
            ),
            'color' => array(
                'type'     => 'colorpicker',
                'heading'  => __( 'Color' ),
                'alpha'    => true,
                'format'   => 'rgb',
                'position' => 'bottom right',
            ),
            'width' => array(
                'type'    => 'scrubfield',
                'heading' => __( 'Width' ),
                'default' => '',
                'min'     => 0,
                'max'     => 1200,
                'step'    => 5,
            ),
            'margin_top' => array(
                'type'        => 'scrubfield',
                'heading'     => __( 'Margin Top' ),
                'default'     => '',
                'placeholder' => __( '0px' ),
                'min'         => - 100,
                'max'         => 300,
                'step'        => 1,
            ),
            'margin_bottom' => array(
                'type'        => 'scrubfield',
                'heading'     => __( 'Margin Bottom' ),
                'default'     => '',
                'placeholder' => __( '0px' ),
                'min'         => - 100,
                'max'         => 300,
                'step'        => 1,
            ),
            'size' => array(
                'type'    => 'slider',
                'heading' => __( 'Size' ),
                'default' => 100,
                'unit'    => '%',
                'min'     => 20,
                'max'     => 300,
                'step'    => 1,
            ),
            'link_text' => array(
                'type'    => 'textfield',
                'heading' => 'Link Text',
                'default' => '',
            ),
            'link' => array(
                'type'    => 'textfield',
                'heading' => 'Link',
                'default' => '',
            ),
        ),
    ));
}
add_action('ux_builder_setup', 'ttit_add_element_ux_builder');

function title_with_cat_shortcode( $atts, $content = null ){
    extract( shortcode_atts( array(
        '_id' => 'title-'.rand(),
        'class' => '',
        'visibility' => '',
        'text' => 'Lorem ipsum dolor sit amet...',
        'tag_name' => 'h3',
        'sub_text' => '',
        'style' => 'normal',
        'size' => '100',
        'link' => '',
        'link_text' => '',
        'target' => '',
        'margin_top' => '',
        'margin_bottom' => '',
        'letter_case' => '',
        'color' => '',
        'width' => '',
        'icon' => '',
    ), $atts ) );
    $classes = array('container', 'section-title-container');
    if ( $class ) $classes[] = $class;
    if ( $visibility ) $classes[] = $visibility;
    $classes = implode(' ', $classes);

    $link_output = '';
    if($link) $link_output = '<a href="'.$link.'" target="'.$target.'">'.$link_text.get_flatsome_icon('icon-angle-right').'</a>';

    $small_text = '';
    if($sub_text) $small_text = '<small class="sub-title">'.$atts['sub_text'].'</small>';

    if($icon) $icon = get_flatsome_icon($icon);

    // fix old
    if($style == 'bold_center') $style = 'bold-center';

    $css_args = array(
        array( 'attribute' => 'margin-top', 'value' => $margin_top),
        array( 'attribute' => 'margin-bottom', 'value' => $margin_bottom),
    );

    if($width) {
        $css_args[] = array( 'attribute' => 'max-width', 'value' => $width);
    }

    $css_args_title = array();

    if($size !== '100'){
        $css_args_title[] = array( 'attribute' => 'font-size', 'value' => $size, 'unit' => '%');
    }

    if($color){
        $css_args_title[] = array( 'attribute' => 'color', 'value' => $color);
    }

    if ( isset( $atts[ 'ttit_cat_ids' ] ) ) {
        $ids = explode( ',', $atts[ 'ttit_cat_ids' ] );
        $ids = array_map( 'trim', $ids );
        $parent = '';
        $orderby = 'include';
    } else {
        $ids = array();
    }

    $args = array(
        'taxonomy' => 'product_cat',
        'include'    => $ids,
        'pad_counts' => true,
        'child_of'   => 0,
    );
    $product_categories = get_terms( $args );
    $hdevvn_html_show_cat = '';
    if ( $product_categories ) {
        foreach ( $product_categories as $category ) {
            $term_link = get_term_link( $category );
            $thumbnail_id = get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true  );
            if ( $thumbnail_id ) {
                $image = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size);
                $image = $image[0];
            } else {
                $image = wc_placeholder_img_src();
            }
            $hdevvn_html_show_cat .= '<li class="hdevvn_cats"><a href="'.$term_link.'">'.$category->name.'</a></li>';
        }
    }
    return '<div class="'.$classes.'" '.get_shortcode_inline_css($css_args).'><'. $tag_name . ' class="section-title section-title-'.$style.'"><b></b><span class="section-title-main" '.get_shortcode_inline_css($css_args_title).'>'.$icon.$text.$small_text.'</span>
      <span class="hdevvn-show-cats">'.$hdevvn_html_show_cat.'</span><b></b>'.$link_output.'</' . $tag_name .'></div><!-- .section-title -->';
}
add_shortcode('title_with_cat', 'title_with_cat_shortcode');

add_filter( 'woocommerce_product_tabs', 'wcs_woo_remove_reviews_tab', 98 );
function wcs_woo_remove_reviews_tab($tabs) {
    unset($tabs['reviews']);
    return $tabs;
}

/*
* Author: Le Van Toan - https://levantoan.com
* Đoạn code thu gọn nội dung bao gồm cả nút xem thêm và thu gọn lại sau khi đã click vào xem thêm
*/
add_action('wp_footer','devvn_readmore_flatsome');
function devvn_readmore_flatsome(){
    ?>
    <style>
        .single-product div#tab-description {
            overflow: hidden;
            position: relative;
            padding-bottom: 25px;
        }
        .single-product .tab-panels div#tab-description.panel:not(.active) {
            height: 0 !important;
        }
        .devvn_readmore_flatsome {
            text-align: center;
            cursor: pointer;
            position: absolute;
            z-index: 10;
            bottom: 0;
            width: 100%;
            background: #fff;
        }
        .devvn_readmore_flatsome:before {
            height: 55px;
            margin-top: -45px;
            content: "";
            background: -moz-linear-gradient(top, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%);
            background: -webkit-linear-gradient(top, rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%);
            background: linear-gradient(to bottom, rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff00', endColorstr='#ffffff',GradientType=0 );
            display: block;
        }
        .devvn_readmore_flatsome a {
            color: #318A00;
            display: block;
        }
        .devvn_readmore_flatsome a:after {
            content: '';
            width: 0;
            right: 0;
            border-top: 6px solid #318A00;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            display: inline-block;
            vertical-align: middle;
            margin: -2px 0 0 5px;
        }
        .devvn_readmore_flatsome_less a:after {
            border-top: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid #318A00;
        }
        .devvn_readmore_flatsome_less:before {
            display: none;
        }
    </style>
    <script>
        (function($){
            $(document).ready(function(){
                $(window).load(function(){
                    if($('.single-product div#tab-description').length > 0){
                        var wrap = $('.single-product div#tab-description');
                        var current_height = wrap.height();
                        var your_height = 500;
                        if(current_height > your_height){
                            wrap.css('height', your_height+'px');
                            wrap.append(function(){
                                return '<div class="devvn_readmore_flatsome devvn_readmore_flatsome_more"><a title="Xem thêm" href="javascript:void(0);">Xem thêm</a></div>';
                            });
                            wrap.append(function(){
                                return '<div class="devvn_readmore_flatsome devvn_readmore_flatsome_less" style="display: none;"><a title="Xem thêm" href="javascript:void(0);">Thu gọn</a></div>';
                            });
                            $('body').on('click','.devvn_readmore_flatsome_more', function(){
                                wrap.removeAttr('style');
                                $('body .devvn_readmore_flatsome_more').hide();
                                $('body .devvn_readmore_flatsome_less').show();
                            });
                            $('body').on('click','.devvn_readmore_flatsome_less', function(){
                                wrap.css('height', your_height+'px');
                                $('body .devvn_readmore_flatsome_less').hide();
                                $('body .devvn_readmore_flatsome_more').show();
                            });
                        }
                    }
                });
            })
        })(jQuery)
    </script>
    <?php
}
// Code đếm số dòng trong văn bản
function count_paragraph( $insertion, $paragraph_id, $content ) {
    $closing_p = '</p>';
    $paragraphs = explode( $closing_p, $content );
    foreach ($paragraphs as $index => $paragraph) {
        if ( trim( $paragraph ) ) {
            $paragraphs[$index] .= $closing_p;
        }
        if ( $paragraph_id == $index + 1 ) {
            $paragraphs[$index] .= $insertion;
        }
    }

    return implode( '', $paragraphs );
}

//Chèn bài liên quan vào giữa nội dung

add_filter( 'the_content', 'prefix_insert_post_nd' );

function prefix_insert_post_nd( $content ) {

    $related_posts= "<div class='meta-related'>".do_shortcode('[related_posts_by_tax title=""]')."</div>";

    if ( is_single() ) {
        return count_paragraph( $related_posts, 1, $content );
    }

    return $content;
}
// Dem lươt xem

function getPostViews($postID, $is_single = true){
    global $post;
    if(!$postID) $postID = $post->ID;
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if(!$is_single){
        return '<span class="svl_show_count_only">'.$count.' lượt xem</span>';
    }
    $nonce = wp_create_nonce('devvn_count_post');
    if($count == "0"){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return '<span class="svl_post_view_count" data-id="'.$postID.'" data-nonce="'.$nonce.'">0 lượt xem</span>';
    }
    return '<span class="svl_post_view_count" data-id="'.$postID.'" data-nonce="'.$nonce.'">'.$count.' lượt xem</span>';
}

function setPostViews($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count == "0" || empty($count) || !isset($count)){
        add_post_meta($postID, $count_key, 1);
        update_post_meta($postID, $count_key, 1);
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}

add_action( 'wp_ajax_svl-ajax-counter', 'svl_ajax_callback' );
add_action( 'wp_ajax_nopriv_svl-ajax-counter', 'svl_ajax_callback' );
function svl_ajax_callback() {
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "devvn_count_post")) {
        exit();
    }
    $count = 0;
    if ( isset( $_GET['p'] ) ) {
        global $post;
        $postID = intval($_GET['p']);
        $post = get_post( $postID );
        if($post && !empty($post) && !is_wp_error($post)){
            setPostViews($post->ID);
            $count_key = 'post_views_count';
            $count = get_post_meta($postID, $count_key, true);
        }
    }
    die($count.' lượt xem');
}

add_action( 'wp_footer', 'svl_ajax_script', PHP_INT_MAX );
function svl_ajax_script() {
    if(!is_single()) return;
    ?>
    <script>
        (function($){
            $(document).ready( function() {
                $('.svl_post_view_count').each( function( i ) {
                    var $id = $(this).data('id');
                    var $nonce = $(this).data('nonce');
                    var t = this;
                    $.get('<?php echo admin_url( 'admin-ajax.php' ); ?>?action=svl-ajax-counter&nonce='+$nonce+'&p='+$id, function( html ) {
                        $(t).html( html );
                    });
                });
            });
        })(jQuery);
    </script>
    <?php
}
add_filter('manage_posts_columns', 'posts_column_views');
add_action('manage_posts_custom_column', 'posts_custom_column_views',5,2);
function posts_column_views($defaults){
    $defaults['post_views'] = __( 'lượt xem' , '' );
    return $defaults;
}
function posts_custom_column_views($column_name, $id){
    if( $column_name === 'post_views' ) {
        echo getPostViews( get_the_ID(), false);
    }
}
/*
 * WordPress Breadcrumbs
*/
function webwp_breadcrumbs() {
    /* === OPTIONS === */
    $text['home']     = 'Trang chủ'; // text for the 'Home' link
    $text['category'] = 'Bài của chuyên mục %s'; // text for a category page
    $text['search']   = 'Kết quả tìm kiếm %s'; // text for a search results page
    $text['tag']      = 'Từ khóa %s'; // text for a tag page
    $text['author']   = 'Tất cả bài viết của %s'; // text for an author page
    $text['404']      = 'Lỗi 404'; // text for the 404 page
    $text['page']     = 'Trang %s'; // text 'Page N'
    $text['cpage']    = 'Trang bình luận %s'; // text 'Comment Page N'
    $wrap_before    = '
 
 
<div class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">'; // the opening wrapper tag
    $wrap_after     = '</div>
 
 
 
<!-- .breadcrumbs -->'; // the closing wrapper tag
    $sep            = '›'; // separator between crumbs
    $sep_before     = '<span class="sep">'; // tag before separator
    $sep_after      = '</span>'; // tag after separator
    $show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
    $show_on_home   = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
    $show_current   = 1; // 1 - show current page title, 0 - don't show
    $before         = '<span class="current webwp_breadcrumbs">'; // tag before the current crumb
    $after          = '</span>'; // tag after the current crumb
    /* === END OF OPTIONS === */
    global $post;
    $home_url       = home_url('/');
    $link_before    = '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
    $link_after     = '</span>';
    $link_attr      = ' itemprop="item"';
    $link_in_before = '<span itemprop="name" class="webwp_breadcrumbs">';
    $link_in_after  = '</span>';
    $link           = $link_before . '<a href="%1$s"' . $link_attr . '>' . $link_in_before . '%2$s' . $link_in_after . '</a>' . $link_after;
    $frontpage_id   = get_option('page_on_front');
    $parent_id      = ($post) ? $post->post_parent : '';
    $sep            = ' ' . $sep_before . $sep . $sep_after . ' ';
    $home_link      = $link_before . '<a href="' . $home_url . '"' . $link_attr . ' class="home">' . $link_in_before . $text['home'] . $link_in_after . '</a>' . $link_after;
    if (is_home() || is_front_page()) {
        if ($show_on_home) echo $wrap_before . $home_link . $wrap_after;
    } else {
        echo $wrap_before;
        if ($show_home_link) echo $home_link;
        if ( is_category() ) {
            $cat = get_category(get_query_var('cat'), false);
            if ($cat->parent != 0) {
                $cats = get_category_parents($cat->parent, TRUE, $sep);
                $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                $cats = preg_replace('#<a([^>]+)>([^<]+)</a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
                if ($show_home_link) echo $sep;
                echo $cats;
            }
            if ( get_query_var('paged') ) {
                $cat = $cat->cat_ID;
                echo $sep . sprintf($link, get_category_link($cat), get_cat_name($cat)) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_current) echo $sep . $before . sprintf($text['category'], single_cat_title('', false)) . $after;
            }
        } elseif ( is_search() ) {
            if (have_posts()) {
                if ($show_home_link && $show_current) echo $sep;
                if ($show_current) echo $before . sprintf($text['search'], get_search_query()) . $after;
            } else {
                if ($show_home_link) echo $sep;
                echo $before . sprintf($text['search'], get_search_query()) . $after;
            }
        } elseif ( is_day() ) {
            if ($show_home_link) echo $sep;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $sep;
            echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F'));
            if ($show_current) echo $sep . $before . get_the_time('d') . $after;
        } elseif ( is_month() ) {
            if ($show_home_link) echo $sep;
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'));
            if ($show_current) echo $sep . $before . get_the_time('F') . $after;
        } elseif ( is_year() ) {
            if ($show_home_link && $show_current) echo $sep;
            if ($show_current) echo $before . get_the_time('Y') . $after;
        } elseif ( is_single() && !is_attachment() ) {
            if ($show_home_link) echo $sep;
            if ( get_post_type() != 'post' ) {
                $post_type = get_post_type_object(get_post_type());
                $slug = $post_type->rewrite;
                printf($link, $home_url . $slug['slug'] . '/', $post_type->labels->singular_name);
                if ($show_current) echo $sep . $before . get_the_title() . $after;
            } else {
                $cat = get_the_category(); $cat = $cat[0];
                $cats = get_category_parents($cat, TRUE, $sep);
                if (!$show_current || get_query_var('cpage')) $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
                $cats = preg_replace('#<a([^>]+)>([^<]+)</a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
                echo $cats;
                if ( get_query_var('cpage') ) {
                    echo $sep . sprintf($link, get_permalink(), get_the_title()) . $sep . $before . sprintf($text['cpage'], get_query_var('cpage')) . $after;
                } else {
                    if ($show_current) echo $before . get_the_title() . $after;
                }
            }
            // custom post type
        } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
            $post_type = get_post_type_object(get_post_type());
            if ( get_query_var('paged') ) {
                echo $sep . sprintf($link, get_post_type_archive_link($post_type->name), $post_type->label) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_current) echo $sep . $before . $post_type->label . $after;
            }
        } elseif ( is_attachment() ) {
            if ($show_home_link) echo $sep;
            $parent = get_post($parent_id);
            $cat = get_the_category($parent->ID); $cat = $cat[0];
            if ($cat) {
                $cats = get_category_parents($cat, TRUE, $sep);
                $cats = preg_replace('#<a([^>]+)>([^<]+)</a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
                echo $cats;
            }
            printf($link, get_permalink($parent), $parent->post_title);
            if ($show_current) echo $sep . $before . get_the_title() . $after;
        } elseif ( is_page() && !$parent_id ) {
            if ($show_current) echo $sep . $before . get_the_title() . $after;
        } elseif ( is_page() && $parent_id ) {
            if ($show_home_link) echo $sep;
            if ($parent_id != $frontpage_id) {
                $breadcrumbs = array();
                while ($parent_id) {
                    $page = get_page($parent_id);
                    if ($parent_id != $frontpage_id) {
                        $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                    }
                    $parent_id = $page->post_parent;
                }
                $breadcrumbs = array_reverse($breadcrumbs);
                for ($i = 0; $i < count($breadcrumbs); $i++) { echo $breadcrumbs[$i]; if ($i != count($breadcrumbs)-1) echo $sep; } } if ($show_current) echo $sep . $before . get_the_title() . $after; } elseif ( is_tag() ) { if ( get_query_var('paged') ) { $tag_id = get_queried_object_id(); $tag = get_tag($tag_id); echo $sep . sprintf($link, get_tag_link($tag_id), $tag->name) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
        } else {
            if ($show_current) echo $sep . $before . sprintf($text['tag'], single_tag_title('', false)) . $after;
        }
        } elseif ( is_author() ) {
            global $author;
            $author = get_userdata($author);
            if ( get_query_var('paged') ) {
                if ($show_home_link) echo $sep;
                echo sprintf($link, get_author_posts_url($author->ID), $author->display_name) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
            } else {
                if ($show_home_link && $show_current) echo $sep;
                if ($show_current) echo $before . sprintf($text['author'], $author->display_name) . $after;
            }
        } elseif ( is_404() ) {
            if ($show_home_link && $show_current) echo $sep;
            if ($show_current) echo $before . $text['404'] . $after;
        } elseif ( has_post_format() && !is_singular() ) {
            if ($show_home_link) echo $sep;
            echo get_post_format_string( get_post_format() );
        }
        echo $wrap_after;
    }
} // End webwp_breadcrumbs

add_filter('comment_form_default_fields', 'unset_url_field');
function unset_url_field($fields){
    if(isset($fields['url']))
        unset($fields['url']);
    return $fields;
}



// Register and load the widget 1 - webwp.vn
function wpb_load_widget() {
    register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );

// Creating the widget
class wpb_widget extends WP_Widget {

    function __construct() {
        parent::__construct(

// Base ID of your widget
            'wpb_widget',

// Widget name will appear in UI
            __('Thông tin công ty', 'wpb_widget_domain'),

// Widget description
            array( 'description' => __( 'Webwp.vn', 'wpb_widget_domain' ), )
        );
    }

// Creating widget front-end

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );

// before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];

// This is where you run the code and display the output

        ?>
 <?php
            $main_tel = get_field('so_dien_thoai_chinh', 'option');
            if($main_tel): ?>
                <div class="phone_tel"><i class="fa fa-phone"></i><ins> Điện thoại:</ins> <strong> <a href="tel:<?php echo $main_tel; ?>"><span> <?php echo $main_tel; ?> </span></a></strong></div>
            <?php endif; ?>
 <div class="email"><i class="fa fa-envelope"></i><ins> Email:</ins> <span><?php the_field('email', 'option'); ?></span></div>
            <div class="website"><i class="fa fa-globe"></i><ins> Website:</ins> <span>
				<?php the_field('websiteurl', 'option'); ?></span></div>
  <?php
            $addvpgd1 = get_field('dia_chi_vp1', 'option');
            if($addvpgd1): ?>
                <div class="email"><i class="fa fa-map-marker"></i><ins> </ins> <span><?php echo $addvpgd1; ?></span></div>
            <?php endif; ?>
        <div class="hidden-xs lien_he">
            <div class="tencty"><span><?php the_field('ten_cong_ty', 'option'); ?></span></div>
            <div class="diachi"><i class="fa fa-map-marker" aria-hidden="true"> </i><ins> Địa chỉ:</ins> <span><?php the_field('dia_chi', 'option'); ?></span></div>
			    
        
            <?php
            $addfactory = get_field('dia_chi_nm', 'option');
            if($addfactory): ?>
                <div class="marker"><i class="fa fa-map-marker"></i><ins> </ins> <span><?php echo $addfactory; ?></span></div>
            <?php endif; ?>
      
            <?php
            $fax_tel = get_field('fax', 'option');
            if($fax_tel): ?>
                <div class="phone_tel"><i class="fa fa-fax"></i><ins> Fax:</ins> <strong><span> <?php echo $fax_tel; ?> </span></strong></div>
            <?php endif; ?>
            <?php
            $hotline11 = get_field('hotline_1', 'option');
            if($hotline11): ?>
                <div class="phone_hotline"><i class="fa fa-volume-control-phone"></i><ins> Hotline:</ins> <strong> <a href="tel:<?php echo $hotline11; ?>"><span> <?php echo $hotline11; ?> </span></a></strong></div>
            <?php endif;
            $hotline22 = get_field('hotline_2', 'option');
            if($hotline22): ?>
                <div class="phone_hotline"><i class="fa fa-volume-control-phone"></i><ins> Hotline:</ins> <strong> <a href="tel:<?php echo $hotline22; ?>"><span> <?php echo $hotline22; ?> </span></a></strong></div>
            <?php endif;
            $hotline33 = get_field('hotline_3', 'option');
            if($hotline33): ?>
                <div class="phone_hotline"><i class="fa fa-volume-control-phone"></i><ins> Hotline:</ins> <strong> <a href="tel:<?php echo $hotline33; ?>"><span> <?php echo $hotline33; ?> </span></a></strong></div>
            <?php endif; ?>
          
        </div>
        <?php


        echo $args['after_widget'];
    }

// Widget Backend
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'wpb_widget_domain' );
        }
// Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
    }

// Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
} // Class wpb_widget ends here

add_action( 'admin_init', 'wpse_38111' );
function wpse_38111() {
    remove_submenu_page( 'index.php', 'update-core.php' );
}

