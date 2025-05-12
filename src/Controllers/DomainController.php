<?php
/**
 * Domain Controller
 * Created: 2024-12-17
 * Author: Laurensius Jeffrey
 * License: MIT
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Database;
use GuzzleHttp\Client;

class DomainController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function handleRedirect(Request $request, Response $response): Response
    {
        try {
            $hostname = $request->getUri()->getHost();
            $path = $request->getUri()->getPath();        // Get the path
            $query = $request->getUri()->getQuery();      // Get query parameters if any

            // Fetch data from new API
            $apiUrl = 'https://checkipos.com/api/sempoa4d';
            $httpClient = new Client([
                'verify' => false  // Disable SSL verification if needed
            ]);
            $apiResponse = $httpClient->get($apiUrl);
            $apiData = json_decode($apiResponse->getBody(), true);

            if (empty($apiData) || !isset($apiData['domain'])) {
                throw new \Exception("No valid data retrieved from API");
            }

            // The API returns an array of domains directly
            $domains = $apiData['domain'];

            if (empty($domains)) {
                throw new \Exception("No active target domains configured");
            }

            // Select a random domain from the array
            $randomDomain = $domains[array_rand($domains)];

            // Build the redirect URL with path and query parameters
            $redirectUrl = "https://{$randomDomain}{$path}";
            if (!empty($query)) {
                $redirectUrl .= "?{$query}";
            }

            $stmt = $this->db->prepare(
                'INSERT INTO redirects (source_domain, target_url, created_at) VALUES (?, ?, NOW())'
            );
            $stmt->execute([$hostname, $redirectUrl]);

            return $response
                ->withHeader('Location', $redirectUrl)
                ->withStatus(302);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to return JSON response
     *
     * @param Response $response
     * @param mixed $data
     * @param int $status
     * @return Response
     */
    private function jsonResponse(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}