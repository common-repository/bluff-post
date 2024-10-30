<?php
/**
 * utilities.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */

/**
 * short cutting string
 *
 * @param string $original_string
 * @param integer $max_length
 * @param boolean $show_period
 *
 * @return string
 */
function blfpst_shortcut_string( $original_string, $max_length, $show_period = true ) {
	$len        = mb_strlen( $original_string, 'utf-8' );
	$new_string = mb_substr( $original_string, 0, $max_length, 'utf-8' );
	$new_string .= ( ( $len > $max_length ) && $show_period ) ? '...' : '';

	return $new_string;
}

/**
 * localized datetime string
 *
 * @param string $original_datetime_string
 *
 * @return string
 */
function blfpst_localize_datetime_string( $original_datetime_string ) {
	$format                   = esc_html__( 'Y/m/d H:i', 'bluff-post' );
	$datetime                 = new DateTime( $original_datetime_string );
	$localize_datetime_string = $datetime ? $datetime->format( $format ) : '';

	return $localize_datetime_string;
}

/**
 * localized datetime string with second
 *
 * @param string $original_datetime_string
 *
 * @return string
 */
function blfpst_localize_datetime_min_string( $original_datetime_string ) {
	$format                   = esc_html__( 'Y/m/d H:i:s', 'bluff-post' );
	$datetime                 = new DateTime( $original_datetime_string );
	$localize_datetime_string = $datetime ? $datetime->format( $format ) : '';

	return $localize_datetime_string;
}

/**
 * get WordPress timezone
 *
 * @return DateTimeZone
 */
function blfpst_get_wp_timezone() {
	$gmt_offset = get_option( 'gmt_offset' );
	$zone_name  = timezone_name_from_abbr( '', $gmt_offset * 60 * 60, 0 );
	$time_zone  = new DateTimeZone( $zone_name );

	return $time_zone;
}
