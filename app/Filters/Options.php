<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Options implements FilterInterface
{
    /**
     * Apply the filter before the request is processed.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param array|null $arguments
     * @return \CodeIgniter\HTTP\ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        
        // Set CORS headers
        $response->setHeader('Access-Control-Allow-Origin', '*'); // Or use a specific origin
        $response->setHeader('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        
        // Allow credentials if needed
        // $response->setHeader('Access-Control-Allow-Credentials', 'true');
        
        // Handle OPTIONS preflight request
        if ($request->getMethod() == 'OPTIONS') {
          $response->setStatusCode(204); // No content response

          // Allow specific headers
          $response->setHeader('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization');
  
          // Allow specific methods
          $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
  
          // Max age for caching preflight request
          $response->setHeader('Access-Control-Max-Age', '3600');
  
          return $response;  // Return r
        }
    }

    /**
     * Apply the filter after the request is processed.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param \CodeIgniter\HTTP\ResponseInterface $response
     * @param array|null $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Optional: Do something after the response is sent
    }
}
