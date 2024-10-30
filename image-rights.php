<?php

/**
 * @package Image Rights
 * @author WSM – Walter Solbach Metallbau GmbH <webmaster@wsm.io>
 * @license GPLv3
 * @copyright 2022 by WSM – Walter Solbach Metallbau GmbH
 */
/*
Plugin Name: Image Rights
Plugin URI: 
Description: Adds additional fields for setting image credits in the media library.
Author: WSM – Walter Solbach Metallbau GmbH
Author URI: https://www.wsm.eu/
Version: 1.2
Text Domain: photocredits
Domain Path: /languages/
License: GPLv3

    Image Rights
    Copyright (C) 2022 WSM – Walter Solbach Metallbau GmbH

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Load plugin textdomain
 *
 */
function pcr_load_textdomain() {
    load_plugin_textdomain( 'photocredits', false, basename( dirname(__FILE__) ) . '/languages' );
}
add_action( 'plugins_loaded', 'pcr_load_textdomain' );


/**
 * Load admin styles and scripts
 *
 */
function pcr_load_scripts() {
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_style( 'pcr_styles', $plugin_url . 'css/admin-styles.css' );
    wp_register_script( 'pcr_scripts', $plugin_url . 'js/admin-scripts.js', array('jquery') );
    wp_enqueue_script( 'pcr_scripts' );
}
add_action( 'admin_enqueue_scripts', 'pcr_load_scripts' );


/**
 * Register admin setting
 *
 */
function pcr_settings_section() {  
    add_settings_section(  
        'pcr_settings_section', // Section ID 
        __( 'Image Rights Options', 'photocredits' ), // Section Title
        'pcr_section_options_callback', // Callback
        'media' // What Page?  This makes the section show up on the General Settings Page
    );
    
    add_settings_field(
        'pcr_option_1',
        __( 'Copyright overlay on images', 'photocredits' ),
        'pcr_imageoverlay_field_cb',
        'media',
        'pcr_settings_section',
        array( 
            'type'         => 'checkbox',
            'option_group' => 'pcr_options', 
            'name'         => 'pcr_option_1',
            'label_for'    => 'pcr_option_1',
            'value'        => get_option('pcr_option_1', '0'),
            'description'  => __( 'activate', 'photocredits' ),
            'checked'      => get_option('pcr_option_1', '0'),
            )
    );
     
    register_setting('media', 'pcr_option_1', 'esc_attr');
 }
 add_action('admin_init', 'pcr_settings_section');  
 
 function pcr_section_options_callback() { // Section Callback
    echo '';  
 }
 
function pcr_imageoverlay_field_cb($args) {
     $checked = $args['checked'];
     $value = $args['value'];

     if($checked) { $checked = ' checked="checked" '; }
         // Could use ob_start.
         $html  = '';
         $html .= '<input id="' . esc_attr( $args['name'] ) . '" 
         name="' . esc_attr( $args['name'] ) .'" value="1" 
         type="checkbox" ' . $checked . '/>';
         $html .= '<span class="wndspan">' . esc_html( $args['description'] ) .'</span>';
 
         echo $html;
 }
 

/**
 * Load Front styles and scripts
 *
 */
function pcr_load_front_scripts() {
    global $post;

    if ( is_singular() ) {
        $single_status = '1';
    } else {
        $single_status = '0';
    }

    $pcr_styles_css = plugin_dir_url(__FILE__) . 'css/styles.css';
    $pcr_styles_path_css = plugin_dir_path(__FILE__) . 'css/styles.css';
    wp_enqueue_style( 'pcr_front_styles', $pcr_styles_css, array(), filemtime( $pcr_styles_path_css ), 'all' );

    $post_thumbnail_id = get_post_meta( $post->ID, '_thumbnail_id', true );
    $pcr_photographer_name = get_post_meta( $post_thumbnail_id, 'pcr_photographer_name', true );
    $pcr_photographer_platform = get_post_meta( $post_thumbnail_id, 'pcr_photographer_platform', true );

    wp_localize_script(
        'pcr-custom-js',
        'pcr_frontend_ajax_object',
        array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'pcr_photographer_name' => $pcr_photographer_name,
            'pcr_photographer_platform' => $pcr_photographer_platform,
            'single_status' => $single_status
        )
    );
}
add_action( 'wp_enqueue_scripts', 'pcr_load_front_scripts' );


