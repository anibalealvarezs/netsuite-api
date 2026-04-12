<?php

namespace Tests;

use Anibalealvarezs\NetSuiteApi\NetSuiteApi;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Anibalealvarezs\ApiSkeleton\Classes\Exceptions\ApiRequestException;

class NetSuiteApiTest extends TestCase
{
    private NetSuiteApi $netSuiteApi;
    private Generator $faker;

    /**
     * @param MockHandler $mock
     * @return GuzzleClient
     */
    protected function createMockedGuzzleClient(MockHandler $mock): GuzzleClient
    {
        $handlerStack = HandlerStack::create($mock);
        return new GuzzleClient(['handler' => $handlerStack]);
    }

    /**
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        $configFile = __DIR__ . "/../config/config.yaml";
        if (file_exists($configFile)) {
            $config = Yaml::parseFile($configFile);
        } else {
            $config = [
                'netsuite_consumer_id' => 'id',
                'netsuite_consumer_secret' => 'secret',
                'netsuite_token_id' => 'token',
                'netsuite_token_secret' => 'secret',
                'netsuite_account_id' => 'account'
            ];
        }
        $this->netSuiteApi = new NetSuiteApi(
            consumerId: $config['netsuite_consumer_id'],
            consumerSecret: $config['netsuite_consumer_secret'],
            token: $config['netsuite_token_id'],
            tokenSecret: $config['netsuite_token_secret'],
            accountId: $config['netsuite_account_id'],
        );
        $this->faker = Factory::create();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(NetSuiteApi::class, $this->netSuiteApi);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetSalesOrders(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['links' => [], 'count' => 0, 'hasMore' => false, 'items' => []])),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);
        $client = new NetSuiteApi(
            consumerId: 'id',
            consumerSecret: 'secret',
            token: 'token',
            tokenSecret: 'secret',
            accountId: 'account',
            guzzleClient: $guzzle
        );

        $salesOrders = $client->getSalesOrders(
            limit: $this->faker->numberBetween(1, 1000)
        );

        $this->assertIsArray($salesOrders);
        $this->assertArrayHasKey('items', $salesOrders);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllSalesOrdersAndProcess(): void
    {
        $response1 = [
            'items' => [['id' => 'o1']],
            'hasMore' => true
        ];
        $response2 = [
            'items' => [['id' => 'o2']],
            'hasMore' => false
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($response1)),
            new Response(200, [], json_encode($response2)),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);

        $client = new NetSuiteApi(
            consumerId: 'id',
            consumerSecret: 'secret',
            token: 'token',
            tokenSecret: 'secret',
            accountId: 'account',
            guzzleClient: $guzzle
        );

        $processedCount = 0;
        $client->getAllSalesOrdersAndProcess(function ($data) use (&$processedCount) {
            $processedCount += count($data);
        });

        $this->assertEquals(2, $processedCount);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllSalesOrdersEmpty(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['items' => [], 'hasMore' => false])),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);

        $client = new NetSuiteApi(
            consumerId: 'id',
            consumerSecret: 'secret',
            token: 'token',
            tokenSecret: 'secret',
            accountId: 'account',
            guzzleClient: $guzzle
        );

        $result = $client->getAllSalesOrders();
        
        $this->assertCount(0, $result['items']);
    }

    /**
     * @throws GuzzleException
     */
    public function testGetAllSalesOrdersErrorMidLoop(): void
    {
        $response1 = [
            'items' => [['id' => 'o1']],
            'hasMore' => true
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($response1)),
            new Response(500, [], 'Internal Server Error'),
        ]);
        $guzzle = $this->createMockedGuzzleClient($mock);

        $client = new NetSuiteApi(
            consumerId: 'id',
            consumerSecret: 'secret',
            token: 'token',
            tokenSecret: 'secret',
            accountId: 'account',
            guzzleClient: $guzzle
        );

        $this->expectException(ApiRequestException::class);

        $client->getAllSalesOrdersAndProcess(function ($data) {});
    }
}
