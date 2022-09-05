<?php

namespace Essential_Addons_Elementor\Traits;

use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait Login_Registration is responsible for login or registering user using custom login | register widget.
 * @package Essential_Addons_Elementor\Traits
 */
trait Login_Registration {
	/**
	 * @var bool
	 */
	public static $send_custom_email = false;
	public static $send_custom_email_admin = false;
	public static $send_custom_email_lostpassword = false;
	/**
	 * It will contain all email related options like email subject, content, email content type etc.
	 * @var array   $email_options {
	 *      Used to build wp_mail().
	 * @type string $template_type The type of the email template; custom | default.
	 * @type string $subject       The subject of the email.
	 * @type string $message       The body of the email.
	 * @type string $content_type  The type of the email body; plain | html
	 * }
	 */
	public static $email_options = [];
	public static $email_options_lostpassword = [];

	public function login_or_register_user() {
		do_action( 'eael/login-register/before-processing-login-register', $_POST );
		// login or register form?
		if ( isset( $_POST['eael-login-submit'] ) ) {
			$this->log_user_in();
		} else if ( isset( $_POST['eael-register-submit'] ) ) {
			$this->register_user();
		} else if ( isset( $_POST['eael-lostpassword-submit'] ) ) {
			$this->send_password_reset();
		} else if ( isset( $_POST['eael-resetpassword-submit'] ) ) {
			$this->reset_password();
		}
		do_action( 'eael/login-register/after-processing-login-register', $_POST );

	}

