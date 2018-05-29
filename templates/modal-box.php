<div class="oiyamaps-modal js-modal_oiyamaps">

	<div class="oiyamaps-modal__container">
		<div class="oiyamaps-block js-slider-box">
			<ul class="oiyamaps-block__tabs js-tabs">
				<li class="oiyamaps-block__tab active js-tab"
				    data-index="0"><?php _e( 'Placemarks', 'oi-yamaps' ); ?></li>
				<li class="oiyamaps-block__tab js-tab" data-index="1"><?php _e( 'Map', 'oi-yamaps' ); ?></li>
			</ul>
			<div class="oiyamaps-block__options">
				<div class="oiyamaps-block__options-block">
					<?php echo \oiyamaps\get_edit_form(); ?>
					<div class="oiyamaps-block__map-add-block js-map-add-block">
						<?php
						the_oinput( array(
								'type'       => 'button',
								'attributes' => array(
									'data' => array(
										'gist' => 'map',
									),
								),
								'class'      => 'oiyamaps-button oiyamaps-button_primary oiyamaps-button_block oiyamaps-block__add js-submit_oiyamaps',
								'value'      => __( 'Add shortcode', 'oi-yamaps' ),
							)
						);

						the_oinput( array(
								'type'  => 'button',
								'class' => 'oiyamaps-button oiyamaps-button_block oiyamaps-block__close js-modal_close_oiyamaps',
								'value' => __( 'Close', 'oi-yamaps' ),
							)
						);
						?>
					</div>
				</div>
			</div>
			<div class="oiyamaps-block__map">
				<div class="oiyamaps-map"></div>
			</div>
		</div>
		<button type="button" class="oiyamaps-modal__close js-modal_close_oiyamaps"></button>
	</div>

	<div class="oiyamaps-modal__backdrop"></div>
</div>

<script id="js-template_placemark" type="text/ejs">
	<div class="oiyamaps__shortcode-item js-item" data-shortcode="<%=shortcode%>" data-id="<%=id%>">
		<div class="oiyamaps__shortcode-address"><%=address%></div>
		<div class="oiyamaps__shortcode-buttons">
			<div class="oiyamaps__shortcode-button oiyamaps__shortcode-button-edit js-edit">✎</div>
			<div class="oiyamaps__shortcode-button oiyamaps__shortcode-button-remove js-remove">✕</div>
		</div>
	</div>


</script>

