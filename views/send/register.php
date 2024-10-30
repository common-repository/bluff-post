<?php
/**
 * post register mail view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var string $message
 */
$title = empty( $data['title'] ) ? '' : $data['title'];
$message = empty( $data['message'] ) ? '' : $data['message'];
?>
<div class="container">
    <h1 class="my-4"><?php echo esc_html( $title ) ?></h1>
    <hr class="my-4">

    <div class="row justify-content-sm-center">
        <div class="col-sm-8">
            <div class="alert alert-success" role="alert">
                <?php echo esc_html( $message ) ?>
            </div>
            <div class="text-right">
                <a class="btn btn-secondary" href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-reserves' ) ) ?>"><?php esc_html_e( 'Reservation list', 'bluff-post' ) ?></a>
            </div>
        </div>
    </div>

</div>
