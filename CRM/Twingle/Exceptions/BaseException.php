<?php

use CRM_Twingle_ExtensionUtil as E;

/**
 * A simple custom exception class that indicates a problem within a class
 * of the Twingle API extension.
 */
class CRM_Twingle_Exceptions_BaseException extends Exception {

  private string $error_code;
  private string $log_message;

  /**
   * BaseException Constructor
   * @param string $message
   *  Error message
   * @param string $error_code
   *  A meaningful error code
   */
  public function __construct(string $message = '', string $error_code = '') {
    parent::__construct($message, 1);
    $this->log_message = !empty($message) ? E::LONG_NAME . ': ' . $message : '';
    $this->error_code = $error_code;
  }

  /**
   * Returns the error message, but with the extension name prefixed.
   * @return string
   */
  public function getLogMessage() {
    return $this->log_message;
  }

  /**
   * Returns the error code.
   * @return string
   */
  public function getErrorCode() {
    return $this->error_code;
  }

}