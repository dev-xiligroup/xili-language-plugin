<?php
namespace Xili_Main;

/**
 * @package  xili-language
 * @subpackage main class
 * functions for comments in front
 */
trait Xili_Language_Comments {

	/**
	 * Add filters of texts of comment form - because default text are linked with wp language (and not theme)
	 *
	 * @since 1.5.5
	 * @ return arrays with themetextdomain
	 */
	public function xili_comment_form_default_fields( $fields ) {
		$commenter = wp_get_current_commenter();

		$req = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
		$fields = array(
			'author' => '<p class="comment-form-author"><label for="author">' . xl__( $this->comment_form_labels['name'], $this->thetextdomain ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .
				'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
			'email'  => '<p class="comment-form-email"><label for="email">' . xl__( $this->comment_form_labels['email'], $this->thetextdomain ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) . '<input id="email" name="email" type="text" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
			'url'    => '<p class="comment-form-url"><label for="url">' . xl__( $this->comment_form_labels['website'], $this->thetextdomain ) . '</label>' .
				'<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
		);
		return $fields;
	}

	/** 2.3.2 - noun context */
	public function xili_comment_form_defaults( $defaults ) {
		global $user_identity, $post;
		$req = get_option( 'require_name_email' );

		$required_text = sprintf( ' ' . xl__( $this->comment_form_labels['requiredmarked'], $this->thetextdomain ), '<span class="required">*</span>' );

		$xilidefaults = array(
			'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . xl_x( $this->comment_form_labels['comment'], 'noun', $this->thetextdomain ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" aria-required="true" required="required"></textarea></p>',
			'must_log_in'          => '<p class="must-log-in">' . sprintf( xl__( $this->comment_form_labels['youmustbe'], $this->thetextdomain ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) ) ) . '</p>',
			'logged_in_as'         => '<p class="logged-in-as">' . sprintf(
				/* translators: 1: edit user link, 2: accessibility text, 3: user name, 4: logout URL */
				xl__( $this->comment_form_labels['loggedinas'], $this->thetextdomain ),
				get_edit_user_link(),
				/* translators: %s: user name */
				esc_attr( sprintf( xl__( $this->comment_form_labels['loggedinas_edit'], $this->thetextdomain ), $user_identity ) ),
				$user_identity,
				wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post->ID ) ) )
			) . '</p>',
			'comment_notes_before' => '<p class="comment-notes"><span id="email-notes">' . xl__( $this->comment_form_labels['emailnotpublished'], $this->thetextdomain ) . ( $req ? $required_text : '' ) . '</p>',

			'comment_notes_after'  => '',
			'id_form'              => 'commentform',
			'id_submit'            => 'submit',
			'class_form'           => 'comment-form',
			'class_submit'         => 'submit',
			'name_submit'          => 'submit',
			'title_reply'          => xl__( $this->comment_form_labels['leavereply'], $this->thetextdomain ),
			'title_reply_to'       => xl__( $this->comment_form_labels['replyto'], $this->thetextdomain ),
			'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
			'title_reply_after'    => '</h3>',
			'cancel_reply_before'  => ' <small>',
			'cancel_reply_after'   => '</small>',
			'cancel_reply_link'    => xl__( $this->comment_form_labels['cancelreply'], $this->thetextdomain ),
			'label_submit'         => xl__( $this->comment_form_labels['postcomment'], $this->thetextdomain ),
			'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" />',
			'submit_field'         => '<p class="form-submit">%1$s %2$s</p>',
			'format'               => 'xhtml',
		);
		$args = wp_parse_args( $xilidefaults, $defaults );
		return $args;
	}

	/**
	 * Language filter for latest comments widget
	 * @since 2.9.22
	 *
	 */
	public function xili_language_comments_clauses( $clauses, $wp_comment_query ) {

		$lang = ( isset( $wp_comment_query->query_vars[ QUETAG ] ) ) ? ( ( '*' == $wp_comment_query->query_vars[ QUETAG ] ) ? $this->curlang : $wp_comment_query->query_vars[ QUETAG ] ) : '';

		if ( '' != $lang ) {

			$reqtag = term_exists( $lang, TAXONAME );

			if ( $reqtag ) {

				global $wpdb;

				$wherereqtag = $reqtag['term_id'];
				$join = " LEFT JOIN $wpdb->term_relationships as tr ON ($wpdb->comments.comment_post_ID = tr.object_id) LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) ";
				$where = " AND tt.taxonomy = '" . TAXONAME . "' ";
				$where .= " AND tt.term_id = $wherereqtag ";
				$clauses['where'] = $clauses['where'] . $where;
				$clauses['join'] = $clauses['join'] . $join;
			}
		}

		return $clauses;
	}

	/**
	 * Select latest comments in current lang.
	 *
	 * @since 0.9.9.4
	 * @now 2.9.22 - obsolete
	 * used by widget xili-recent-comments
	 *
	 * @param $number.
	 * @return $comments.
	 */
	public function xiliml_recent_comments( $number = 5 ) {
		global $comments, $wpdb;
		if ( ! $comments = wp_cache_get( 'xili_language_recent_comments', 'widget' ) ) {
			$join = '';
			$where = '';// AND 'post_status' = 'publish' ;
			$reqtag = term_exists( $this->curlang, TAXONAME );
			if ( '' != $reqtag ) {
				$wherereqtag = $reqtag['term_id'];
				$join = " LEFT JOIN $wpdb->term_relationships as tr ON ($wpdb->comments.comment_post_ID = tr.object_id) LEFT JOIN $wpdb->term_taxonomy as tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) ";
				$where = " AND tt.taxonomy = '" . TAXONAME . "' ";
				$where .= " AND tt.term_id = $wherereqtag ";
			}
			$query = "SELECT * FROM $wpdb->comments" . $join . " WHERE comment_approved = '1' " . $where . " ORDER BY comment_date_gmt DESC LIMIT $number";

			$comments = $wpdb->get_results( $query );
			wp_cache_add( 'xili_language_recent_comments', $comments, 'widget' );
		}
		return $comments;
	}

}
