<?php
?>
<!-- 
<div class="search form-wrapper form-group">
	<form method="get" id="searchform" class="form-inline form-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label for="s" class="assistive-text"><?php _e( 'Search', 'twentyeleven' ); ?></label>
		<input type="text" class="form-control" name="s" id="s" placeholder="<?php esc_attr_e( 'Search...', 'ttp' ); ?>" />
	</form>
</div>
 -->
 
	<form method="get" id="searchform" role="form" class="navbar-form navbar-right" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<div class="form-group">
			<div class="input-group">
				<input type="text" class="form-control" name="s" id="s" placeholder="<?php esc_attr_e( 'Search...', 'ttp' ); ?>" />
				<span class="input-group-btn">
					<button type="submit" class="btn btn-default"><i class="fa fa-search fa-fw"></i></button>
				</span>
			</div>
		</div>
	</form>
