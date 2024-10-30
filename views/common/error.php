<?php
/**
 * mail information view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */

/**
 * @var WP_Error $errors
 */
$errors = $data['errors'];
?>
<div class="container">
	<div class="page-header">
		<h2><?php esc_html_e( 'Failure', 'bluff-post' ) ?></h2>
	</div>

	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">

			<?php if ( ! empty( $errors ) ) : ?>
				<?php if ( 0 < count( $errors->get_error_messages() ) ) : ?>
					<div class="alert alert-danger" role="alert">
						<ul>
							<?php foreach ( $errors->get_error_messages() as $error ) : ?>
							<li><i class="bi bi-exclamation-triangle-fill"></i> <?php echo esc_html( $error ) ?>
								<?php endforeach ?>
						</ul>
					</div>
				<?php endif ?>
			<?php endif ?>
		</div>
	</div>
</div>
