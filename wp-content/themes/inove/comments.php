<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/comment.js"></script>

<?php if (!empty($post->post_password) && $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) : ?>
	<div class="errorbox">
		<?php _e('Enter your password to view comments.', 'inove'); ?>
	</div>
<?php return; endif; ?>

<?php
	$options = get_option('inove_options');
	// for WordPress 2.7 or higher
	if (function_exists('wp_list_comments')) {
		$trackbacks = $comments_by_type['pings'];
	// for WordPress 2.6.3 or lower
	} else {
		$trackbacks = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = '1' AND (comment_type = 'pingback' OR comment_type = 'trackback') ORDER BY comment_date", $post->ID));
	}
?>

<?php if ($comments || comments_open()) : ?>
<div id="comments">

<div id="cmtswitcher">
	<?php if(pings_open()) : ?>
		<a id="commenttab" class="curtab" href="javascript:void(0);" onclick="MGJS.switchTab('thecomments,commentnavi', 'thetrackbacks', 'commenttab', 'curtab', 'trackbacktab', 'tab');"><?php _e('Comments', 'inove'); echo (' (' . (count($comments)-count($trackbacks)) . ')'); ?></a>
		<a id="trackbacktab" class="tab" href="javascript:void(0);" onclick="MGJS.switchTab('thetrackbacks', 'thecomments,commentnavi', 'trackbacktab', 'curtab', 'commenttab', 'tab');"><?php _e('Trackbacks', 'inove'); echo (' (' . count($trackbacks) . ')'); ?></a>
	<?php else : ?>
		<a id="commenttab" class="curtab" href="javascript:void(0);"><?php _e('Comments', 'inove'); echo (' (' . (count($comments)-count($trackbacks)) . ')'); ?></a>
	<?php endif; ?>
	<?php if(comments_open()) : ?>
		<span class="addcomment"><a href="#respond"><?php _e('Leave a comment', 'inove'); ?></a></span>
	<?php endif; ?>
	<?php if(pings_open()) : ?>
		<span class="addtrackback"><a href="<?php trackback_url(); ?>"><?php _e('Trackback', 'inove'); ?></a></span>
	<?php endif; ?>
	<div class="fixed"></div>
</div>

<div id="commentlist">
	<!-- comments START -->
	<ol id="thecomments">
	<?php
		if ($comments && count($comments) - count($trackbacks) > 0) {
			// for WordPress 2.7 or higher
			if (function_exists('wp_list_comments')) {
				wp_list_comments('type=comment&callback=custom_comments');
			// for WordPress 2.6.3 or lower
			} else {
				foreach ($comments as $comment) {
					if($comment->comment_type != 'pingback' && $comment->comment_type != 'trackback') {
						custom_comments($comment, null, null);
					}
				}
			}
		} else {
	?>
		<li class="messagebox">
			<?php _e('No comments yet.', 'inove'); ?>
		</li>
	<?php
		}
	?>
	</ol>
	<!-- comments END -->

<?php
	if (get_option('page_comments')) {
		$comment_pages = paginate_comments_links('echo=0');
		if ($comment_pages) {
?>
		<div id="commentnavi">
			<span class="pages"><?php _e('Comment pages', 'inove'); ?></span>
			<div id="commentpager">
				<?php echo $comment_pages; ?>
				<span id="cp_post_id"><?php echo $post->ID; ?></span>
			</div>
			<div class="fixed"></div>
		</div>
<?php
		}
	}
?>

	<!-- trackbacks START -->
	<?php if (pings_open()) : ?>
		<ol id="thetrackbacks">
			<?php if ($trackbacks) : $trackbackcount = 0; ?>
				<?php foreach ($trackbacks as $comment) : ?>
					<li class="trackback">
						<div class="date">
							<?php printf( __('%1$s at %2$s', 'inove'), get_comment_time(__('F jS, Y', 'inove')), get_comment_time(__('H:i', 'inove')) ); ?>
							 | <a href="#comment-<?php comment_ID() ?>"><?php printf('#%1$s', ++$trackbackcount); ?></a>
						</div>
						<div class="act">
							<?php edit_comment_link(__('Edit', 'inove'), '', ''); ?>
						</div>
						<div class="fixed"></div>
						<div class="title">
							<a href="<?php comment_author_url() ?>">
								<?php comment_author(); ?>
							</a>
						</div>
					</li>
				<?php endforeach; ?>

			<?php else : ?>
				<li class="messagebox">
					<?php _e('No trackbacks yet.', 'inove'); ?>
				</li>

			<?php endif; ?>
		</ol>
	<?php endif; ?>
	<div class="fixed"></div>
	<!-- trackbacks END -->
