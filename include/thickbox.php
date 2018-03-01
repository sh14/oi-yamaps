<?php
function oiyamaps__button() // Добавляем кнопку редактирования шорткода на страницу редактирования поста в админке
{
	?>
	<a href="#" class="oiyamaps__button button"
	   title="<?php _e( "Yandex Map", "oiyamaps" ); ?>"><?php _e( "Yandex Map", "oiyamaps" ); ?></a>
	<?php
}

function oi_yamaps_thickbox() // окно редактирования шорткода
{
	$fields = array(
		'address'     => array(
			'label' => __( 'Address or coordinates', 'oiyamaps' ),
			'hint'  => __( 'Type an address or coordinates. Address example: Moscow; Coordinates example: 55.755814,37.617635', 'oiyamaps' ),
			'cols'  => ' colspan="3"',
			'value' => '',
		),
		'coordinates' => array(
			'value' => '',
		),
		'header'      => array(
			'label' => __( 'Baloon header', 'oiyamaps' ),
			'value' => '',
		),
		'body'        => array(
			'label' => __( 'Baloon body content', 'oiyamaps' ),
			'value' => '',
		),
		'footer'      => array(
			'label' => __( 'Baloon footer', 'oiyamaps' ),
			'value' => '',
		),
		'hint'        => array(
			'label' => __( 'Placemark hint', 'oiyamaps' ),
			'value' => '',
		),
		'iconcontent' => array(
			'label' => __( 'Plcamark label', 'oiyamaps' ),
			'value' => '',
		),
		'placemark'   => array(
			'label' => __( 'Plcamark type', 'oiyamaps' ),
			'hint'  => __( 'Default: ', 'oiyamaps' ),
			'value' => '',
		),
		'center'      => array(
			'label' => __( 'Map center', 'oiyamaps' ),
			'hint'  => __( 'It should be a coordinates, like 55.754736,37.620875', 'oiyamaps' ) . '<br>' . __( 'By default center = coordinates', 'oiyamaps' ),
			'value' => '',
		),
		'height'      => array(
			'label' => __( 'Map height', 'oiyamaps' ),
			'hint'  => __( 'Default: ', 'oiyamaps' ),
			'value' => '',
		),
		'width'       => array(
			'label' => __( 'Map width', 'oiyamaps' ),
			'hint'  => __( 'Default: ', 'oiyamaps' ),
			'value' => '',
		),
		'zoom'        => array(
			'label' => __( 'Map zoom', 'oiyamaps' ),
			'hint'  => __( 'Default: ', 'oiyamaps' ),
			'value' => '',
		),
	);
	$out    = '';

	$options = wp_parse_args( get_option( OIYM_PREFIX . 'options' ), oi_yamaps_defaults() );

	$template = '<td><label for="%key%">%label%</label></td>' .
	            '<td%cols%><div class="oiyamaps__form-group"><input type="text" name="%key%" class="oiyamaps__form-control" /></div>%hint%</td>';

	$i = 0;
	foreach ( $fields as $key => $val ) {
		$i ++;
		$val['key'] = $key;

		if ( ! empty( $val['hint'] ) ) {
			$val['hint'] = '<p class="help-block description">' . $val['hint'] . ' ' . $options[ $key ] . '</p>';
		} else {
			$val['hint'] = '';
		}
		// формируем таблицу с полями
		if ( $i % 2 != 0 ) {
			$out .= '<tr>';
		}

		if ( $key == 'coordinates' ) {
			$out .= '<input type="hidden" name="coordinates" />';
		} else {
			$out .= oiyamaps_html( $template, $val, array(
				'cols',
			) );
		}


		if ( $i % 2 == 0 ) {
			$out .= '</tr>';
		}
	}

	oiyamaps_add_map( array( 'form' => $out, ) );
}

function oiyamaps_add_map( $atts ) {
	$atts = wp_parse_args( $atts, array(
		'form' => '',
	) );
	?>
	<div class="oiyamaps__form" style="display:none;">
		<div class="media-modal wp-core-ui">
			<button type="button" class="button-link media-modal-close" onclick="close_oi_yamaps();">
				<span class="media-modal-icon">
					<span class="screen-reader-text">Закрыть окно параметров файла</span>
				</span>
			</button>
			<div class="media-modal-content">
				<div class="media-frame mode-select wp-core-ui hide-menu">
					<div class="media-frame-title">
						<h1>Вставить карту<span class="dashicons dashicons-arrow-down"></span></h1>
					</div>
					<div class="media-frame-router">
						<div class="media-router">
							<a href="#" class="media-menu-item active" data-block="mark">Настройки карты</a>
							<?php /* ?><a href="#" class="media-menu-item" data-block="option">Добавление меток</a><?php */ ?>
						</div>
					</div>
					<div class="media-frame-content">
						<div class="media-frame-content-box mark">
							<div class="map-toolbar">
								<form>
									<table><?php print $atts['form']; ?></table>
								</form>
							</div>
						</div>
						<?php /* ?>
					<div id="YMaps_0" class="YMaps" style=""><a class="author_link" href="https://oiplug.com/">OiYM</a></div>
					<div class="shortcode_text"></div>
					<?php */ ?>
					</div>

					<div class="media-frame-toolbar">
						<div class="media-toolbar">
							<div class="media-toolbar-primary search-form">
								<input type="button"
								       class="button media-button button-primary button-large media-button-insert"
								       value="<?php _e( 'Insert map', '' ); ?>" onclick="insert_oi_yamaps('map');"/>
								<input type="button"
								       class="button media-button button-primary button-large media-button-insert"
								       value="<?php _e( 'Insert placemark', '' ); ?>"
								       onclick="insert_oi_yamaps('placemark');"/>
								<a class="button media-button button-large media-button-insert button-cancel" href="#"
								   onclick="close_oi_yamaps();"><?php _e( "Cancel" ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="media-modal-backdrop"></div>
	</div>
	<style>
		.map-toolbar {
			margin: 0 15px;
		}

		.oiyamaps__form-group {
			margin-right: 10px;
			position: relative;
		}
		.oiyamaps__form-control {
			width: 100%;
			box-sizing: border-box;
		}
		.oiyamaps__preloader {
			position: relative;
		}
		.oiyamaps__preloader:after {
			position: absolute;
			box-sizing: border-box;
			content: '';
			top: 50%;
			right: .5em;
			margin-top: -0.7em;
			width:1.4em;
			height:1.4em;
			display:inline-block;
			padding:0px;
			border-radius:100%;
			border:2px solid;
			border-top-color:rgba(0,0,0, 0.65);
			border-bottom-color:rgba(0,0,0, 0.15);
			border-left-color:rgba(0,0,0, 0.65);
			border-right-color:rgba(0,0,0, 0.15);
			-webkit-animation: oiyamaps__preloader 0.8s linear infinite;
			animation: oiyamaps__preloader 0.8s linear infinite;
		}
		@keyframes oiyamaps__preloader {
			from {transform: rotate(0deg);}
			to {transform: rotate(360deg);}
		}
		@-webkit-keyframes oiyamaps__preloader {
			from {-webkit-transform: rotate(0deg);}
			to {-webkit-transform: rotate(360deg);}
		}
	</style>
	<?php
}
