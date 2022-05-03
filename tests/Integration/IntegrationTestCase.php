<?php

namespace Makaira\OxidConnectEssential\Test\Integration;

use Makaira\HttpClient;
use Makaira\HttpClient\Curl;
use Makaira\HttpClient\Signing;
use Makaira\OxidConnectEssential\Service\UserService;
use Makaira\OxidConnectEssential\Test\ConnectClient;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;

use function ltrim;
use function rtrim;
use function strpos;

abstract class IntegrationTestCase extends UnitTestCase
{
    protected const SECRET = 'ChangeThis';

    protected function getHttpClient()
    {
        $baseUrl       = $this->getTestConfig()->getShopUrl();

        return new class (new Curl(10, 1), $baseUrl) extends HttpClient {
            private HttpClient $aggregate;

            private string $baseUrl;

            public function __construct(HttpClient $aggregate, string $baseUrl)
            {
                $this->aggregate = $aggregate;
                $this->baseUrl   = rtrim(rtrim($baseUrl, '/'));
            }

            public function request($method, $url, $body = null, array $headers = [])
            {
                if (false === strpos($url, 'http:') && false === strpos($url, 'https:')) {
                    $url = ltrim(ltrim($url, '/'));
                    $url = "{$this->baseUrl}/{$url}";
                }

                return $this->aggregate->request($method, $url, $body, $headers);
            }
        };
    }

    protected function getConnectClient(string $secret = self::SECRET)
    {
        $signingClient = new Signing($this->getHttpClient(), $secret);
        $connectUrl = rtrim($this->getTestConfig()->getShopUrl(), '/') . '/?cl=makaira_connect_endpoint';

        return new ConnectClient($signingClient, $connectUrl);
    }

    protected function loginToTestingUser(Session $session = null): User
    {
        if (null === $session) {
            $session = Registry::getSession();
        }

        return (new UserService($session))->login('dev@marmalade.de', 'mGXW6qpWhQTEx-wX_!D7', false);
    }
}
