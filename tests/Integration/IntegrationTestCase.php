<?php

namespace Makaira\OxidConnectEssential\Test\Integration;

use JsonException;
use Makaira\OxidConnectEssential\Exception\UserBlockedException;
use Makaira\OxidConnectEssential\Service\UserService;
use Makaira\Signing\Hash\Sha256;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\CookieException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\TestingLibrary\UnitTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

use function debug_backtrace;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function json_encode;
use function mkdir;
use function random_bytes;
use function sprintf;
use function str_ends_with;

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

    /**
     * @param mixed  $rawBody
     * @param string $secret
     * @param bool   $encodeBody
     *
     * @return Request
     * @throws JsonException
     */
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

    /**
     * @param Session|null $session
     *
     * @return User
     * @throws UserBlockedException
     * @throws CookieException
     * @throws UserException
     */
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
     * @param mixed       $actual
     * @param string|null $message
     * @param bool        $continueIfIncomplete
     *
     * @return void|bool
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function assertSnapshot($actual, ?string $message = null, bool $continueIfIncomplete = false)
    {
        $backtrace = debug_backtrace();
        /** @var false|string $testCaseClass */
        $testCaseClass = false;
        foreach ($backtrace as $call) {
            if (str_ends_with($call['class'], 'Test')) {
                $testCaseClass = $call['class'];
                break;
            }
        }

        if (false === $testCaseClass) {
            throw new RuntimeException("Can't find test case");
        }

        $reflection = new ReflectionClass($testCaseClass);

        $snapshotDir = dirname($reflection->getFileName()) . '/__snapshots__';

        $snapshotFilename = sprintf(
            '%s--%s--%u.json',
            $reflection->getShortName(),
            $this->slugify($this->getName()),
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

    protected function slugify($text, string $divider = '_')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', $divider, $text);

        // trim
        $text = trim($text, $divider);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
