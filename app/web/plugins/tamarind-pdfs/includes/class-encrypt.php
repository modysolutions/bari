<?php
/**
 * Class Encrypt functions for Tamarind PDFs.
 *
 * @package Tamarind_Pdfs
 */

namespace tamarind_pdfs;

defined( 'ABSPATH' ) || exit;

/**
 * Class Encrypt functions for Tamarind PDFs.
 */
class Tamarind_Encoding {

	/**
	 * Ciphering method.
	 *
	 * @var string
	 */
	public $ciphering      = 'AES-128-CTR';
	/**
	 * Encryption IV.
	 *
	 * @var string
	 */
	public $encryption_iv  = '6661236661236661';

	/**
	 * Encryption Key.
	 *
	 * @var string
	 */
	public $encryption_key = 'CbdIntel2022R';

	/**
	 * Encrypt a string.
	 *
	 * @param string $string String to encrypt.
	 * @return string
	 */
	public function encrypt( $string ) {
		$iv_length = openssl_cipher_iv_length( $this->ciphering );
		$options = 0;
		$encryption = openssl_encrypt( $string, $this->ciphering, $this->encryption_key, $options, $this->encryption_iv );
		return $encryption;
	}

	/**
	 * Decrypt a string.
	 *
	 * @param string $string String to decrypt.
	 * @return string
	 */
	public function decrypt( $string ) {
		$iv_length = openssl_cipher_iv_length( $this->ciphering );
		$options = 0;
		$decryption = openssl_decrypt( $string, $this->ciphering, $this->encryption_key, $options, $this->encryption_iv );
		return $decryption;
	}

}
