<?php
/**
Plugin Name: SAE Features Support
Description: Let WordPress run in Sina App Engine better.
Version: 0.0.1
Plugin URI: http://wordpress.org/extend/plugins/sae-feature-support/
Author: soulteary
Author URI:http://www.soulteary.com
License: GPL2

!!PLEASE Copy this file to wp-content/sae-support.php
 */

add_filter('SAE_FLITER_STORAGE_CHECKER', 'SAE_FLITER_STORAGE_CHECKER');
add_filter('SAE_FILTER_MKDIR_P', 'SAE_FILTER_MKDIR_P');
add_filter('SAE_FILTER_UPLOAD_DIR', 'SAE_FILTER_UPLOAD_DIR');


/**
 * 检查是否正常开启了STORAGE服务以及创建DOMAIN。
 *
 * @file wp-includes/capabilities.php
 * @since 3.7.1
 *
 */
function SAE_FLITER_STORAGE_CHECKER($ak){
    $ak ? $storage = new SaeStorage($ak) : $storage = new SaeStorage();
    $file = array('name'=>'install-checker','data'=>'install check by soulteary');
    $storage->write(SAE_STORAGE, $file['name'], $file['data']);
    $get = $storage->read(SAE_STORAGE, $file['name']);
    if( $get==$file['data']){
        return true;
    }else{
        return false;
    }
}

/**
 * 递归创建目录。
 *
 * @file wp-includes/functions.php
 * @since 3.5.2
 *
 */
function SAE_FILTER_MKDIR_P($target)
{
    if (substr($target, 0, 10) == 'saestor://') {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取上传路径地址
 *
 * @file wp-includes/functions.php
 * @since 3.5.2
 *
 */
function SAE_FILTER_UPLOAD_DIR( $time = null ) {
    $dir = SAE_DIR;
    $url = SAE_URL;
    $basedir = $dir;
    $baseurl = $url;
    $subdir = '';
    if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
        // Generate the yearly and monthly dirs
        if ( !$time )
            $time = current_time( 'mysql' );
        $y = substr( $time, 0, 4 );
        $m = substr( $time, 5, 2 );
        $subdir = "/$y/$m";
    }

    $dir .= $subdir;
    $url .= $subdir;

    $uploads = apply_filters( 'upload_dir',
        array(
            'path'    => $dir,
            'url'     => $url,
            'subdir'  => $subdir,
            'basedir' => $basedir,
            'baseurl' => $baseurl,
            'error'   => false,
        ) );

    // Make sure we have an uploads dir
    if ( ! SAE_FILTER_MKDIR_P( $uploads['path'] ) ) {
        if ( 0 === strpos( $uploads['basedir'], ABSPATH ) )
            $error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
        else
            $error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];

        $message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $error_path );
        $uploads['error'] = $message;
    }
    return $uploads;
}

/**
 * 保存图片
 *
 * @file wp-includes/functions.php
 * @since 3.5.2
 *
 */
function SAE_FLITER_SAVE_IMAGE_FILE($mime_type,$image,$filename)
{
    $tmp = tempnam(SAE_TMP_PATH, 'WPIMG');
    switch ($mime_type) {
        case 'image/jpeg':
            return imagejpeg($image, $tmp, apply_filters('jpeg_quality', 90, 'edit_image')) && copy($tmp, $filename);
        case 'image/png':
            return imagepng($image, $tmp) && copy($tmp, $filename);
        case 'image/gif':
            return imagegif($image, $tmp) && copy($tmp, $filename);
        default:
            return false;
    }
}

?>