/**
 * Add Photographer Name and Platform fields to media uploader
 *
 */
function pcr_attachment_field_credit( $form_fields, $post ) {
    $form_fields['pcr-photographer-name'] = array(
        'label' => __( 'Photographer Name', 'photocredits' ),
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'pcr_photographer_name', true ),
        'helps' => __( 'Provide name of photographer', 'photocredits' ),
    );

    $form_fields['pcr-photographer-platform'] = array(
        'label' => __( 'Platform', 'photocredits' ),
        'input' => 'text',
        'value' => get_post_meta( $post->ID, 'pcr_photographer_platform', true ),
        'helps' => __( 'Provide name of platform (e.g. Adobe Stock)', 'photocredits' ),
    );

    return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'pcr_attachment_field_credit', 10, 2 );


/**
 * Save values of Photographer Name and URL in media uploader
 *
 */
function pcr_attachment_field_credit_save( $post, $attachment ) {
    if ( isset( $attachment['pcr-photographer-name'] ) ) :
        update_post_meta( $post['ID'], 'pcr_photographer_name', $attachment['pcr-photographer-name'] );
    endif;

    if ( isset( $attachment['pcr-photographer-platform'] ) ) :
        update_post_meta( $post['ID'], 'pcr_photographer_platform', $attachment['pcr-photographer-platform'] );
    endif;

    return $post;
}
add_filter( 'attachment_fields_to_save', 'pcr_attachment_field_credit_save', 10, 2 );


/**
 * Function for querying media library
 *
 */
function pcr_get_media() {
    // Query arguments
    $args = array(
        'post_type'         => 'attachment',
        'post_status'       => 'inherit',
        'post_mime_type'    => 'image',
        'meta_query'        => array(
            'relation'      => 'OR',
            array(
                'key'       => 'pcr_photographer_name',
                'value'     => array(''),
                'compare'   => 'NOT IN'
            ),
            array(
                'key'       => 'pcr_photographer_platform',
                'value'     => array(''),
                'compare'   => 'NOT IN'
            ),
        ),
        'posts_per_page'    => -1,
        'orderby'           => 'name',
    );

    // Query
    $query_images = new WP_Query( $args );

    // Put desired information into output
    $images = array();
    foreach ( $query_images->posts as $image ) {
        $images[] = $image;
    }

    return $images;
}


/**
 * Shortcode for displaying photo credits
 *
 */
function pcr_shortcode_photo_credits( $atts ) {
    // Attributes
    extract(shortcode_atts(array(
        'fields' => '',
    ), $atts));

    // init some vars
    $html = '';
    $the_images = pcr_get_media();

    // images found?
    if ( $the_images ) {

        $html .= '<table class="table photo-credits-table">';
        $html .= '<thead><tr><th style="width:25%;">' . __('Image', 'photocredits') . '</th><th>' . __('Image rights', 'photocredits') . '</th></tr></thead>';
        $html .= '<tbody>';

        // loop over images with our meta fields
        foreach ( $the_images as $image ) {

            // get meta information
            $meta_photographer      = get_post_meta( $image->ID, 'pcr_photographer_name', true );
            $meta_platform          = get_post_meta( $image->ID, 'pcr_photographer_platform', true );

            // puzzle the markup
            $html .= '<tr><td>';
            $html .= '<img src="' . wp_get_attachment_thumb_url( $image->ID ) . '" alt="' . $image->post_title . '" title="' . $image->post_title . '" class="image wp-image-' . $image->ID . ' attachment-thumbnail size-thumbnail img-thumbnail">';
            $html .= '</td><td>';
            $html .= '<p>';

            if ( !empty( $meta_photographer ) ) :
                $html .= __( 'Photographer', 'photocredits' ) . ': ' . $meta_photographer;
                $html .= '<br>';
            endif;

            if ( !empty( $meta_platform_defaults ) ) :
                $html .= __( 'Platform', 'photocredits' ) . ': ' . $meta_platform_defaults;
            elseif (!empty($meta_platform)) :
                $html .= __( 'Platform', 'photocredits' ) . ': ' . $meta_platform;
            endif;

            $html .= '</p>';
            $html .= '</td></tr>';
        }

        $html .= '</tbody></table>';
    }

    // output the html markup
    return $html;
}
add_shortcode( 'photo_credits', 'pcr_shortcode_photo_credits' );


