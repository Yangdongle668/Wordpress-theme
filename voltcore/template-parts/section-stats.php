<?php
/**
 * Stats counter section.
 *
 * @package VoltCore
 */
?>
<section class="section section--dark stats">
	<div class="stats__grid">
		<?php for ( $i = 1; $i <= 4; $i++ ) :
			$value = voltcore_text( 'voltcore_stat_' . $i . '_value', '' );
			$label = voltcore_text( 'voltcore_stat_' . $i . '_label', '' );
			if ( ! $value && ! $label ) {
				continue;
			}
		?>
		<div class="stat" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
			<div class="stat__value" data-count="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></div>
			<div class="stat__label"><?php echo esc_html( $label ); ?></div>
		</div>
		<?php endfor; ?>
	</div>
</section>
