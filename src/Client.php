<?php

namespace Innocode\Mailgun\EmailValidation;

use WP_Error;

/**
 * Class Client
 * @package Innocode\Mailgun\EmailValidation
 */
class Client
{
    /**
     * API URL
     *
     * @var string
     */
    private $url;
    /**
     * API basic auth header
     *
     * @var string
     */
    private $auth;

    /**
     * Client constructor.
     *
     * @param string $url
     * @param string $private_key
     */
    public function __construct( $url, $private_key )
    {
        $this->url = $url;
        $this->auth = base64_encode( "api:$private_key" );
    }

    /**
     * Returns full URL to Mailgun endpoint
     *
     * @param string $endpoint
     * @return string
     */
    public function get_url( $endpoint = '' )
    {
        return trailingslashit( $this->url ) . ltrim( $endpoint, '/' );
    }

    /**
     * Returns basic auth header
     *
     * @return string
     */
    public function get_auth()
    {
        return $this->auth;
    }

    /**
     * Validates email address
     *
     * @param string $email
     * @return array|WP_Error
     */
    public function validate( $email )
    {
        return $this->request( '/v4/address/validate', [
            'address' => $email,
        ] );
    }

    /**
     * Performs HTTP request to Mailgun and returns its response.
     *
     * @param string     $endpoint
     * @param array|null $body
     * @param string     $method
     * @return array|WP_Error
     */
    protected function request( $endpoint, array $body = null, $method = 'GET' )
    {
        $response = wp_remote_request( $this->get_url( $endpoint ), [
            'method'  => $method,
            'headers' => [
                'Authorization' => "Basic {$this->get_auth()}",
            ],
            'body'    => $body,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status >= 400 ) {
            return new WP_Error(
                'bad_request',
                isset( $body['message'] ) ? $body['message'] : '',
                $body
            );
        }

        return $body;
    }
}