	/**
	 * It logs the user in when the login form is submitted normally without AJAX.
	 */
	public function log_user_in() {
		$ajax   = wp_doing_ajax();
		// before even thinking about login, check security and exit early if something is not right.
		$page_id = 0;
		if ( ! empty( $_POST['page_id'] ) ) {
			$page_id = intval( $_POST['page_id'], 10 );
		} else {
			$err_msg = __( 'Page ID is missing', 'essential-addons-for-elementor-lite' );
		}

		$widget_id = 0;
		if ( ! empty( $_POST['widget_id'] ) ) {
			$widget_id = sanitize_text_field( $_POST['widget_id'] );
		} else {
			$err_msg = __( 'Widget ID is missing', 'essential-addons-for-elementor-lite' );
		}

		if (!empty( $err_msg )){
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_login_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}


		if ( empty( $_POST['eael-login-nonce'] ) ) {
			$err_msg = __( 'Insecure form submitted without security token', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_login_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		if ( ! wp_verify_nonce( $_POST['eael-login-nonce'], 'eael-login-action' ) ) {
			$err_msg = __( 'Security token did not match', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_login_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}
		$settings = $this->lr_get_widget_settings( $page_id, $widget_id);

		if ( is_user_logged_in() ) {
			$err_msg = isset( $settings['err_loggedin'] ) ? $settings['err_loggedin'] : __( 'You are already logged in', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_login_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		do_action( 'eael/login-register/before-login' );

		$widget_id = ! empty( $_POST['widget_id'] ) ? sanitize_text_field( $_POST['widget_id'] ) : '';
		if ( isset( $_POST['g-recaptcha-enabled'] ) && ! $this->lr_validate_recaptcha() ) {
			$err_msg = isset( $settings['err_recaptcha'] ) ? $settings['err_recaptcha'] : __( 'You did not pass recaptcha challenge.', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_login_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            } // vail early if recaptcha failed
		}

		$user_login = ! empty( $_POST['eael-user-login'] ) ? sanitize_text_field( $_POST['eael-user-login'] ) : '';
		if ( is_email( $user_login ) ) {
			$user_login = sanitize_email( $user_login );
		}

		$password   = ! empty( $_POST['eael-user-password'] ) ? sanitize_text_field( $_POST['eael-user-password'] ) : '';
		$rememberme = ! empty( $_POST['eael-rememberme'] ) ? sanitize_text_field( $_POST['eael-rememberme'] ) : '';

		$credentials = [
			'user_login'    => $user_login,
			'user_password' => $password,
			'remember'      => ( 'forever' === $rememberme ),
		];
		$user_data   = wp_signon( $credentials );

		if ( is_wp_error( $user_data ) ) {
			$err_msg = '';
			if ( isset( $user_data->errors['invalid_email'][0] ) ) {
				$err_msg = isset( $settings['err_email'] ) ? $settings['err_email'] : __( 'Invalid Email. Please check your email or try again with your username.', 'essential-addons-for-elementor-lite' );
			} elseif ( isset( $user_data->errors['invalid_username'][0] )) {
				$err_msg = isset( $settings['err_username'] ) ? $settings['err_username'] : __( 'Invalid Username. Please check your username or try again with your email.', 'essential-addons-for-elementor-lite' );

			} elseif ( isset( $user_data->errors['incorrect_password'][0] ) || isset( $user_data->errors['empty_password'][0] ) ) {
				$err_msg = isset( $settings['err_pass'] ) ? $settings['err_pass'] : __( 'Invalid Password', 'essential-addons-for-elementor-lite' );

			}

			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_login_error_' . $widget_id, $err_msg, false );
		} else {
			wp_set_current_user( $user_data->ID, $user_login );
			do_action( 'wp_login', $user_data->user_login, $user_data );
			do_action( 'eael/login-register/after-login', $user_data->user_login, $user_data );
			if ( $ajax ) {

				$data = [
					'message' => isset( $settings['success_login'] ) ? $settings['success_login'] : __( 'You are logged in successfully', 'essential-addons-for-elementor-lite' ),
				];
				if ( ! empty( $_POST['redirect_to'] ) ) {
					$data['redirect_to'] = esc_url_raw( $_POST['redirect_to'] );
				}
				wp_send_json_success( $data );
			}

			if ( ! empty( $_POST['redirect_to'] ) ) {
				wp_safe_redirect( esc_url_raw( $_POST['redirect_to'] ) );
				exit();
			}
		}
        if (isset($_SERVER['HTTP_REFERER'])) {
            wp_safe_redirect($_SERVER['HTTP_REFERER']);
            exit();
        }
	}

	/**
	 * It register the user in when the registration form is submitted normally without AJAX.
	 */
	public function register_user() {
		$ajax = wp_doing_ajax();

		// validate & sanitize the request data
		if ( empty( $_POST['eael-register-nonce'] ) ) {
			if ( $ajax ) {
				wp_send_json_error( __( 'Insecure form submitted without security token', 'essential-addons-for-elementor-lite' ) );
			}

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}
		if ( ! wp_verify_nonce( $_POST['eael-register-nonce'], 'eael-register-action' ) ) {
			if ( $ajax ) {
				wp_send_json_error( __( 'Security token did not match', 'essential-addons-for-elementor-lite' ) );
			}

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}
		$page_id = $widget_id = 0;
        if ( ! empty( $_POST['page_id'] ) ) {
            $page_id = intval( $_POST['page_id'] );
        } else {
            $err_msg = __( 'Page ID is missing', 'essential-addons-for-elementor-lite' );
        }
        if ( ! empty( $_POST['widget_id'] ) ) {
            $widget_id = sanitize_text_field( $_POST['widget_id'] );
        } else {
            $err_msg = __( 'Widget ID is missing', 'essential-addons-for-elementor-lite' );
        }

        if (!empty( $err_msg )){
            if ( $ajax ) {
                wp_send_json_error( $err_msg );
            }
            update_option( 'eael_register_errors_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
            return false;
        }



		$settings = $this->lr_get_widget_settings( $page_id, $widget_id);

		if ( is_user_logged_in() ) {
			$err_msg = isset( $settings['err_loggedin'] ) ? $settings['err_loggedin'] : __( 'You are already logged in.', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		do_action( 'eael/login-register/before-register' );

		// prepare the data
		$errors               = [];
		$registration_allowed = get_option( 'users_can_register' );
		$protocol             = is_ssl() ? "https://" : "http://";
		$url                  = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		// vail early if reg is closed.
		if ( ! $registration_allowed ) {
			$errors['registration'] = __( 'Registration is closed on this site', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $errors['registration'] );
			}

            //update_option( 'eael_register_errors_' . $widget_id, $errors, false );// if we redirect to other page, we dont need to save value
            wp_safe_redirect( site_url( 'wp-login.php?registration=disabled' ) );
			exit();
		}
		// prepare vars and flag errors
		if ( isset( $_POST['eael_tnc_active'] ) && empty( $_POST['eael_accept_tnc'] ) ) {
			$errors['terms_conditions'] =  isset( $settings['err_tc'] ) ? $settings['err_tc'] : __( 'You did not accept the Terms and Conditions. Please accept it and try again.', 'essential-addons-for-elementor-lite' );
		}
		if ( isset( $_POST['g-recaptcha-enabled'] ) && ! $this->lr_validate_recaptcha() ) {
			$errors['recaptcha'] = isset( $settings['err_recaptcha'] ) ? $settings['err_recaptcha'] : __( 'You did not pass recaptcha challenge.', 'essential-addons-for-elementor-lite' );
		}

		if ( ! empty( $_POST['email'] ) && is_email( $_POST['email'] ) ) {
			$email = sanitize_email( $_POST['email'] );
			if ( email_exists( $email ) ) {
				$errors['email'] = isset( $settings['err_email_used'] ) ? $settings['err_email_used'] : __( 'The provided email is already registered with other account. Please login or reset password or use another email.', 'essential-addons-for-elementor-lite' );
			}
		} else {
			$errors['email'] = isset( $settings['err_email_missing'] ) ? $settings['err_email_missing'] : __( 'Email is missing or Invalid', 'essential-addons-for-elementor-lite' );
		}

		// if user provided user name, validate & sanitize it
		if ( isset( $_POST['user_name'] ) ) {
			$username = sanitize_user( $_POST['user_name'] );
			if ( ! validate_username( $username ) || mb_strlen( $username ) > 60 ) {
				$errors['user_name'] = isset( $settings['err_username'] ) ? $settings['err_username'] : __( 'Invalid username provided.', 'essential-addons-for-elementor-lite' );
			}elseif(username_exists( $username )){
				$errors['user_name'] = isset( $settings['err_username_used'] ) ? $settings['err_username_used'] : __( 'The username already registered.', 'essential-addons-for-elementor-lite' );

			}
		} else {
			// user has not provided username, so generate one from the provided email.
			if ( empty( $errors['email'] ) && isset( $email ) ) {
				$username = $this->generate_username_from_email( $email );
			}
		}

		// Dynamic Password Generation
		$is_pass_auto_generated = false; // emailing is must for autogen pass
		if ( ! empty( $_POST['password'] ) ) {
			$password = sanitize_text_field( $_POST['password'] );
		} else {
			$password               = wp_generate_password();
			$is_pass_auto_generated = true;
		}
		if ( isset( $_POST['confirm_pass'] ) ) {
			$confirm_pass = sanitize_text_field( $_POST['confirm_pass'] );
			if ( $confirm_pass !== $password ) {
				$errors['confirm_pass'] = isset( $settings['err_conf_pass'] ) ? $settings['err_conf_pass'] : __( 'The confirmed password did not match.', 'essential-addons-for-elementor-lite' );
			}
		}

		// if any error found, abort
		if ( ! empty( $errors ) ) {
			if ( $ajax ) {
				$err_msg = '<ol>';
				foreach ( $errors as $error ) {
					$err_msg .= "<li>{$error}</li>";
				}
				$err_msg .= '</ol>';
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_register_errors_' . $widget_id, $errors, false );
			wp_safe_redirect( esc_url( $url ) );
			exit();
		}

		/*------General Mail Related Stuff------*/
		self::$email_options['username']            = $username;
		self::$email_options['password']            = $password;
		self::$email_options['email']               = $email;
		self::$email_options['firstname']           = '';
		self::$email_options['lastname']            = '';
		self::$email_options['website']             = '';
		self::$email_options['password_reset_link'] = '';

		// handle registration...
		$user_data = [
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $email,
		];

		if ( ! empty( $_POST['first_name'] ) ) {
			$user_data['first_name'] = self::$email_options['firstname'] = sanitize_text_field( $_POST['first_name'] );
		}
		if ( ! empty( $_POST['last_name'] ) ) {
			$user_data['last_name'] = self::$email_options['lastname'] = sanitize_text_field( $_POST['last_name'] );
		}
		if ( ! empty( $_POST['website'] ) ) {
			$user_data['user_url'] = self::$email_options['website'] = esc_url_raw( $_POST['website'] );
		}

		$register_actions    = [];
		$custom_redirect_url = '';
		if ( !empty( $settings) ) {
			$register_actions    = ! empty( $settings['register_action'] ) ? (array) $settings['register_action'] : [];
			$custom_redirect_url = ! empty( $settings['register_redirect_url']['url'] ) ? $settings['register_redirect_url']['url'] : '/';
			if ( ! empty( $settings['register_user_role'] ) ) {
				$user_data['role'] = sanitize_text_field( $settings['register_user_role'] );
			}


			// set email related stuff
			/*------User Mail Related Stuff------*/
			if ( $is_pass_auto_generated || ( in_array( 'send_email', $register_actions ) && 'custom' === $settings['reg_email_template_type'] ) ) {
				self::$send_custom_email = true;
			}
			if ( isset( $settings['reg_email_subject'] ) ) {
				self::$email_options['subject'] = $settings['reg_email_subject'];
			}
			if ( isset( $settings['reg_email_message'] ) ) {
				self::$email_options['message'] = $settings['reg_email_message'];
			}
			if ( isset( $settings['reg_email_content_type'] ) ) {
				self::$email_options['headers'] = 'Content-Type: text/' . $settings['reg_email_content_type'] . '; charset=UTF-8' . "\r\n";
			}


			/*------Admin Mail Related Stuff------*/
			self::$send_custom_email_admin = ( ! empty( $settings['reg_admin_email_template_type'] ) && 'custom' === $settings['reg_admin_email_template_type'] );
			if ( isset( $settings['reg_admin_email_subject'] ) ) {
				self::$email_options['admin_subject'] = $settings['reg_admin_email_subject'];
			}
			if ( isset( $settings['reg_admin_email_message'] ) ) {
				self::$email_options['admin_message'] = $settings['reg_admin_email_message'];
			}
			if ( isset( $settings['reg_admin_email_content_type'] ) ) {
				self::$email_options['admin_headers'] = 'Content-Type: text/' . $settings['reg_admin_email_content_type'] . '; charset=UTF-8' . "\r\n";
			}
		}


		$user_data = apply_filters( 'eael/login-register/new-user-data', $user_data );

		do_action( 'eael/login-register/before-insert-user', $user_data );
		$user_default_role = get_option( 'default_role' );

        if(!empty($user_default_role) && empty($user_data['role'])){
            $user_data['role'] = $user_default_role;
        }

        if ('administrator' == strtolower($user_data['role'])) {
            $user_data['role'] = !empty($settings['register_user_role']) ? $settings['register_user_role'] : get_option('default_role');
        }

		$user_id = wp_insert_user( $user_data );

		do_action( 'eael/login-register/after-insert-user', $user_id, $user_data );

		if ( is_wp_error( $user_id ) ) {
			// error happened during user creation
			$errors['user_create'] = isset( $settings['err_unknown'] ) ? $settings['err_unknown'] :  __( 'Sorry, something went wrong. User could not be registered.', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $errors['user_create'] );
			}
			update_option( 'eael_register_errors_' . $widget_id, $errors, false );
			wp_safe_redirect( esc_url( $url ) );
			exit();
		}


		// generate password reset link for autogenerated password
		if ( $is_pass_auto_generated ) {
			update_user_option( $user_id, 'default_password_nag', true, true ); // Set up the password change nag.
			$user = get_user_by( 'id', $user_id );
			$key  = get_password_reset_key( $user );
			if ( ! is_wp_error( $key ) ) {
				self::$email_options['password_reset_link'] = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n";
			}
		}

		$admin_or_both = $is_pass_auto_generated || in_array( 'send_email', $register_actions ) ? 'both' : 'admin';


		/**
		 * Fires after a new user registration has been recorded.
		 *
		 * @param int $user_id ID of the newly registered user.
		 *
		 * @since 4.4.0
		 */
		remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
		do_action( 'register_new_user', $user_id );

		wp_new_user_notification( $user_id, null, $admin_or_both );

		// success & handle after registration action as defined by user in the widget
		if ( ! $ajax && !in_array( 'redirect', $register_actions ) ) {
			update_option( 'eael_register_success_' . $widget_id, 1, false );
		}


		// Handle after registration action
		$data = [
			'message' => isset( $settings['success_register'] ) ? $settings['success_register'] : __( 'Your registration completed successfully.', 'essential-addons-for-elementor-lite' ),
		];
		// should user be auto logged in?
		if ( in_array( 'auto_login', $register_actions ) && ! is_user_logged_in() ) {
			wp_signon( [
				'user_login'    => $username,
				'user_password' => $password,
				'remember'      => true,
			] );
            $this->delete_registration_options($widget_id);

			if ( $ajax ) {
				if ( in_array( 'redirect', $register_actions ) ) {
					$data['redirect_to'] = $custom_redirect_url;
				}
				wp_send_json_success( $data );
			}

			// if custom redirect not available then refresh the current page to show admin bar
			if ( ! in_array( 'redirect', $register_actions ) ) {
				wp_safe_redirect( esc_url( $url ) );
				exit();
			}
		}

		// custom redirect?
		if ( $ajax ) {
			if ( in_array( 'redirect', $register_actions ) ) {
				$data['redirect_to'] = $custom_redirect_url;
			}
			wp_send_json_success( $data );
		}

		if ( in_array( 'redirect', $register_actions ) ) {
			wp_safe_redirect( $custom_redirect_url );
			exit();
		}

        if (isset($_SERVER['HTTP_REFERER'])) {
            wp_safe_redirect($_SERVER['HTTP_REFERER']);
            exit();
        }

	}

	/**
	 * It sends the user an email with reset password link. Lost Password form is submitted normally without AJAX.
	 */
	public function send_password_reset() {
		$ajax   = wp_doing_ajax();
		// before even thinking about sending mail, check security and exit early if something is not right.
		$page_id = 0;
		if ( ! empty( $_POST['page_id'] ) ) {
			$page_id = intval( $_POST['page_id'], 10 );
		} else {
			$err_msg = esc_html__( 'Page ID is missing', 'essential-addons-for-elementor-lite' );
		}

		$widget_id = 0;
		if ( ! empty( $_POST['widget_id'] ) ) {
			$widget_id = sanitize_text_field( $_POST['widget_id'] );
		} else {
			$err_msg = esc_html__( 'Widget ID is missing', 'essential-addons-for-elementor-lite' );
		}

		if (!empty( $err_msg )){
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_losstpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}


		if ( empty( $_POST['eael-lostpassword-nonce'] ) ) {
			$err_msg = esc_html__( 'Insecure form submitted without security token', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_lostpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		if ( ! wp_verify_nonce( $_POST['eael-lostpassword-nonce'], 'eael-lostpassword-action' ) ) {
			$err_msg = esc_html__( 'Security token did not match', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_lostpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}
		
		$settings = $this->lr_get_widget_settings( $page_id, $widget_id);

		if ( is_user_logged_in() ) {
			$err_msg = isset( $settings['err_loggedin'] ) ? __( wp_strip_all_tags( $settings['err_loggedin'] ), 'essential-addons-for-elementor-lite' ) : esc_html__( 'You are already logged in', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_lostpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		do_action( 'eael/login-register/before-lostpassword-email' );

		$widget_id = ! empty( $_POST['widget_id'] ) ? sanitize_text_field( $_POST['widget_id'] ) : '';

		$user_login = ! empty( $_POST['eael-user-lostpassword'] ) ? sanitize_text_field( $_POST['eael-user-lostpassword'] ) : '';
		if ( is_email( $user_login ) ) {
			$user_login = sanitize_email( $user_login );
		}

		// set email related stuff
		if ( 'custom' === $settings['lostpassword_email_template_type'] ) {
			self::$send_custom_email_lostpassword = true;
		}
		if ( isset( $settings['lostpassword_email_subject'] ) ) {
			self::$email_options_lostpassword['subject'] = __( wp_strip_all_tags( $settings['lostpassword_email_subject'] ), 'essential-addons-for-elementor-lite' );
		}
		if ( isset( $settings['lostpassword_email_message'] ) ) {
			self::$email_options_lostpassword['message'] = __( $settings['lostpassword_email_message'], 'essential-addons-for-elementor-lite' );
		}
		if ( isset( $settings['lostpassword_email_content_type'] ) ) {
			self::$email_options_lostpassword['headers'] = 'Content-Type: text/' . wp_strip_all_tags( $settings['lostpassword_email_content_type'] ) . '; charset=UTF-8' . "\r\n";
		}

		if ( isset($_SERVER['HTTP_REFERER']) ) {
			self::$email_options_lostpassword['http_referer'] = esc_url_raw( strtok( $_SERVER['HTTP_REFERER'], '?' ) );
		}
		
		if ( isset($page_id) ) {
			self::$email_options_lostpassword['page_id'] = sanitize_text_field( $page_id );
		}
		
		if ( isset($widget_id) ) {
			self::$email_options_lostpassword['widget_id'] = sanitize_text_field( $widget_id );
		}

		add_filter( 'retrieve_password_notification_email', [ $this, 'eael_retrieve_password_notification_email' ], 10, 4 );
		
		$results = retrieve_password( $user_login );
		
		if ( is_wp_error( $results ) ) {
			$err_msg = '';
			if ( isset( $results->errors['invalidcombo'][0] ) ) {
				$err_msg = esc_html__( 'There is no account with that username or email address.', 'essential-addons-for-elementor-lite' );
			}

			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_lostpassword_error_' . $widget_id, $err_msg, false );
		} else {
			$data = [
				'message' => esc_html__( 'Check your email for the confirmation link.', 'essential-addons-for-elementor-lite' ),
			];

			if ( $ajax ) {
				if ( ! empty( $_POST['redirect_to'] ) ) {
					$data['redirect_to'] = esc_url_raw( $_POST['redirect_to'] );
				}
				wp_send_json_success( $data );
			} else {
				update_option( 'eael_lostpassword_success_' . $widget_id, $data['message'], false );
			}

			if ( ! empty( $_POST['redirect_to'] ) ) {
				wp_safe_redirect( esc_url_raw( $_POST['redirect_to'] ) );
				exit();
			}
		}
        if (isset($_SERVER['HTTP_REFERER'])) {
            wp_safe_redirect($_SERVER['HTTP_REFERER']);
            exit();
        }
	}
	
	/**
	 * It reset the password with user submitted new password.
	 */
	public function reset_password() {
		$ajax   = wp_doing_ajax();
		$page_id = 0;
		if ( ! empty( $_POST['page_id'] ) ) {
			$page_id = intval( $_POST['page_id'], 10 );
		} else {
			$err_msg = esc_html__( 'Page ID is missing', 'essential-addons-for-elementor-lite' );
		}

		$widget_id = 0;
		if ( ! empty( $_POST['widget_id'] ) ) {
			$widget_id = sanitize_text_field( $_POST['widget_id'] );
		} else {
			$err_msg = esc_html__( 'Widget ID is missing', 'essential-addons-for-elementor-lite' );
		}

		$rp_data = [
			'rp_key' => ! empty( $_POST['rp_key'] ) ? sanitize_text_field( $_POST['rp_key'] ) : '',
			'rp_login' => ! empty( $_POST['rp_login'] ) ? sanitize_text_field( $_POST['rp_login'] ) : '',
		];

		update_option( 'eael_resetpassword_rp_data_' . esc_attr( $widget_id ), maybe_serialize( $rp_data ), false );

		update_option( 'eael_show_reset_password_on_form_submit_' . $widget_id, true, false );

		if (!empty( $err_msg )){
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_resetpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		if ( empty( $_POST['eael-resetpassword-nonce'] ) ) {
			$err_msg = esc_html__( 'Insecure form submitted without security token', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_resetpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		if ( ! wp_verify_nonce( $_POST['eael-resetpassword-nonce'], 'eael-resetpassword-action' ) ) {
			$err_msg = esc_html__( 'Security token did not match', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_resetpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}
		$settings = $this->lr_get_widget_settings( $page_id, $widget_id);

		if ( is_user_logged_in() ) {
			$err_msg = isset( $settings['err_loggedin'] ) ? __( wp_strip_all_tags( $settings['err_loggedin'] ), 'essential-addons-for-elementor-lite' ) : esc_html__( 'You are already logged in', 'essential-addons-for-elementor-lite' );
			if ( $ajax ) {
				wp_send_json_error( $err_msg );
			}
			update_option( 'eael_resetpassword_error_' . $widget_id, $err_msg, false );

            if (isset($_SERVER['HTTP_REFERER'])) {
                wp_safe_redirect($_SERVER['HTTP_REFERER']);
                exit();
            }
		}

		do_action( 'eael/login-register/before-resetpassword-email' );

		$widget_id = ! empty( $_POST['widget_id'] ) ? sanitize_text_field( $_POST['widget_id'] ) : '';

		// Check if password is one or all empty spaces.
		$errors = [];
		if ( ! empty( $_POST['eael-pass1'] ) ) {
			$post_eael_pass1 = trim( $_POST['eael-pass1'] );

			if ( empty( $post_eael_pass1 ) ) {
				$errors['password_reset_empty_space'] = isset( $settings['err_pass'] ) ? __( wp_strip_all_tags( $settings['err_pass'] ), 'essential-addons-for-elementor-lite' ) : esc_html__( 'The password cannot be a space or all spaces.', 'essential-addons-for-elementor-lite' );
			}
		} else {
			if ( empty( $_POST['eael-pass1'] ) ) {
				$errors['password_reset_empty_space'] = isset( $settings['err_pass'] ) ? __( wp_strip_all_tags( $settings['err_pass'] ), 'essential-addons-for-elementor-lite' ) : esc_html__( 'The password cannot be a space or all spaces.', 'essential-addons-for-elementor-lite' );
			}
		}

		if( ! empty( $_POST['eael-pass1'] ) && strlen( trim( $_POST['eael-pass1'] ) ) == 0 ){
			$errors['password_reset_empty'] = esc_html__( 'The password cannot be empty.', 'essential-addons-for-elementor-lite' );
		}
		
		// Check if password fields do not match.
		if ( ! empty( $_POST['eael-pass1'] ) && $_POST['eael-pass2'] !== $_POST['eael-pass1'] ) {
			$errors['password_reset_mismatch'] = isset( $settings['err_conf_pass'] ) ? __( wp_strip_all_tags( $settings['err_conf_pass'] ), 'essential-addons-for-elementor-lite' ) : esc_html__( 'The passwords do not match.', 'essential-addons-for-elementor-lite' );
		}

		if ( ( ! count( $errors ) ) && isset( $_POST['eael-pass1'] ) && ! empty( $_POST['eael-pass1'] ) ) {
			$rp_login = isset( $_POST['rp_login']) ? sanitize_text_field( $_POST['rp_login'] ) : '';
			$user = get_user_by( 'login', $rp_login );
			
			if( $user || ! is_wp_error( $user ) ){
				reset_password( $user, sanitize_text_field( $_POST['eael-pass1'] ) );
				$data['message'] = isset( $settings['success_resetpassword'] ) ? __( wp_strip_all_tags( $settings['success_resetpassword'] ), 'essential-addons-for-elementor-lite' ) : esc_html__( 'Your password has been reset.', 'essential-addons-for-elementor-lite' );

				$error_key = 'eael_resetpassword_error_' . esc_attr( $widget_id );
				delete_option( $error_key );				
				delete_option( 'eael_show_reset_password_on_form_submit_' . $widget_id );

				if($ajax){
					wp_send_json_success( $data );
				} else {
					update_option( 'eael_resetpassword_success_' . $widget_id, $data['message'], false );
				}
			} else {
				$data['message'] = isset( $settings['error_resetpassword'] ) ? __( wp_strip_all_tags( $settings['error_resetpassword'] ), 'essential-addons-for-elementor-lite' ) : esc_html__( 'Invalid user name found!', 'essential-addons-for-elementor-lite' );
				
				$success_key = 'eael_resetpassword_success_' . esc_attr( $widget_id );
				delete_option( $success_key );

				if($ajax){
					wp_send_json_error( $data );
				}else {
					update_option( 'eael_resetpassword_error_' . $widget_id, $data['message'], false );
				}
			}

			if (isset($_SERVER['HTTP_REFERER'])) {
				wp_safe_redirect( strtok( $_SERVER['HTTP_REFERER'], '?' ) );
				exit();
			}
		} else {
			// if any error found, abort
			if ( ! empty( $errors ) ) {
				if ( $ajax ) {
					$err_msg = '<ol>';
					foreach ( $errors as $error ) {
						$err_msg .= "<li>{$error}</li>";
					}
					$err_msg .= '</ol>';
					wp_send_json_error( $err_msg );
				}
				update_option( 'eael_resetpassword_error_' . $widget_id, maybe_serialize( $errors ), false );

				if (isset( $_SERVER['HTTP_REFERER'] )) {
					wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
					exit();
				}
			}
		}

	}

	public function eael_redirect_to_reset_password(){
		if( empty($_GET['eael-resetpassword']) ){
			return;
		}

		$this->page_id = isset( $_GET['page_id'] ) ? intval( $_GET['page_id'] ) : 0;
		$this->widget_id = isset( $_GET['widget_id'] ) ? sanitize_text_field( $_GET['widget_id'] ) : '';
		$rp_page_url = get_permalink( $this->page_id ); 
		
		list( $rp_path ) = explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$rp_cookie       = 'wp-resetpass-' . COOKIEHASH;

		if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			$value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

			wp_safe_redirect( remove_query_arg( array( 'key', 'login' ) ) );
			exit;
		}

		if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
			list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );

			$user = check_password_reset_key( $rp_key, $rp_login );

			if ( isset( $_POST['eael-pass1'] ) && isset( $_POST['rp_key'] ) && ! hash_equals( $rp_key, $_POST['rp_key'] ) ) {
				$user = false;
			}
		} else {
			$user = false;
		}

		if ( ! $user || is_wp_error( $user ) ) {
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

			$rp_err_msg = isset( $this->ds['err_reset_password_key_expired'] ) ? esc_html__( $this->ds['err_reset_password_key_expired'] ) : esc_html__( 'Your password reset link appears to be invalid. Please request a new link.', 'essential-addons-for-elementor-lite' );
			update_option( 'eael_lostpassword_error_' . esc_attr( $this->widget_id ) . '_show', 1, false );

			if ( $user && $user->get_error_code() === 'expired_key' ) {
				wp_redirect( $rp_page_url . '?eael-lostpassword=1&error=expiredkey' );
			} else {
				wp_redirect( $rp_page_url . '?eael-lostpassword=1&error=expiredkey' );
			}

			exit;
		}

		$rp_data = [
			'rp_key' => !empty( $rp_key ) ? $rp_key : '',
			'rp_login' => $rp_login,
			'rp_path' => $rp_path,
			'rp_cookie' => $rp_cookie,
			'user' => $user,
		];

		update_option( 'eael_resetpassword_rp_data_' . esc_attr( $this->widget_id ), maybe_serialize( $rp_data ), false );
		setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );

		wp_redirect( $rp_page_url . '?eael-resetpassword=1' );
		exit;
	}

	public function eael_retrieve_password_notification_email( $defaults, $key, $user_login, $user_data ){
		if ( ! self::$send_custom_email_lostpassword ) {
			return $defaults;
		}

		if ( ! empty( self::$email_options_lostpassword['subject'] ) ) {
			$defaults['subject'] = self::$email_options_lostpassword['subject'];
		}

		$page_id = self::$email_options_lostpassword['page_id'] ? self::$email_options_lostpassword['page_id'] : 0;
		$widget_id = self::$email_options_lostpassword['widget_id'] ? self::$email_options_lostpassword['widget_id'] : '';

		if ( ! empty( self::$email_options_lostpassword['message'] ) ) {
			if ( ! empty( $key ) ) {
				$locale = get_user_locale( $user_data );
				self::$email_options_lostpassword['password_reset_link'] = network_site_url( "wp-login.php?action=rp&eael-resetpassword=1&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '&page_id='. $page_id . '&widget_id='. $widget_id .'&wp_lang=' . $locale . "\r\n\r\n";
			}

			if( is_object($user_data) ) {
				$user_meta = get_user_meta( $user_data->ID );
				self::$email_options_lostpassword['username'] = $user_login;
				self::$email_options_lostpassword['firstname'] = !empty( $user_meta['first_name'][0] ) ? $user_meta['first_name'][0] : '';
				self::$email_options_lostpassword['lastname'] = !empty( $user_meta['last_name'][0] ) ? $user_meta['last_name'][0] : '';
				self::$email_options_lostpassword['email'] = $user_data->user_email;
				self::$email_options_lostpassword['website'] = $user_data->user_url;				
			}
			$defaults['message'] = $this->replace_placeholders_lostpassword( self::$email_options_lostpassword['message'] );
		}

		if ( ! empty( self::$email_options_lostpassword['headers'] ) ) {
			$defaults['headers'] = self::$email_options_lostpassword['headers'];
		}

		$defaults['message'] = wpautop( $defaults['message'] );
		
		return $defaults;
	}

	public function generate_username_from_email( $email, $suffix = '' ) {

		$username_parts = [];
		if ( empty( $username_parts ) ) {
			$email_parts    = explode( '@', $email );
			$email_username = $email_parts[0];

			// Exclude common prefixes.
			if ( in_array( $email_username, [
				'sales',
				'hello',
				'mail',
				'contact',
				'info',
			], true ) ) {
				// Get the domain part.
				$email_username = $email_parts[1];
			}

			$username_parts[] = sanitize_user( $email_username, true );
		}
		$username = strtolower( implode( '', $username_parts ) );

		if ( $suffix ) {
			$username .= $suffix;
		}

		$username = sanitize_user( $username, true );
		if ( username_exists( $username ) ) {
			// Generate something unique to append to the username in case of a conflict with another user.
			$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );

			return $this->generate_username_from_email( $email, $suffix );
		}

		return $username;
	}

	/**
	 * Get Widget data.
	 *
	 * @param array  $elements Element array.
	 * @param string $form_id  Element ID.
	 *
	 * @return bool|array
	 */
	public function find_element_recursive( $elements, $form_id ) {

		foreach ( $elements as $element ) {
			if ( $form_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $form_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}

	public function get_user_roles() {
		$user_roles[''] = __( 'Default', 'essential-addons-for-elementor-lite' );
		if ( function_exists( 'get_editable_roles' ) ) {
			$wp_roles = get_editable_roles();
			$roles    = $wp_roles ? $wp_roles : [];
			if ( ! empty( $roles ) && is_array( $roles ) ) {

				foreach ( $wp_roles as $role_key => $role ) {
					$user_roles[ $role_key ] = $role['name'];
				}
			}
		}
		return apply_filters( 'eael/login-register/new-user-roles', $user_roles );
	}

	/**
	 * It store data temporarily,5 minutes by default
	 *
	 * @param     $name
	 * @param     $data
	 * @param int $time time in seconds. Default is 300s = 5 minutes
	 *
	 * @return bool it returns true if the data saved, otherwise, false returned.
	 */
	public function set_transient( $name, $data, $time = 300 ) {
		$time = empty( $time ) ? (int) $time : ( 5 * MINUTE_IN_SECONDS );

		return set_transient( $name, $data, $time );
	}

	/**
	 * Filters the contents of the new user notification email sent to the new user.
	 *
	 * @param array    $email_data It contains, to, subject, message, headers etc.
	 * @param \WP_User $user       User object for new user.
	 * @param string   $blogname   The site title.
	 *
	 * @return array
	 * @since 4.9.0
	 */
	public function new_user_notification_email( $email_data, $user, $blogname ) {
		if ( ! self::$send_custom_email ) {
			return $email_data;
		}

		if ( ! empty( self::$email_options['subject'] ) ) {
			$email_data['subject'] = self::$email_options['subject'];
		}

		if ( ! empty( self::$email_options['message'] ) ) {
			if ( isset( self::$email_options['password_reset_link'] ) && self::$email_options['password_reset_link'] != '' ) {
				$_message = $email_data['message'];
				$start    = 'action=rp&key=';
				$end      = '&login=';
				$_message = substr( $_message, strpos( $_message, $start ) + strlen( $start ) );
				$key      = substr( $_message, 0, strpos( $_message, $end ) );
				if ( ! empty( $key ) ) {
					self::$email_options['password_reset_link'] = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n";
				}
			}
			$email_data['message'] = $this->replace_placeholders( self::$email_options['message'], 'user' );
		}

		if ( ! empty( self::$email_options['headers'] ) ) {
			$email_data['headers'] = self::$email_options['headers'];
		}

		$email_data['message'] = wpautop( $email_data['message'] );

		return apply_filters( 'eael/login-register/new-user-email-data', $email_data, $user, $blogname );

	}

	/**
	 * Filters the contents of the new user notification email sent to the site admin.
	 *
	 * @param array    $email_data It contains, to, subject, message, headers etc.
	 * @param \WP_User $user       User object for new user.
	 * @param string   $blogname   The site title.
	 *
	 * @return array
	 * @since 4.9.0
	 */
	public function new_user_notification_email_admin( $email_data, $user, $blogname ) {

		if ( ! self::$send_custom_email_admin ) {
			return $email_data;
		}

		if ( ! empty( self::$email_options['admin_subject'] ) ) {
			$email_data['subject'] = self::$email_options['admin_subject'];
		}

		if ( ! empty( self::$email_options['admin_message'] ) ) {
			$email_data['message'] = $this->replace_placeholders( self::$email_options['admin_message'], 'admin' );
		}

		if ( ! empty( self::$email_options['admin_headers'] ) ) {
			$email_data['headers'] = self::$email_options['admin_headers'];
		}

		$email_data['message'] = wpautop( $email_data['message'] );

		return apply_filters( 'eael/login-register/new-user-admin-email-data', $email_data, $user, $blogname );
	}

	/**
	 * It replaces placeholders with dynamic value and returns it.
	 *
	 * @param        $message
	 * @param string $receiver
	 *
	 * @return null|string|string[]
	 */
	public function replace_placeholders( $message, $receiver = 'user' ) {
		$placeholders = [
			'/\[password\]/',
			'/\[password_reset_link\]/',
			'/\[username\]/',
			'/\[email\]/',
			'/\[firstname\]/',
			'/\[lastname\]/',
			'/\[website\]/',
			'/\[loginurl\]/',
			'/\[sitetitle\]/',
		];
		$replacement  = [
			self::$email_options['password'],
			self::$email_options['password_reset_link'],
			self::$email_options['username'],
			self::$email_options['email'],
			self::$email_options['firstname'],
			self::$email_options['lastname'],
			self::$email_options['website'],
			wp_login_url(),
			get_option( 'blogname' ),
		];

		if ( 'user' !== $receiver ) {
			// remove password from admin mail, because admin should not see user's plain password
			unset( $placeholders[0] );
			unset( $placeholders[1] );
			unset( $replacement[0] );
			unset( $replacement[1] );
		}

		return preg_replace( $placeholders, $replacement, $message );
	}

	/**
	 * It replaces placeholders with dynamic value and returns it.
	 *
	 * @param        $message
	 * @param string $receiver
	 *
	 * @return null|string|string[]
	 */
	public function replace_placeholders_lostpassword( $message ) {
		$password_reset_link = !empty( self::$email_options_lostpassword['password_reset_link'] ) ? '<a href="'.esc_url_raw( self::$email_options_lostpassword['password_reset_link'] ).'">' . esc_url_raw( self::$email_options_lostpassword['password_reset_link'] ) . '</a>' : '';
		$username 		   = !empty( self::$email_options_lostpassword['username'] ) ? self::$email_options_lostpassword['username'] : '';
		$email 			   = !empty( self::$email_options_lostpassword['email'] ) ? self::$email_options_lostpassword['email'] : '';
		$firstname 		   = !empty( self::$email_options_lostpassword['firstname'] ) ? self::$email_options_lostpassword['firstname'] : '';
		$lastname 		   = !empty( self::$email_options_lostpassword['lastname'] ) ? self::$email_options_lostpassword['lastname'] : '';
		$website 		   = !empty( self::$email_options_lostpassword['website'] ) ? self::$email_options_lostpassword['website'] : '';
		
		$placeholders = [
			'/\[password_reset_link\]/',
			'/\[username\]/',
			'/\[email\]/',
			'/\[firstname\]/',
			'/\[lastname\]/',
			'/\[website\]/',
			'/\[loginurl\]/',
			'/\[sitetitle\]/',
		];
		$replacement  = [
			$password_reset_link,
			$username,
			$email,
			$firstname,
			$lastname,
			$website,
			wp_login_url(),
			get_option( 'blogname' ),
		];

		return preg_replace( $placeholders, $replacement, $message );
	}

	public function lr_validate_recaptcha() {
		if ( ! isset( $_REQUEST['g-recaptcha-response'] ) ) {
			return false;
		}
		$endpoint = 'https://www.google.com/recaptcha/api/siteverify';
		$data     = [
			'secret'   => get_option( 'eael_recaptcha_secret' ),
			'response' => sanitize_text_field( $_REQUEST['g-recaptcha-response'] ),
			'ip'       => $_SERVER['REMOTE_ADDR'],
		];

		$res = json_decode( wp_remote_retrieve_body( wp_remote_post( $endpoint, [ 'body' => $data ] ) ), 1 );
		if ( isset( $res['success'] ) ) {
			return $res['success'];
		}

		return false;
	}

	public function lr_get_widget_settings( $page_id, $widget_id ) {
		$document = Plugin::$instance->documents->get( $page_id );
		$settings = [];
		if ( $document ) {
			$elements    = Plugin::instance()->documents->get( $page_id )->get_elements_data();
			$widget_data = $this->find_element_recursive( $elements, $widget_id );

			if(!empty($widget_data)) {
                $widget      = Plugin::instance()->elements_manager->create_element_instance( $widget_data );
                if ( $widget ) {
                    $settings    = $widget->get_settings_for_display();
                }
            }

		}
		return $settings;
	}

    public function delete_registration_options($widget_id)
    {
        delete_option('eael_register_success_' . $widget_id);
        delete_option('eael_register_errors_' . $widget_id);
	}

}
