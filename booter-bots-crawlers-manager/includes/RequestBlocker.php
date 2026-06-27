<?php
namespace Upress\Booter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
class RequestBlocker {

	private static $instance;

	/**
	 * @return RequestBlocker
	 */
	public static function initialize() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'muplugins_loaded', [ $this, 'maybe_block_request' ] );
	}

	/**
	 * Do the blocking
	 */
	public function maybe_block_request() {
		$settings = get_option( 'booter_settings' );

		if ( ! $settings ) {
			return;
		}

		$ip = implode( ',', Utilities::get_client_ip());

		// do not block cli
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        $qs = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';

		// block bad robots
        if ( Utilities::bool_value( $settings['block']['block_bad_robots'] ) ) {
            $bad_bots = is_array( $settings['block']['badrobots'] ) ? $settings['block']['badrobots'] : json_decode( $settings['block']['badrobots'], true );
            $bad_bots = is_array( $bad_bots ) ? $bad_bots : [];
            foreach ( $bad_bots as $robot ) {
				if ( false !== strpos( sanitize_text_field( $ua ), sanitize_text_field( $robot ) ) ) {
					Logger::write( "{$ip}, '{$ua}' blocked due to bad bot block of '{$robot}'" );

					header( 'HTTP/1.0 403 Forbidden' );
					die( '<div style="text-align: center;"><h1 style="margin: 40px 0;">403 Forbidden</h1><hr><small>Booter - Bots & Crawlers Manager</small></div>' );
				}
			}
		}

		if ( ! Utilities::bool_value( $settings['block']['enabled'] ) ) {
			return;
		}

		if ( isset( $settings['block']['block_empty_useragents'] ) && Utilities::bool_value( $settings['block']['block_empty_useragents'] ) && empty( $ua ) ) {
			Logger::write( "{$ip}, blocked for empty user agent" );

			header( 'HTTP/1.0 403 Forbidden' );
			die( '<div style="text-align: center;"><h1 style="margin: 40px 0;">403 Forbidden</h1><hr><small>Booter - Bots & Crawlers Manager</small></div>' );
		}

		// allow users to decide not to rate limit some useragents
		$fingerprint = Utilities::generate_user_fingerprint_string();
		if ( false === apply_filters( 'booter_should_block_useragent', $fingerprint ) ) {
			return;
		}

		if ( isset( $settings['block']['block_useragents'] ) && 'bots' == $settings['block']['block_useragents'] ) {
			if ( empty( $ua ) || Utilities::is_user_logged_in() ) {
				return;
			}

			$useragents = array_map( function ( $item ) {
				return preg_quote( trim( $item ), '/' );
			}, Utilities::get_known_bots() );

			if ( ! preg_match( '/' . implode( '|', $useragents ) . '/i', $ua ) ) {
				return;
			}
		}

		$block   = false;

		$strings = is_array( $settings['block']['strings'] ) ? $settings['block']['strings'] : json_decode( $settings['block']['strings'] );
        $strings = is_array( $strings ) ? $strings : [];

		$strings = array_map( function ( $item ) {
			return preg_quote( trim( $item ), '/' );
		}, $strings );

		// woocommerce specific
        $enabled_woocommerce = isset( $settings['block']['enabled_woocommerce'] ) ? Utilities::bool_value( $settings['block']['enabled_woocommerce'] ) : true;
        if ( $enabled_woocommerce && ! empty( $_GET ) && preg_match( '/filtering=|add-to-cart=|filter|orderby=|(filter_.+?=)/i', $qs ) ) {
			$block = true;
			Logger::write( "{$ip}, '{$ua}' blocked for WooCommerce blocks" );
		}

        $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// string filtering
		if ( ! $block && count( $strings ) ) {
			$strings = implode( '|', $strings );
			if ( preg_match( '/' . $strings . '/i', $uri ) ) {
				$block = true;
				Logger::write( "{$ip}, '{$ua}' blocked for rejected strings" );
			}
		}

		// regex filtering
        if ( ! $block && Utilities::bool_value( $settings['block']['regex_enabled'] ) ) {
            $regex = is_array( $settings['block']['regex'] ) ? $settings['block']['regex'] : json_decode( $settings['block']['regex'], true );
            $regex = is_array( $regex ) ? $regex : [];

            if ( count( $regex ) > 0 ) {
                foreach ( $regex as $r ) {
                    if ( empty( $r ) ) {
                        continue;
                    }

                    $pattern = '#' . str_replace( '#', '\#', trim( $r ) ) . '#';

                    $match_result = @preg_match( $pattern, $uri );

                    if ( 1 === $match_result ) {
                        $block = true;
                        Logger::write( "{$ip}, '{$ua}' blocked for regex block" );
                        break;
                    }
                }
            }
        }

        if ( $block ) {
            $response = $settings['block']['http_response'];
            switch( $response ) {
                case '401':
                    $response = '401 Unauthorized';
                    break;
                case '403':
                    $response = '403 Forbidden';
                    break;
                case '404':
                    $response = '404 Page Not Found';
                    break;
                default:
                case '410':
                    $response = '410 Gone';
                    break;
            }
            header( 'HTTP/1.0 ' . $response );
            die( '<div style="text-align: center;"><h1 style="margin: 40px 0;">' . esc_html( $response ) . '</h1><hr><small>Booter - Bots & Crawlers Manager</small></div>' );
        }
	}
}
