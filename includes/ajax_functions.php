<?php


/**
 * @param $post_id
 * @param $cat_id
 *
 * @return array
 */
function splFetchPosts( $post_id, $cat_id )
{
	$result = [];

//	$args = array(
//		'category' => $cat_id,
//		'orderby'  => 'id',
//		'order'    => 'ASC'
//	);
	$posts = get_posts();
// get IDs of posts retrieved from get_posts

	$ids = array();
	foreach ( $posts as $thePost ) {
		$ids[] = $thePost->ID;
	}
// get and echo previous and next post in the same category
	$thisIndex = array_search( $post_id, $ids );
	$nextid    = isset( $ids[ $thisIndex + 1 ] ) ? $ids[ $thisIndex + 1 ] : -1;

	if ($nextid){
		$result['nextID'] = $nextid;
	}
    $post_full = get_post($nextid);

    $post_full->thumbnail = get_the_post_thumbnail($nextid);
    $post_full->author = get_the_author_meta('display_name',(int)$post_full->post_author);

	$result['current_post'] = $post_full;

	return $result;
}



function splGetPostTemplate()
{
	//Get nonce value
	$nonce = $_REQUEST['nonce'];
	//return false if nonce verification fails
	if (!  wp_verify_nonce(  $nonce, 'spl_ajax_verify_'. 2074 ) ) return;
	$postId = (int) $_REQUEST['postID'];
	$category_id = (int) $_REQUEST['category_id'];
	wp_send_json(splFetchPosts( $postId, $category_id ));
}

add_action('wp_ajax_splGetPostTemplate','splGetPostTemplate');
add_action('wp_ajax_nopriv_splGetPostTemplate','splGetPostTemplate');