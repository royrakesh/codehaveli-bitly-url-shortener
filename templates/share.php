<?php

global $post_id;

$bitly_url = get_wbitly_short_url($post_id);

?>

<div class="wbitly-social-share-buttons-wrapper">
	<p>Share Now</p>
	<div class="wbitly-social-share-buttons">
		<div class="wbitly-share-button wbitly-facebook">
			<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $bitly_url;?>"
			target="_blank" rel="noopener noreferrer" title="Share on Facebook"><svg aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" class="dashicon dashicons-facebook-alt"><path d="M8.46 18h2.93v-7.3h2.45l.37-2.84h-2.82V6.04c0-.82.23-1.38 1.41-1.38h1.51V2.11c-.26-.03-1.15-.11-2.19-.11-2.18 0-3.66 1.33-3.66 3.76v2.1H6v2.84h2.46V18z"></path></svg></a>
		</div>
		<div class="wbitly-share-button wbitly-x">
			<a href="https://x.com/intent/post?url=<?php echo $bitly_url;?>&text=<?php echo get_the_title($post_id); ?>"
			target="_blank" rel="noopener noreferrer" title="Share on X">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" viewBox="0 0 512 512">
				<g>
					<path fill="#111" d="M486,392.599C486,443.97,443.97,486,392.599,486H119.401C68.03,486,26,443.97,26,392.599V119.401 C26,68.031,68.03,26,119.401,26h273.198C443.97,26,486,68.031,486,119.401V392.599z"/>
					<path fill="#F0F0F1" d="M290.425,233.064l110.65-137.91h-32.05l-94.62,117.94l-94.63-117.94H74.125l147.45,183.78l-110.66,137.92 h32.05l94.63-117.95l94.64,117.95h105.65L290.425,233.064z M126.225,120.153h41.55l218,271.7h-41.55L126.225,120.153z"/>
				</g>
			</svg>
			</a>
		</div>
		<div class="wbitly-share-button wbitly-email">
			<a href="mailto:?subject=<?php echo get_the_title($post_id); ?>&body=<?php echo $bitly_url;?>"
			target="_blank" rel="noopener noreferrer" title="Email now"><svg aria-hidden="true" role="img" focusable="false" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" class="dashicon dashicons-email"><path d="M3.87 4h13.25C18.37 4 19 4.59 19 5.79v8.42c0 1.19-.63 1.79-1.88 1.79H3.87c-1.25 0-1.88-.6-1.88-1.79V5.79c0-1.2.63-1.79 1.88-1.79zm6.62 8.6l6.74-5.53c.24-.2.43-.66.13-1.07-.29-.41-.82-.42-1.17-.17l-5.7 3.86L4.8 5.83c-.35-.25-.88-.24-1.17.17-.3.41-.11.87.13 1.07z"></path></svg></a>
		</div>
	</div>
	
</div>
