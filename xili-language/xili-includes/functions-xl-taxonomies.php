<?php
//
/**
 * == Functions that improve taxinomy.php ==
 */

/**
 * get terms and add order in term's series that are in a taxonomy
 * (not in class for general use)
 *
 * @since 0.9.8.2 - full version is in xili-tidy-tags
 * @uses $wpdb
 */
function get_terms_of_groups_lite( $group_ids, $taxonomy, $taxonomy_child, $order = '' ) {
	global $wpdb;
	if ( ! is_array( $group_ids ) ) {
		$group_ids = array( $group_ids );
	}
	$group_ids = array_map( 'intval', $group_ids );
	$group_ids = implode( ', ', $group_ids );
	$theorderby = '';

	// lite release
	if ( 'ASC' == $order || 'DESC' == $order ) {
		$theorderby = ' ORDER BY tr.term_order ' . $order;
	}

	$query = "SELECT t.*, tt2.term_taxonomy_id, tt2.description,tt2.parent, tt2.count, tt2.taxonomy, tr.term_order FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN $wpdb->terms AS t ON t.term_id = tr.object_id INNER JOIN $wpdb->term_taxonomy AS tt2 ON tt2.term_id = tr.object_id WHERE tt.taxonomy IN ('".$taxonomy."') AND tt2.taxonomy = '".$taxonomy_child."' AND tt.term_id IN (".$group_ids.") ".$theorderby;

	$listterms = $wpdb->get_results( $query );
	if ( ! $listterms ) {
		return array();
	}

	return $listterms;
}

/**
 * for backward compatibility - soon obsolete - please modify your theme's function.php
 */
function get_terms_with_order( $group_ids, $taxonomy, $taxonomy_child, $order = 'ASC' ) {
	return get_terms_of_groups_lite( $group_ids, $taxonomy, $taxonomy_child, $order );
}

/**
 * function that improve taxinomy.php
 * @since 0.9.8
 *
 * update term order in relationships (for terms of langs group defined by his taxonomy_id)
 *
 * @param $object_id, $taxonomy_id, $term_order
 *
 */
function update_term_order( $object_id, $term_taxonomy_id, $term_order ) {
	global $wpdb;
	$wpdb->update(
		$wpdb->term_relationships,
		compact( 'term_order' ),
		array(
			'term_taxonomy_id' => $term_taxonomy_id,
			'object_id'        => $object_id,
		)
	);
}

/**
 * function that improve taxinomy.php
 * @since 0.9.8
 *
 * get one term and order of it in relationships
 *
 * @param term_id and $group_ttid (taxonomy id of group)
 * @return object with term_order
 */
function get_term_and_order( $term_id, $group_ttid, $taxonomy ) {
	global $wpdb;
	$term = get_term( $term_id, $taxonomy, OBJECT, 'edit' );
	$term->term_order = $wpdb->get_var( "SELECT term_order FROM $wpdb->term_relationships WHERE object_id = $term_id AND term_taxonomy_id = $group_ttid " );
	return $term;
}

