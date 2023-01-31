<?php

namespace Makaira\OxidConnectEssential\Test\Integration;

use Exception;
use JsonException;
use Makaira\OxidConnectEssential\Exception\UserBlockedException;
use Makaira\OxidConnectEssential\Service\UserService;
use Makaira\Signing\Hash\Sha256;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\CookieException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Tests\DatabaseTrait;
use OxidEsales\Facts\Edition\EditionSelector;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function json_encode;
use function md5;
use function mkdir;
use function random_bytes;
use function sprintf;
use function strtolower;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * @SuppressWarnings(PHPMD)
 */
abstract class IntegrationTestCase extends \OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase
{
    use DatabaseTrait;

    protected const SECRET = 'phpunit';

    protected const MODULE_ID = 'makaira_oxid-connect-essential';

    private int $snapshotCount = 0;

    private static ?string $shopEdition = null;

    public function setUp(): void
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
     * @throws Exception
     */
    protected function getConnectRequest(
        mixed $rawBody,
        string $secret = self::SECRET,
        bool $encodeBody = true
    ): Request {
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

        $user = new User();
        $user->setId(md5('admin@example.com'));
        $userData = [
            'oxid' => md5('admin@example.com'),
            'oxusername' => 'admin@example.com',
            'oxshopid' => 1,
            'oxrights' => 'malladmin',
            'oxfname' => 'John',
            'oxlname' => 'Doe',
        ];

        $user->assign($userData);
        $user->setPassword('phpunit_admin');

        $user->save();

        return (new UserService($session))->login('admin@example.com', 'phpunit_admin', false);
    }

    /**
     * @param mixed       $actual
     * @param string|null $message
     * @param bool        $continueIfIncomplete
     *
     * @return void|bool
     * @throws JsonException
     */
    protected function assertSnapshot(mixed $actual, ?string $message = null, bool $continueIfIncomplete = false)
    {
        $reflection = new ReflectionClass($this);

        $snapshotDir = sprintf(
            '%s/__snapshots__/%s',
            dirname($reflection->getFileName()),
            strtolower($this->getShopEdition()),
        );

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

        $actualJson = json_encode($actual, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

        if (!file_exists($snapshotFile)) {
            file_put_contents($snapshotFile, $actualJson);
            if (!$continueIfIncomplete) {
                $this->markTestIncomplete();
            }

            return false;
        }

        if (null === $message) {
            $message = sprintf("Current object doesn't match the contents of %s", $snapshotFilename);
        }

        $this->assertStringEqualsFileCanonicalizing($snapshotFile, $actualJson, $message);
    }

    protected function getShopEdition(): string
    {
        if (static::$shopEdition === null) {
            $editionSelector     = new EditionSelector();
            static::$shopEdition = $editionSelector->getEdition();
        }

        return static::$shopEdition;
    }

    protected function slugify($text, string $divider = '_'): string
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
