<?php



function splProcessTemplate( $post )
{

	$html = '
	<article itemscope="" itemtype="">
		<header class="entry-header entry-header-01">
			<div class="entry-before-title">
			</div>
			<h1 class="g1-mega g1-mega-1st entry-title" itemprop="headline">'. $post->post_title .'</h1>
		</header>

		<div class="g1-content-narrow g1-typography-xl entry-content" itemprop="articleBody">'.
	        $post->content .
	        '</div>
	</article>';
	return $html;
}


function splGetPostTemplate()
{
	//Get nonce value
	$nonce = $_REQUEST['nonce'];
	//return false if nonce verification fails
	if (!  wp_verify_nonce(  $nonce, 'spl_ajax_verify_'. 2074 ) ) return;

	//Get Post
    $post = get_post((int)$_REQUEST['postID']);
    $template = splProcessTemplate($post);
	wp_send_json($template);
}

add_action('wp_ajax_splGetPostTemplate','splGetPostTemplate');
add_action('wp_ajax_nopriv_splGetPostTemplate','splGetPostTemplate');