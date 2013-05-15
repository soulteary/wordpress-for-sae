<?php get_header(); ?>
<?php
	$options = get_option('inove_options');
	if (function_exists('wp_list_comments')) {
		add_filter('get_comments_number', 'comment_count', 0);
	}
?>

<?php if (is_search()) : ?>
	<div class="boxcaption"><h3><?php _e('Search Results', 'inove'); ?></h3></div>
	<div class="box"><?php printf( __('Keyword: &#8216;%1$s&#8217;', 'inove'), wp_specialchars($s, 1) ); ?></div>

<?php else : ?>
	<div class="boxcaption"><h3><?php _e('Archive', 'inove'); ?></h3></div>
	<div class="box">
		<?php
		// If this is a category archive
		if (is_category()) {
			printf( __('Archive for the &#8216;%1$s&#8217; Category', 'inove'), single_cat_title('', false) );
		// If this is a tag archive
		} elseif (is_tag()) {
			printf( __('Posts Tagged &#8216;%1$s&#8217;', 'inove'), single_tag_title('', false) );
		// If this is a daily archive
		} elseif (is_day()) {
			printf( __('Archive for %1$s', 'inove'), get_the_time(__('F jS, Y', 'inove')) );
		// If this is a monthly archive
		} elseif (is_month()) {
			printf( __('Archive for %1$s', 'inove'), get_the_time(__('F, Y', 'inove')) );
		// If this is a yearly archive
		} elseif (is_year()) {
			printf( __('Archive for %1$s', 'inove'), get_the_time(__('Y', 'inove')) );
		// If this is an author archive
		} elseif (is_author()) {
			_e('Author Archive', 'inove');
		// If this is a paged archive
		} elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {
			_e('Blog Archives', 'inove');
		}
		?>
	</div>
<?php endif; ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); update_post_caches($posts); ?>
	<div class="post" id="post-<?php the_ID(); ?>">
		<h2><a class="title" href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
		<div class="info">
			<span class="date"><?php the_time(__('F jS, Y', 'inove')) ?></span>
			<?php if ($options['author']) : ?><span class="author"><?php the_author_posts_link(); ?></span><?php endif; ?>
			<?php edit_post_link(__('Edit', 'inove'), '<span class="editpost">', '</span>'); ?>
			<span class="comments"><?php comments_popup_link(__('No comments', 'inove'), __('1 comment', 'inove'), __('% comments', 'inove'), '', __('Comments off', 'inove')); ?></span>
			<div class="fixed"></div>
		</div>
		<div class="content">
			<?php the_content(__('Read more...', 'inove')); ?>
			<div class="fixed"></div>
		</div>
		<div class="under">
			<?php if ($options['categories']) : ?><span class="categories"><?php _e('Categories: ', 'inove'); ?></span><span><?php the_category(', '); ?></span><?php endif; ?>
			<?php if ($options['tags']) : ?><span class="tags"><?php _e('Tags: ', 'inove'); ?></span><span><?php the_tags('', ', ', ''); ?></span><?php endif; ?>
		</div>
	</div>
<?php endwhile; else : ?>
	<div class="errorbox">
		<?php _e('Sorry, no posts matched your criteria.', 'inove'); ?>
	</div>
<?php endif; ?>

<div id="pagenavi">
	<?php if(function_exists('wp_pagenavi')) : ?>
		<?php wp_pagenavi() ?>
	<?php else : ?>
		<span class="newer"><?php previous_posts_link(__('Newer Entries', 'inove')); ?></span>
		<span class="older"><?php next_posts_link(__('Older Entries', 'inove')); ?></span>
	<?php endif; ?>
	<div class="fixed"></div>
</div>

<?php get_footer(); ?>
