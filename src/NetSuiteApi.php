<?php

namespace Anibalealvarezs\NetSuiteApi;

use Anibalealvarezs\ApiSkeleton\Clients\OAuthV1Client;
use Anibalealvarezs\OAuthV1\Enums\SignatureMethod;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;

class NetSuiteApi extends OAuthV1Client
{
    /**
     * @param string $consumerId
     * @param string $consumerSecret
     * @param string $token
     * @param string $tokenSecret
     * @param string $accountId
     * @throws GuzzleException
     */
    public function __construct(
        string $consumerId,
        string $consumerSecret,
        string $token,
        string $tokenSecret,
        string $accountId,
    ) {
        return parent::__construct(
            baseUrl: 'https://'.$accountId.'.suitetalk.api.netsuite.com',
            consumerId: $consumerId,
            consumerSecret: $consumerSecret,
            token: $token,
            tokenSecret: $tokenSecret,
            realm: $accountId,
            signatureMethod: SignatureMethod::HMAC_SHA256,
        );
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $query
     * @param string|array $body
     * @param array $form_params
     * @param string $baseUrl
     * @param array $headers
     * @param array $additionalHeaders
     * @param ?CookieJar $cookies
     * @param bool $verify
     * @param bool $allowNewToken
     * @param string $pathToSave
     * @param bool|null $stream
     * @param array|null $errorMessageNesting
     * @param int $sleep
     * @param array $customErrors
     * @param bool $ignoreAuth
     * @return Response
     * @throws GuzzleException
     */
    public function performRequest(
        string $method,
        string $endpoint,
        array $query = [],
        string|array $body = "",
        array $form_params = [],
        string $baseUrl = "",
        array $headers = [],
        array $additionalHeaders = [], // Ex: ["Amazon-Advertising-API-Scope" => 'profileId'];
        ?CookieJar $cookies = null,
        bool $verify = false,
        bool $allowNewToken = true,
        string $pathToSave = "",
        bool $stream = null,
        ?array $errorMessageNesting = null, // Ex: ['error' => ['message']]
        int $sleep = 0,
        array $customErrors = [], // Ex: ['403' => 'body'] or ['500' => 'code'] or ['404' => 'message']
        bool $ignoreAuth = false,
    ): Response {

        if (!$errorMessageNesting) {
            $errorMessageNesting = ['o:errorDetails' => [['detail']]];
        }

        return parent::performRequest(
            method: $method,
            endpoint: $endpoint,
            query: $query,
            body: $body,
            form_params: $form_params,
            baseUrl: $baseUrl,
            headers: $headers,
            additionalHeaders: $additionalHeaders,
            cookies: $cookies,
            verify: $verify,
            allowNewToken: $allowNewToken,
            pathToSave: $pathToSave,
            stream: $stream,
            errorMessageNesting: $errorMessageNesting,
            sleep: $sleep,
            customErrors: $customErrors,
            ignoreAuth: $ignoreAuth,
        );
    }

    /**
     * @return bool
     * @throws GuzzleException
     */
    public function test(): bool
    {
        // Request the spreadsheet data
        $this->performRequest(
            method: "OPTIONS",
            endpoint: "/services/rest/*",
        );
        // Return response
        return true;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param bool $expandSubResources
     * @return array
     * @throws GuzzleException
     */
    public function getSalesOrders(
        int $offset = 0,
        int $limit = 1000,
        bool $expandSubResources = true,
    ): array
    {
        // Request the spreadsheet data
        $response = $this->performRequest(
            method: "GET",
            endpoint: "/services/rest/record/v1/salesOrder",
            query: [
                "limit" => $limit,
                "offset" => $offset,
                "expandSubResources" => $expandSubResources,
            ],
        );
        // Return response
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $id
     * @param bool $expandSubResources
     * @return array
     * @throws GuzzleException
     */
    public function getSalesOrder(
        int $id,
        bool $expandSubResources = true,
    ): array
    {
        // Request the spreadsheet data
        $response = $this->performRequest(
            method: "GET",
            endpoint: "/services/rest/record/v1/salesOrder/" . $id,
            query: [
                "expandSubResources" => $expandSubResources,
            ],
        );
        // Return response
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $query
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws GuzzleException
     */
    public function getSuiteQLQuery(
        string $query,
        int $offset = 0,
        int $limit = 1000,
    ): array
    {
        // Request the spreadsheet data
        $response = $this->performRequest(
            method: "POST",
            endpoint: "/services/rest/query/v1/suiteql",
            query: [
                "limit" => $limit,
                "offset" => $offset,
            ],
            body: json_encode([
                "q" => preg_replace("/\s+/", " ", preg_replace( "/\r|\n/", "", $query )),
            ]),
            headers: [
                "Prefer" => "transient",
            ],
        );
        // Return response
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array
     * @throws GuzzleException
     */
    public function getSuiteQLQueryAll(
        string $query,
        int $limit = 1000,
    ): array
    {
        $offset = 0;
        $results = [
            'items' => [],
        ];
        do {
            $response = $this->getSuiteQLQuery($query, $offset, $limit);
            $results['items'] = array_merge($results['items'], $response["items"]);
            $offset += $limit;
        } while ($response['hasMore']);
        $results['count'] = count($results['items']);
        return $results;
    }

    /**
     * @param string $store
     * @param array $productsIds
     * @return array
     * @throws GuzzleException
     */
    public function getImagesForProducts(
        string $store,
        array $productsIds,
    ): array
    {
        $imagesQuery = "SELECT * FROM itemimage WHERE sitelist = '".$store."' AND item IN (".implode(',', $productsIds).")";
        return $this->getSuiteQLQueryAll(
            query: $imagesQuery,
        );
    }
}
