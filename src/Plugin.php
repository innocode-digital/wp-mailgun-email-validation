<?php

namespace Innocode\Mailgun\EmailValidation;

/**
 * Class Plugin
 * @package Innocode\Mailgun\EmailValidation
 */
final class Plugin
{
    /**
     * Mailgun API Key
     *
     * @var string
     */
    private $_key;
    /**
     * API Client
     *
     * @var Client
     */
    private $_client;

    /**
     * Plugin constructor.
     */
    public function __construct()
    {
        if ( defined( 'MAILGUN_API_KEY' ) ) {
            $this->_key = MAILGUN_API_KEY;
        } elseif ( defined('MAILGUN_APIKEY') ) {
            // Try to use constant from Mailgun for WordPress plugin https://github.com/mailgun/wordpress-plugin
            $this->_key = MAILGUN_APIKEY;
        } else {
            $this->_key = '';
        }

        $this->_client = new Client(
            defined( 'MAILGUN_API_URL' )
                ? MAILGUN_API_URL
                : 'https://api.mailgun.net',
            $this->get_key()
        );
    }

    /**
     * Returns Mailgun API Key
     *
     * @return string
     */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * Returns API Client
     *
     * @return Client
     */
    public function get_client()
    {
        return $this->_client;
    }

    /**
     * Adds hooks
     */
    public function run()
    {
        add_filter( 'wp_mail', [ $this, 'validate_wp_mail_attrs' ] );
    }

    /**
     * Validates wp_mail() $to parameter
     *
     * @see wp_mail()
     * @param array $attrs
     * @return array
     */
    public function validate_wp_mail_attrs( array $attrs ) : array
    {
        if ( ! isset( $attrs['to'] ) ) {
            return $attrs;
        }

        $to = $attrs['to'];

        if ( ! is_array( $to ) ) {
            $to = explode( ',', $to );
        }

        $attrs['to'] = array_filter( $to, [ $this, 'validate' ] );

        return $attrs;
    }

    /**
     * Validates email address with Mailgun Email Validation
     *
     * @param string $email
     * @return bool
     */
    public function validate( $email )
    {
        if ( ! is_email( $email ) ) {
            return false;
        }

        // Trusts that admin email is valid by default
        if (
            apply_filters( 'innocode_mailgun_email_validation_skip_admin_email', true, $email ) &&
            $email == get_option( 'admin_email' )
        ) {
            return true;
        }

        // Trusts that users email is valid by default
        if (
            apply_filters( 'innocode_mailgun_email_validation_skip_user_email', true, $email ) &&
            email_exists( $email )
        ) {
            return true;
        }

        $cache_key = sprintf( 'innocode_mailgun_email_validation%s', md5( $email ) );
        $cache_value = wp_cache_get( $cache_key );

        if ( false !== $cache_value ) {
            return $this->validated( $cache_value );
        }

        $email = $this->get_client()->validate( $email );

        // Probably something is wrong with key or Mailgun, so just ignores validation
        if ( is_wp_error( $email ) || ! isset( $email['result'], $email['risk'] ) ) {
            return true;
        }

        wp_cache_set( $cache_key, $email, '', ( 24 * HOUR_IN_SECONDS + wp_rand( 0, ( 12 * HOUR_IN_SECONDS ) ) ) );

        return $this->validated( $email );
    }

    /**
     * Determines if email address should be skipped according to response from Mailgun Email Validation
     *
     * @param array $email
     * @return bool
     */
    public function validated( array $email )
    {
        // Uses non-strict validation by default
        return apply_filters( 'innocode_mailgun_email_validation_validated', in_array( $email['result'], [
            'deliverable',
            'unknown',
        ] ) && $email['risk'] != 'high', $email );
    }
}
