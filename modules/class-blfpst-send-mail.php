<?php

/**
 * send mail process.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Send_Mail {
	/**
	 * @var PHPMailer $phpmailer
	 */
	private $phpmailer;

	/**
	 * Constructor
	 */
	public function __construct() {
        global $wp_version;
        if ( !class_exists( "\\PHPMailer" ) ) {
            if ( version_compare( $wp_version, '5.5', '<' ) ) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                require_once ABSPATH . WPINC . '/class-smtp.php';

                $this->phpmailer = new PHPMailer();
            } else {
                // for WordPress5.5
                require_once( \ABSPATH . \WPINC . "/PHPMailer/PHPMailer.php" );
                require_once( \ABSPATH . \WPINC . "/PHPMailer/SMTP.php" );
                require_once( \ABSPATH . WPINC . '/PHPMailer/Exception.php' );

                $this->phpmailer = new PHPMailer\PHPMailer\PHPMailer();
            }
        } else {
            if ( version_compare( $wp_version, '5.5', '<' ) ) {
                $this->phpmailer = new PHPMailer();
            } else {
                // for WordPress5.5
                $this->phpmailer = new PHPMailer\PHPMailer\PHPMailer();
            }
        }
    }

	/**
	 * Destructor.
	 */
	public function __destruct() {
	}

	public function initialize() {
	}

	/**
	 * メール送信処理
	 *
	 * @param $recipients array
	 * @param $target_id integer
	 * @param $send_mail_id integer
	 * @param $subject string
	 * @param $text_content string
	 * @param $html_content string
	 * @param $parameter array
	 * @param $option array
	 *
	 * @return array
	 */
	public function send_mail_sync( $recipients, $target_id, $send_mail_id, $subject, $text_content, $html_content, $parameter, $option = array() ) {
		// result
		$results = array();
		$result  = 'non';
		$success = 0;
		$failure = 0;
		$locale  = get_locale();

		$send_mail_id = empty( $send_mail_id ) ? 0 : $send_mail_id;
		$target       = BLFPST_Targets_Controller::load_target_info( $target_id );
		$data_source  = BLFPST_Targets_Controller::data_source_object( $target );

		// parameter
		$from_name     = isset( $parameter['from_name'] ) ? stripslashes( $parameter['from_name'] ) : '';
		$from_address  = isset( $parameter['from_address'] ) ? stripslashes( $parameter['from_address'] ) : '';
		$reply_address = isset( $parameter['reply_address'] ) ? stripslashes( $parameter['reply_address'] ) : '';
		$post_id       = isset( $parameter['post_id'] ) ? stripslashes( $parameter['post_id'] ) : 0;

		// Host
		if ( isset( $_SERVER['SERVER_NAME'] ) ) {
			$host_name = $_SERVER['SERVER_NAME'];
		} else {
			$host_name = ( function_exists( 'gethostname' ) ) ? gethostname() : php_uname( 'n' );
		}

		if ( ! empty( $option['hostname'] ) ) {
			$this->phpmailer->Hostname = $option['hostname'];
		} else {
			$this->phpmailer->Hostname = $host_name;
		}

		$from_charset = get_bloginfo( 'charset' );

		// Content-Type:
		$charset = BLFPST::get_option( 'mail_content_charset', $from_charset );

		if ( '' !== $html_content ) {
			$content_type = sprintf( 'Content-Type: text/html; charset="%s"', $charset );
		} else {
			$content_type = sprintf( 'Content-Type: text/plain; charset="%s"', $charset );
		}

		$subject      = stripslashes( $subject );
		$html_content = stripslashes( $html_content );
		$text_content = stripslashes( $text_content );

		if ( $charset !== $from_charset ) {
			$subject      = mb_convert_encoding( $subject, $charset, $from_charset );
			$from_name    = mb_convert_encoding( $from_name, $charset, $from_charset );
			$html_content = mb_convert_encoding( $html_content, $charset, $from_charset );
			$text_content = mb_convert_encoding( $text_content, $charset, $from_charset );
		}

		// Mail From / From:
		$this->phpmailer->setFrom( $from_address, $from_name );

		// Replay-To:
		$this->phpmailer->clearReplyTos();
		if ( ! empty( $reply_address ) ) {
			$this->phpmailer->addReplyTo( $reply_address );
		}

		// Sender: / MAIL FROM
		$sender_address = BLFPST::get_option( 'error_address', '' );
		if ( ! empty( $sender_address ) ) {
			$this->phpmailer->Sender = $sender_address;
		}

		$mailer_type = BLFPST::get_option( 'mailer_type', 'mail' );

		$random_id     = mt_rand( 0, 2147483647 ) . '.' . mt_rand( 0, 2147483647 );
		$mail_page_url = empty( $post_id ) ? '' : get_permalink( $post_id, false );

		$mail_id = $send_mail_id;

		// Transmission limits
		$speed_limit_count = 0;
		$value = (int) BLFPST::get_option( 'transmission_speed_limit_count', '' );
		if ( '' !== $value ) {
			$speed_limit_count = $value;
		}

		$speed_limit_time = 0;
		$value = (int) BLFPST::get_option( 'transmission_speed_limit_time', '' );
		if ( '' !== $value ) {
			$speed_limit_time = $value;
		}

		/**
		 * Mailer種別
		 * sendmail : Send mail using the $Sendmail program. sendmailコマンド
		 * smtp : Send mail via SMTP.
		 * mail : Send mail using the PHP mail() function. default.
		 */
		switch ( $mailer_type ) {
			case 'sendmail': {
				$sendmail_path = BLFPST::get_option( 'sendmail_path', '' );
                $this->phpmailer->isSendmail();

				if ( ! empty( $sendmail_path ) ) {
					$this->phpmailer->Sendmail = $sendmail_path;
				}
			}
				break;

			case 'smtp': {
                $this->phpmailer->isSMTP();

				$value = BLFPST::get_option( 'smtp_host', '' );
				if ( ! empty( $value ) ) {
					$this->phpmailer->Host = $value;
				}

				$value = (int) BLFPST::get_option( 'smtp_port', '' );
				if ( '' !== $value ) {
					$this->phpmailer->Port = $value;
				}

				$value = BLFPST::get_option( 'smtp_auth', '' );
				if ( '' !== $value ) {
					$this->phpmailer->SMTPAuth = ($value === 'true');
				}

				if ( false !== $this->phpmailer->SMTPAuth ) {
                    $value = BLFPST::get_option( 'smtp_secure', '' );
                    $this->phpmailer->SMTPSecure = $value;

					$value = BLFPST::get_option( 'smtp_user_name', '' );
					if ( '' !== $value ) {
						$this->phpmailer->Username = $value;
					}

					$value = BLFPST::get_option( 'smtp_password', '' );
					if ( ! empty( $value ) ) {
						$this->phpmailer->Password = $value;
					}

					$value = BLFPST::get_option( 'smtp_auth_type', '' );
					if ( '' !== $value ) {
						$this->phpmailer->AuthType = $value;
					}

                    $value = BLFPST::get_option( 'smtp_secure', '' );
                    if ( '' !== $value ) {
                        $this->phpmailer->SMTPSecure = $value;
                    }
				}

				$value = BLFPST::get_option( 'smtp_realm', '' );
				if ( '' !== $value ) {
					$this->phpmailer->Realm = $value;
				}

				$value = BLFPST::get_option( 'smtp_workstation', '' );
				if ( '' !== $value ) {
					$this->phpmailer->Workstation = $value;
				}

				$value = BLFPST::get_option( 'smtp_timeout', '' );
				if ( '' !== $value ) {
					$this->phpmailer->Timeout = (int)$value;
				}

				$value = BLFPST::get_option( 'smtp_keep_alive', '' );
				if ( '' !== $value ) {
					$this->phpmailer->SMTPKeepAlive = (bool)$value;
				}
			}
				break;

			case 'mail':
			default:
				$this->phpmailer->isMail();
				break;
		}

		if ( isset( $option['x_mailer'] ) && ( '' !== $option['x_mailer'] ) ) {
			$this->phpmailer->XMailer = $option['x_mailer'];
		}

		// insertion keys
		$insertions = array(
			'%%user_id%%',
			'%%user_name%%',
			'%%user_last_name%%',
			'%%user_first_name%%',
			'%%user_mail_address%%',
			'%%mail_id%%', // send mail id
			'%%random_id%%', // random identifier
			'%%mail_page_url%%', // mail page url
		);

		$transmission_count = 0;

		// send loop
		for ( $i = 0; $i < count( $recipients ); $i ++ ) {
			/** @var object $recipient */
			$recipient    = $recipients[ $i ];
			$recipient_id = $recipient->{$data_source->id_field_name()};
			$recipient_to = $recipient->{$data_source->email_field_name()};

			$user_name = '';
			if ( isset( $recipient->{'user_name'} ) ) {
				$user_name = $recipient->{'user_name'};
			}

			$user_first_name_field = $recipient->{$data_source->user_first_name_field_name()};
			$user_first_name       = empty( $user_first_name_field ) ? '' : $user_first_name_field;

			if ( $data_source->user_last_name_field_name() !== '' ) {
				$user_last_name_field = $recipient->{$data_source->user_last_name_field_name()};
				$user_last_name       = empty( $user_last_name_field ) ? '' : $user_last_name_field;

				// Localize
				if ( 'ja' === $locale ) {
					$user_name = ( '' === $user_name ) ? $user_last_name . $user_first_name : $user_name;
				} else {
					$user_name = ( '' === $user_name ) ? $user_first_name . $user_last_name : $user_name;
				}
			} else {
				$user_name      = ( '' === $user_name ) ? $user_first_name : $user_name;
				$user_last_name = '';
			}

			$deliver_target = array(
				'%%user_id%%'           => $recipient_id,
				'%%user_name%%'         => $user_name,
				'%%user_last_name%%'    => $user_last_name,
				'%%user_first_name%%'   => $user_first_name,
				'%%user_mail_address%%' => $recipient_to,
				'%%mail_id%%'           => $mail_id,
				'%%random_id%%'         => $random_id,
				'%%mail_page_url%%'     => $mail_page_url,
			);

			// replace content
			$edited_text_content = $text_content;
			foreach ( $insertions as $insertion ) {
				$edited_text_content = str_replace( $insertion, $deliver_target[ $insertion ], $edited_text_content );
			}

			$edited_html_content = $html_content;
			foreach ( $insertions as $insertion ) {
				$edited_html_content = str_replace( $insertion, $deliver_target[ $insertion ], $edited_html_content );
				//$edited_html_content = wpautop( $edited_html_content );　// issue #156 157
			}

			// Header
			$headers = array(
				$content_type,
			);

			// Message-ID:
			$message_id = sprintf( '<%s.%d.%d.%d@%s>', $random_id, $send_mail_id, $i, $recipient_id, $host_name );

			// WordPress Mail
			if ( $this->mail( $recipient_to, $user_name, $message_id, $subject, $edited_text_content, $edited_html_content, $headers ) ) {
				$success ++;
			} // error
			else {
				$failure ++;
			}

			// Limits
			if ( $speed_limit_count > 0 ) {

				$transmission_count ++;

				if ( $transmission_count >= $speed_limit_count ) {

					if ( $speed_limit_time > 0 ) {
						sleep( $speed_limit_time );
					}

					$transmission_count = 0;
				}
			}
		}

		if ( $success > 0 ) {
			$result = 'success';
		} elseif ( ( 0 == $success ) && ( 0 < $failure ) ) {
			$result = 'failure';
		}

		$results['result']  = $result;
		$results['success'] = $success;
		$results['failure'] = $failure;

		return $results;
	}

	/**
	 * Send mail, similar to PHP's mail
	 *
	 * base wp_mail
	 *
	 * A true return value does not automatically mean that the user received the
	 * email successfully. It just only means that the method used was able to
	 * process the request without any errors.
	 *
	 * require from address
	 * CC,BCC not support
	 *
	 * @uses PHPMailer
	 *
	 * @param array $recipient_address email addresses to send message.
	 * @param string $recipient_name User name
	 * @param string $message_id Message-ID
	 * @param string $subject Email subject
	 * @param string $message Message contents
	 * @param string $html_content Message contents
	 * @param array $headers Optional. Additional headers.
	 * @param array $attachments Optional. Files to attach.
	 *
	 * @return bool Whether the email contents were sent successfully.
	 */
	private function mail( $recipient_address, $recipient_name, $message_id, $subject, $message, $html_content, $headers = array(), $attachments = array() ) {
		/**
		 * @var string $content_type
		 * @var string $charset
		 * @var string $boundary
		 */
		$content_type = '';
		$charset      = '';
		$boundary     = '';

		// Headers
		$temp_headers = $headers;
		$headers      = array();

		// If it's actually got contents
		if ( ! empty( $temp_headers ) ) {

			// Iterate through the raw headers
			foreach ( (array) $temp_headers as $header ) {

				// get boundary
				if ( strpos( $header, ':' ) === false ) {
					if ( false !== stripos( $header, 'boundary=' ) ) {
						$parts    = preg_split( '/boundary=/i', trim( $header ) );
						$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
					}
					continue;
				}

				// Explode them out
				list( $name, $content ) = explode( ':', trim( $header ), 2 );

				// Cleanup crew
				$name    = trim( $name );
				$content = trim( $content );

				switch ( strtolower( $name ) ) {

					case 'content-type':
						if ( strpos( $content, ';' ) !== false ) {

							list( $type, $charset_content ) = explode( ';', $content );
							$content_type = trim( $type );

							if ( false !== stripos( $charset_content, 'charset=' ) ) {
								$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );

							} elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
								// BOUNDARY
								$boundary = trim( str_replace( array(
									'BOUNDARY=',
									'boundary=',
									'"',
								), '', $charset_content ) );
								$charset  = '';
							}

							// Avoid setting an empty $content_type.
						} elseif ( '' !== trim( $content ) ) {
							$content_type = trim( $content );
						}
						break;

					default:
						// Add it to our grand headers array
						$headers[ trim( $name ) ] = trim( $content );
						break;
				}
			}
		}

		// Empty out the values that may be set
		$this->phpmailer->clearAllRecipients();
		$this->phpmailer->clearAttachments();
		$this->phpmailer->clearCustomHeaders();

		// Rcpt To / To:
		try {
			$this->phpmailer->addAddress( $recipient_address, $recipient_name );
		} catch ( phpmailerException $e ) {
			error_log( 'phpmailer->addAddress() ' . $e->getMessage() );
			return false;
		}

		// Message-ID
		if ( isset( $message_id ) && ( '' !== $message_id ) ) {
			$this->phpmailer->MessageID = $message_id;
		}

		// Subject:
		$this->phpmailer->Subject = $subject;

		// Body
		$this->phpmailer->Body = $message;

		// Set Content-Type and charset
		// If we don't have a content-type from the input headers
		$content_type                 = ( isset( $content_type ) && '' !== $content_type ) ? $content_type : 'text/plain';
		$this->phpmailer->ContentType = $content_type;

		// Set whether it's plaintext, depending on $content_type
		if ( 'text/html' == $content_type ) {
			$this->phpmailer->Body    = $html_content;
			$this->phpmailer->AltBody = $message;
			$this->phpmailer->isHTML( true );
		}

		// If we don't have a charset from the input headers
		$charset                  = ( isset( $charset ) && '' !== $charset ) ? $charset : get_bloginfo( 'charset' );
		$this->phpmailer->CharSet = $charset;

		// Set custom headers
		if ( ! empty( $headers ) ) {

			foreach ( $headers as $name => $content ) {
				$this->phpmailer->addCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
			}

			// multipart
			if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
				$this->phpmailer->addCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
			}
		}

		// attachments
		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
            try {
					$this->phpmailer->addAttachment( $attachment );
				} catch ( phpmailerException $e ) {
					continue;
				}
            }
        }

		// Send!
		try {
			return $this->phpmailer->send();
		} catch ( phpmailerException $e ) {
			error_log( 'phpmailer->send() ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * @param BLFPST_Model_Send_Mail $mail
	 * @param integer $send_mail_id
	 * @param integer $user_id
	 *
	 * @return void
	 */
	public static function start_send( $mail, $send_mail_id, $user_id ) {
		$send_result = 'failure';

		if ( 0 == $send_mail_id ) {
			$message = esc_html__( 'Invalid call has occurred. Send e-mail does not start.', 'bluff-post' );
			self::start_send_error_log( $message, $user_id, $send_mail_id );

		} else {
			if ( empty( $mail ) ) {
				$mail = BLFPST_Send_Mails_Controller::load_mail( $send_mail_id );
			}
			$target_id = $mail->target_id;

			if ( ( $mail->id > 0 ) && ( $target_id > 0 ) ) {

				if ( $mail->is_waiting() ) {

					$blog_charset = get_bloginfo( 'charset' );
					$charset      = BLFPST::get_option( 'mail_content_charset', $blog_charset );

					// for Lock send data
					$mail->update_start_send( $send_mail_id, 0, $charset, '' );

					$recipients = BLFPST_Targets_Controller::execute_query_recipient( $target_id, - 1, 0, $sql );

					if ( false !== $recipients ) {

						// for update data
						$mail->update_start_send( $send_mail_id, count( $recipients ), $charset, $sql );

						if ( count( $recipients ) > 0 ) {

							$message = sprintf( esc_html__( 'It started \'%s\' mail transmission request processing.', 'bluff-post' ), $mail->subject );
							BLFPST_Logs_Controller::notice_log( esc_html__( 'E-mail transmission request has started.', 'bluff-post' ), $message, 'BLFPST_Send_Mail::start_send', $user_id, $send_mail_id );

							// 送信処理
							$send_mail = new BLFPST_Send_Mail();

							$results = $send_mail->send_mail_sync(
								$recipients,
								$target_id,
								$send_mail_id,
								$mail->subject,
								$mail->text_content,
								$mail->html_content,
								array(
									'from_name'     => $mail->from_name,
									'from_address'  => $mail->from_address,
									'reply_address' => $mail->reply_address,
									'post_id'       => $mail->post_id,
								)
							);

							$message = sprintf( esc_html__( 'It finished \'%s\' mail transmission request processing.', 'bluff-post' ), $mail->subject );
							BLFPST_Logs_Controller::notice_log( esc_html__( 'E-mail transmission request has finished.', 'bluff-post' ), $message, 'BLFPST_Send_Mail::start_send', $user_id, $send_mail_id );

							if ( 'failure' === $results['result'] ) {

								$recipient_count = count( $recipients );
								$success_count   = (int) $results['success'];
								$failure_count   = (int) $results['failure'];
								$request_count   = $success_count + $failure_count;
								$left_count      = $recipient_count - $request_count;

								$message = sprintf( esc_html__( 'Error has occurred in \'%s\' mail transmission request. total %d sent %d (success %d failure %d) untreated %d SQL %s.', 'bluff-post' ),
									$mail->subject,
									$recipient_count,
									$request_count,
									$success_count,
									$failure_count,
									$left_count,
									$sql
								);

								self::start_send_error_log( $message, $user_id, $send_mail_id );
							}

							$mail->update_finish_send( $send_mail_id, $results['result'], (int) $results['success'], (int) $results['failure'] );

							return;

						} else {
							$send_result = 'success';
							$message     = sprintf( esc_html__( 'The \'%s\' e-mail transmission request was 0 destination.', 'bluff-post' ), $mail->subject );
							BLFPST_Logs_Controller::log( BLFPST_Logs_Controller::$notice, esc_html__( 'No target', 'bluff-post' ), $message, 'BLFPST_Send_Mail::start_send', 0, $send_mail_id );

							$mail->update_finish_send( $send_mail_id, $send_result, 0, 0 );
						}
					} else {
						$message = esc_html__( 'Receivers data as failed to read from DB. Send e-mail does not start.', 'bluff-post' );
						self::start_send_error_log( $message, $user_id, $send_mail_id );

						$mail->update_start_send( $send_mail_id, 0, $charset, '' );
						$mail->update_finish_send( $send_mail_id, $send_result, 0, 0 );
					}
				}
			} else {
				if ( $mail->id <= 0 ) {
					$message = esc_html__( 'The send data is invalid. Send e-mail does not start.', 'bluff-post' );
					self::start_send_error_log( $message, $user_id, $send_mail_id );
				} elseif ( $target_id <= 0 ) {
					$message = esc_html__( 'The recipients data is invalid. Send e-mail does not start.', 'bluff-post' );
					self::start_send_error_log( $message, $user_id, $send_mail_id );
				}

				$mail->update_start_send( $send_mail_id, 0, '', '' );
				$mail->update_finish_send( $send_mail_id, $send_result, 0, 0 );
			}
		}
	}

	/**
	 * @param string $response_message
	 * @param int $user_id
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private static function start_send_error_log( $response_message, $user_id, $send_mail_id ) {
		BLFPST_Logs_Controller::error_log( __( 'Mail transmission request error', 'bluff-post' ), $response_message, 'BLFPST_Send_Mail::start_send', $user_id, $send_mail_id );
	}
}