</div>

</div>
<?php endif; ?>

<?php if (!comments_open()) : // If comments are closed. ?>
	<div class="messagebox">
		<?php _e('Comments are closed.', 'inove'); ?>
	</div>
<?php elseif ( get_option('comment_registration') && !$user_ID ) : // If registration required and not logged in. ?>
	<div id="comment_login" class="messagebox">
		<?php
			if (function_exists('wp_login_url')) {
				$login_link = wp_login_url();
			} else {
				$login_link = get_option('siteurl') . '/wp-login.php?redirect_to=' . urlencode(get_permalink());
			}
		?>
		<?php printf(__('You must be <a href="%s">logged in</a> to post a comment.', 'inove'), $login_link); ?>
	</div>

<?php else : ?>
	<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
	<div id="respond">

		<?php if ($user_ID) : ?>
			<?php
				if (function_exists('wp_logout_url')) {
					$logout_link = wp_logout_url();
				} else {
					$logout_link = get_option('siteurl') . '/wp-login.php?action=logout';
				}
			?>
			<div class="row">
				<?php _e('Logged in as', 'inove'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><strong><?php echo $user_identity; ?></strong></a>.
				 <a href="<?php echo $logout_link; ?>" title="<?php _e('Log out of this account', 'inove'); ?>"><?php _e('Logout &raquo;', 'inove'); ?></a>
			</div>

			<?php else : ?>
			<?php if ( $comment_author != "" ) : ?>
				<div class="row">
					<?php printf(__('Welcome back <strong>%s</strong>.', 'inove'), $comment_author) ?>
					<span id="show_author_info"><a href="javascript:void(0);" onclick="MGJS.setStyleDisplay('author_info','');MGJS.setStyleDisplay('show_author_info','none');MGJS.setStyleDisplay('hide_author_info','');"><?php _e('Change &raquo;', 'inove'); ?></a></span>
					<span id="hide_author_info"><a href="javascript:void(0);" onclick="MGJS.setStyleDisplay('author_info','none');MGJS.setStyleDisplay('show_author_info','');MGJS.setStyleDisplay('hide_author_info','none');"><?php _e('Close &raquo;', 'inove'); ?></a></span>
				</div>
			<?php endif; ?>

			<div id="author_info">
				<div class="row">
					<input type="text" name="author" id="author" class="textfield" value="<?php echo $comment_author; ?>" size="24" tabindex="1" />
					<label for="author" class="small"><?php _e('Name', 'inove'); ?> <?php if ($req) _e('(required)', 'inove'); ?></label>
				</div>
				<div class="row">
					<input type="text" name="email" id="email" class="textfield" value="<?php echo $comment_author_email; ?>" size="24" tabindex="2" />
					<label for="email" class="small"><?php _e('E-Mail (will not be published)', 'inove');?> <?php if ($req) _e('(required)', 'inove'); ?></label>
				</div>
				<div class="row">
					<input type="text" name="url" id="url" class="textfield" value="<?php echo $comment_author_url; ?>" size="24" tabindex="3" />
					<label for="url" class="small"><?php _e('Website', 'inove'); ?></label>
				</div>
			</div>

			<?php if ( $comment_author != "" ) : ?>
				<script type="text/javascript">MGJS.setStyleDisplay('hide_author_info','none');MGJS.setStyleDisplay('author_info','none');</script>
			<?php endif; ?>

		<?php endif; ?>

		<!-- comment input -->
		<div class="row">
			<textarea name="comment" id="comment" tabindex="4" rows="8" cols="50"></textarea>
		</div>

		<!-- comment submit and rss -->
		<div id="submitbox">
			<a class="feed" href="<?php bloginfo('comments_rss2_url'); ?>"><?php _e('Subscribe to comments feed', 'inove'); ?></a>
			<div class="submitbutton">
				<input name="submit" type="submit" id="submit" class="button" tabindex="5" value="<?php _e('Submit Comment', 'inove'); ?>" />
			</div>
			<?php if (function_exists('highslide_emoticons')) : ?>
				<div id="emoticon"><?php highslide_emoticons(); ?></div>
			<?php endif; ?>
			<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
			<div class="fixed"></div>
		</div>

	</div>
	<?php do_action('comment_form', $post->ID); ?>
	</form>

	<?php if ($options['ctrlentry']) : ?>
		<script type="text/javascript">MGJS.loadCommentShortcut();</script>
	<?php endif; ?>

<?php endif; ?>
