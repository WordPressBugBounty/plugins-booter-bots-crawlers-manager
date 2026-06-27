<?php
namespace Upress\Booter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use LimitIterator;
use SplFileObject;

class Logger {
	static $settings;

	/**
	 * Get the log file path
	 * @return string
	 */
	public static function get_log_path() {
        return wp_normalize_path( trailingslashit( WP_CONTENT_DIR ) . 'booter-logs/booter.log' );
	}

    protected static function ensure_log_dir( $log_dir ) {
        if ( ! is_dir( $log_dir ) ) {
            wp_mkdir_p( $log_dir );
        }

        if ( ! is_dir( $log_dir ) ) {
            return;
        }

        $index_file = trailingslashit( $log_dir ) . 'index.html';
        if ( ! file_exists( $index_file ) ) {
            file_put_contents( $index_file, '', LOCK_EX );
        }

        $htaccess_file = trailingslashit( $log_dir ) . '.htaccess';
        if ( ! file_exists( $htaccess_file ) ) {
            file_put_contents( $htaccess_file, "Deny from all\n", LOCK_EX );
        }
    }

	/**
	 * Write a message to log
	 * @param string $message
	 */
    public static function write( $message ) {
        if ( ! static::$settings ) {
            static::$settings = get_option( 'booter_settings' );
        }

        if ( ! isset( static::$settings['debug'] ) || ! Utilities::bool_value( static::$settings['debug'] ) ) {
            return;
        }

        $logfile = static::get_log_path();
        static::ensure_log_dir( dirname( $logfile ) );

        $datetime = gmdate( 'r' );
        $ip       = implode( ',', Utilities::get_client_ip() );
        $message  = str_replace( [ "\r", "\n" ], ' ', sanitize_text_field( (string) $message ) );

        $log_line = sprintf(
            '%s [%s] "%s", request: "%s %s", referer: "%s"' . PHP_EOL,
            $ip,
            $datetime,
            $message,
            isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '',
            isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
            ( empty( $_SERVER['HTTP_REFERER'] ) ? '-' : sanitize_url( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) )
        );

        file_put_contents( $logfile, $log_line, FILE_APPEND | LOCK_EX );
    }

	/**
	 * Clear the log file
	 */
    public static function clear_log() {
        $logfile = static::get_log_path();
        static::ensure_log_dir( dirname( $logfile ) );

        if ( ! file_exists( $logfile ) ) {
            return;
        }

        file_put_contents( $logfile, '', LOCK_EX );
    }

	/**
	 * Get the latest log entries
	 * @param int $lines Number of lines to return from the end of the file, 0 for all
	 * @return string
	 */
	public static function get_latest_logs( $lines=200 ) {
		$logfile = static::get_log_path();

		// file does not exists so the contents is blank
		if ( ! file_exists( $logfile ) ) {
			return '';
		}

		// the user wants the whole file (this can be HUGE!)
		if ( 0 === $lines ) {
			return file_get_contents( $logfile );
		}

		// get the last $lines lines from the file
		$file = new SplFileObject( $logfile, 'r' );
		$file->seek( PHP_INT_MAX );
		$last_line = $file->key();

		if ( $last_line <= 0 ) {
			return '';
		}

		// $lines can be larget than $last_line, in that case start at the start of the file
		$offset = max( 0, ( $last_line - $lines ) );
		$iterator = new LimitIterator( $file, $offset, $last_line );
		$lines = iterator_to_array( $iterator );
		$file = null; // make sure the handle to the file is closed

		return implode( '', $lines );
	}
}
