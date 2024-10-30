<?php
/**
 * calendar view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var int $current_year
 * @var int $current_month
 * @var int $current_day
 */
$current_year  = isset( $data['current_year'] ) ? (int) $data['current_year'] : 0;
$current_month = isset( $data['current_month'] ) ? (int) $data['current_month'] : 0;
$current_day   = isset( $data['current_day'] ) ? (int) $data['current_day'] : 0;
?>
<div class="container">

	<div class="card mt-4">
		<div class="card-header">
			<div id="calendar_head">
				<div class='row'>
					<div class="col-sm-4">
						<a href='#' class='btn btn-outline-secondary' id="prev_month_url"><i class="bi bi-arrow-left"></i></span></a>
					</div>
					<div class="col-sm-4 text-center">
						<h1 id="current_month"></h1>
					</div>
					<div class="col-sm-4 text-right">
						<a href='#' class='btn btn-outline-secondary' id="next_month_url"><i class="bi bi-arrow-right"></i></a>
					</div>
				</div>
			</div>
		</div>
		<div class="card-body" id="reserved_panel">

			<button type="button" id='up' class="btn btn-outline-secondary">
                <i class="bi bi-arrow-up"></i>
            </button>

			<table class="calendar_table my-4">
				<tbody>
				<tr>
					<td class="calendar_head_sunday"><?php esc_html_e( 'Sun', 'bluff-post' ) ?></td>
					<td class="calendar_head"><?php esc_html_e( 'Mon', 'bluff-post' ) ?></td>
					<td class="calendar_head"><?php esc_html_e( 'Tue', 'bluff-post' ) ?></td>
					<td class="calendar_head"><?php esc_html_e( 'Wed', 'bluff-post' ) ?></td>
					<td class="calendar_head"><?php esc_html_e( 'Thu', 'bluff-post' ) ?></td>
					<td class="calendar_head"><?php esc_html_e( 'Fri', 'bluff-post' ) ?></td>
					<td class="calendar_head_saturday"><?php esc_html_e( 'Sat', 'bluff-post' ) ?></td>
				</tr>
				<?php for ( $i = 0; $i < 5; $i ++ ) { ?>
					<tr>
						<?php for ( $j = 0; $j < 7; $j ++ ) { ?>
							<?php $n = $i * 7 + $j ?>
							<td class="calendar_cell" id="calendar_cell<?php esc_html_e( $n ) ?>">
								<div class="calendar_cell_date"></div>
								<div class="calendar_cell_content"></div>
								<div class="clearfix"></div>
								<ul>
								</ul>
							</td>
						<?php } ?>
					</tr>
				<?php } ?>
				</tbody>
			</table>

			<button type="button" id='down' class="btn btn-outline-secondary">
                <i class="bi bi-arrow-down"></i></button>
		</div>
	</div>

	<input type='hidden' id='year' name='year' value='<?php echo esc_attr( $current_year ) ?>'>
	<input type='hidden' id='month' name='month' value='<?php echo esc_attr( $current_month ) ?>'>
	<input type='hidden' id='day' name='day' value='<?php echo esc_html( $current_day ) ?>'>
</div>
