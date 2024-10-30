<div class="import-progress-bar">
	<h2><?php _e( "Posts import progress:", "csv-importer-posts" ); ?></h2>
	<div class="meter animate">
		<span class="progress" style="width:5px"><span></span></span>
	</div>
</div>

<div class="result hidden">
	<h2><?php _e( 'Complete!', 'bwt-csv-importer' ) ?></h2>
	<h2>
		<?php _e( 'Total:', 'bwt-csv-importer' ) ?> <span class="total-posts"></span>.
		<?php _e( 'Posts inserted:', 'bwt-csv-importer' ) ?> <span class="post-inserted"></span>.
	</h2>
</div>
