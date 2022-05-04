<?php

namespace Makaira\OxidConnectEssential\Test\Integration;

use JsonException;
use Makaira\OxidConnectEssential\Service\UserService;
use Makaira\Signing\Hash\Sha256;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\TestingLibrary\UnitTestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use function basename;
use function debug_backtrace;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function json_encode;
use function mkdir;
use function random_bytes;
use function sprintf;
use function str_replace;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

abstract class IntegrationTestCase extends UnitTestCase
{
    protected const SECRET = 'phpunit';

    private int $snapshotCount = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshotCount = 0;
    }

    protected function getConnectRequest($rawBody, string $secret = self::SECRET, bool $encodeBody = true): Request
    {
        $nonce = md5(random_bytes(32));

        $body = $rawBody;
        if ($encodeBody) {
            $body = json_encode($rawBody, JSON_THROW_ON_ERROR);
        }

        $signature = (new Sha256())->hash($nonce, $body, $secret);

        $server = [
            'HTTP_X-MAKAIRA-NONCE' => $nonce,
            'HTTP_X-MAKAIRA-HASH'  => $signature,
        ];

        return new Request([], [], [], [], [], $server, $body);
    }

    /**
     * @param string $name
     * @param string $moduleId
     *
     * @return mixed
     */
    protected static function getModuleSetting(string $name, string $moduleId = 'makaira_oxid-connect-essential')
    {
        return self::getContainer()->get(ModuleSettingBridgeInterface::class)->get($name, $moduleId);
    }

    /**
     * @param string $name
     * @param mixed  $value
     * @param string $moduleId
     *
     * @return void
     */
    protected static function setModuleSetting(
        string $name,
        $value,
        string $moduleId = 'makaira_oxid-connect-essential'
    ): void {
        $moduleSettings = self::getContainer()->get(ModuleSettingBridgeInterface::class);
        $moduleSettings->save($name, $value, $moduleId);
    }

    protected function loginToTestingUser(Session $session = null): User
    {
        if (null === $session) {
            $session = Registry::getSession();
        }

        return (new UserService($session))->login('dev@marmalade.de', 'mGXW6qpWhQTEx-wX_!D7', false);
    }

    /**
     * @return ContainerInterface
     */
    protected static function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }

    /**
     * @param mixed $actual
     *
     * @return void|bool
     * @throws JsonException
     */
    protected function assertSnapshot($actual, ?string $message = null, bool $continueIfIncomplete = false)
    {
        $backtrace = debug_backtrace();
        $snapshotDir = dirname($backtrace[0]['file']) . '/__snapshots__';

        $snapshotFilename = sprintf(
            '%s__%s__%u.json',
            basename(str_replace('\\', '/', $backtrace[1]['class'])),
            $backtrace[1]['function'],
            $this->snapshotCount
        );

        $this->snapshotCount++;

        if (!is_dir($snapshotDir)) {
            mkdir($snapshotDir, 0755, true);
        }

        $snapshotFile = "{$snapshotDir}/{$snapshotFilename}";

        if (!file_exists($snapshotFile)) {
            file_put_contents($snapshotFile, json_encode($actual, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            if (!$continueIfIncomplete) {
                $this->markTestIncomplete();
            }

            return false;
        }

        $actualJson = json_encode($actual, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        if (null === $message) {
            $message = sprintf("Current object doesn't match the contents of %s", $snapshotFilename);
        }

        $this->assertStringEqualsFileCanonicalizing($snapshotFile, $actualJson, $message);
    }
}
