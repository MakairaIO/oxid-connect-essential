<?php

namespace Makaira\OxidConnectEssential\Rpc\Handler;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\HttpException;
use Makaira\OxidConnectEssential\Repository;
use Makaira\OxidConnectEssential\Rpc\HandlerInterface;
use Makaira\OxidConnectEssential\Utils\TableTranslator;
use OxidEsales\Eshop\Core\Language;

use function array_flip;

class GetUpdates implements HandlerInterface
{
    public function __construct(
        private Language $language,
        private TableTranslator $tableTranslator,
        private Repository $repository
    ) {
    }

    /**
     * @param array $request
     *
     * @return array
     * @throws HttpException
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function handle(array $request): mixed
    {
        if (!isset($request['since'])) {
            throw new HttpException(400);
        }

        $language  = $request['language'] ?? $this->language->getLanguageAbbr();
        $languages = array_flip($this->language->getLanguageIds());
        if (isset($languages[$language])) {
            $this->language->setBaseLanguage($languages[$language]);
        }

        $this->tableTranslator->setLanguage($language);

        return $this->repository->getChangesSince((int) $request['since'], (int) ($request['count'] ?? 50));
    }
}
