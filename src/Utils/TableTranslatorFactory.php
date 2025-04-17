<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\Facts\Edition\EditionSelector;

use function is_numeric;

class TableTranslatorFactory
{
    /**
     * @var array<string, int>
     */
    private array $languageMap;

    public function __construct(
        Language $language,
        private TableViewNameGenerator $viewNameGenerator,
        private EditionSelector $editionSelector,
    ) {
        $oxidLanguages = $language->getLanguageArray();
        foreach ($oxidLanguages as $oxidLanguage) {
            $this->languageMap[(string) $oxidLanguage->abbr] = (int) $oxidLanguage->id;
        }
    }

    public function create(array $searchTables, array $enterpriseSearchTables = []): TableTranslator
    {
        if ($this->editionSelector->isEnterprise()) {
            $searchTables = array_merge($searchTables, $enterpriseSearchTables);
        }

        $tableTranslator = new TableTranslator($searchTables);

        $tableTranslator->setViewNameGenerator(
            fn($table, $language, $shopId = null) => $this->viewNameGenerator->getViewName(
                $table,
                $this->mapLanguage($language),
                $shopId,
            ),
        );

        return $tableTranslator;
    }

    /**
     * @param int|string $language
     *
     * @return int|null
     */
    private function mapLanguage(int|string $language): ?int
    {
        if (is_numeric($language)) {
            return (int) $language;
        }

        return $this->languageMap[$language] ?? null;
    }
}
