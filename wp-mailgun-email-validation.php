<?php
/**
 * Plugin Name: Mailgun Email Validation
 * Description: Validates email address through Mailgun.
 * Version: 1.1.0
 * Author: Innocode
 * Author URI: https://innocode.com
 * Tested up to: 5.3.2
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Innocode\Mailgun\EmailValidation;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! function_exists( 'innocode_mailgun_email_validation_init' ) ) {
    function innocode_mailgun_email_validation_init() {
        /**
         * @var EmailValidation\Plugin $innocode_mailgun_email_validation
         */
        global $innocode_mailgun_email_validation;

        $innocode_mailgun_email_validation->run();
    }
}

if ( defined( 'MAILGUN_API_KEY' ) || defined( 'MAILGUN_APIKEY' ) ) {
    $GLOBALS['innocode_mailgun_email_validation'] = new EmailValidation\Plugin();

    add_action( 'init', 'innocode_mailgun_email_validation_init' );
}

if ( ! function_exists( 'innocode_mailgun_email_validation' ) ) {
    function innocode_mailgun_email_validation() {
        /**
         * @var EmailValidation\Plugin $innocode_mailgun_email_validation
         */
        global $innocode_mailgun_email_validation;

        if ( is_null( $innocode_mailgun_email_validation ) ) {
            trigger_error(
                'Missing required constants MAILGUN_API_KEY or MAILGUN_APIKEY.',
                E_USER_ERROR
            );
        }

        return $innocode_mailgun_email_validation;
    }
}
