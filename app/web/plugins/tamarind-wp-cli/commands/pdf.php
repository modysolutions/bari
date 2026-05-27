<?php

namespace tamarind_wp_cli;

use WP_CLI;

if ( defined( 'WP_CLI' ) && \WP_CLI ) {
    WP_CLI::add_command( 'tamarind pdf', __NAMESPACE__ . '\Pdf_Handler' );
}

class Pdf_Handler {
    public function set() : void {
        if ( function_exists( '\tamarind_pdfs\set_all_pdfs' ) ) {
            \tamarind_pdfs\set_all_pdfs();
        }
    }

    function reset () : void {
        if ( function_exists( '\tamarind_pdfs\reset_pdfs' ) ) {
            \tamarind_pdfs\reset_pdfs();
        }
    }

    function generate() : void {
        if ( function_exists( '\tamarind_pdfs\generate_post_pdfs' ) ) {
            \tamarind_pdfs\generate_post_pdfs();
        }
    }
}