/**
 * Add meta layer over featured images
 *
 */
function pcr_post_thumb_meta_data( $html, $post_id, $post_image_id ) {
    
    // check if overlay was activted in admin settings
    if ( get_option('pcr_option_1') == "1" ) {

        // check if not a single post or front page
        if ( !is_singular() || is_front_page() ) {
    
            // Return default if NOT single post / page / front
            return $html;
    
        } else {
    
            //get meta information
            $meta_photographer = get_post_meta( $post_image_id, 'pcr_photographer_name', true );
            $meta_platform     = get_post_meta( $post_image_id, 'pcr_photographer_platform', true );
            $featured_image    = get_the_post_thumbnail_url( $post_id, 'medium' );
            
            // init string
            $meta = $meta_photographer . ' | ' . $meta_platform;
    
            // remove pipe sign if only one meta field present
            if ( str_starts_with( $meta, ' | ' ) || str_ends_with( $meta, ' | ' ) ) :
                $meta = str_replace( ' | ', '', $meta );
            endif;
    
            // add layer if meta present
            if ( !empty( $meta ) ) :       
                if ( str_contains( $html, 'figcaption' ) ) {
                    $html = str_replace( '</figcaption>', '</figcaption><span>' . $meta . '</span>', $html );
                    $html = '<div class="pcr_featured_wrap">' . $html . '</div>';
                } else {
                    $html = '<div class="pcr_featured_wrap">' . $html . '<span>' . $meta . '</span></div>';
                }
            endif;
    
            return $html;
        }
        
    } else {
        
        // not activated, return standard markup
        return $html;
        
    }
}
add_filter( 'post_thumbnail_html', 'pcr_post_thumb_meta_data', 10, 3 );


/**
 * Add meta layer over content images
 *
 */
function pcr_img_add_meta_data( $filtered_image, $context, $attachment_id ) {
    
    // check if overlay was activted in admin settings
    if ( get_option('pcr_option_1') == "1" ) {

        //get meta information
        $meta_photographer = get_post_meta( $attachment_id, 'pcr_photographer_name', true );
        $meta_platform     = get_post_meta( $attachment_id, 'pcr_photographer_platform', true );
        $featured_image = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
        
        // init string
        $meta = $meta_photographer . ' | ' . $meta_platform;
    
        // remove pipe sign if only one meta field present
        if ( str_starts_with( $meta, ' | ' ) || str_ends_with( $meta, ' | ' ) ) :
            $meta = str_replace( ' | ', '', $meta );
        endif;
    
        // add layer if meta present
        if ( !empty( $meta ) ) :       
            if ( str_contains( $filtered_image, 'figcaption' ) ) {
                $filtered_image = str_replace( '</figcaption>', '</figcaption><span>' . $meta . '</span>', $filtered_image );
                $filtered_image = '<div class="pcr_content_wrap">' . $filtered_image . '</div>';
            } else {
                $filtered_image = '<div class="pcr_content_wrap">' . $filtered_image . '<span>' . $meta . '</span></div>';
            }
        endif;
        // return the block content
        return $filtered_image;
        
    } else {
        
        // not activated, return standard image
        return $filtered_image;
        
    }
}
add_filter( 'wp_content_img_tag', 'pcr_img_add_meta_data', 15, 3 );

?>