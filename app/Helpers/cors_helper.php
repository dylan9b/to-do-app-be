<?php
if (!defined('APP_NAMESPACE')) {
  exit('No direct script access allowed');
}
/**
 * CORS Helper for enabling Cross-Origin Resource Sharing in CodeIgniter.
 */
function allow_cors()
{
  header('Access-Control-Allow-Origin: http://localhost:4200');
  header('Access-Control-Allow-Headers: Authorization, Content-Type');
  header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

    header('Access-Control-Max-Age: 3600');  // Cache preflight response for 1 hour

    exit(0); // End the script for OPTIONS request
  }

}
