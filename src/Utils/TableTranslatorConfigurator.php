<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

use function is_numeric;

class TableTranslatorConfigurator
{
    /**
     * @var array<string>
     */
    private array $languageMap;

    /**
     * TableTranslatorConfigurator constructor.
     *
     * @param Language               $language
     * @param TableViewNameGenerator $viewNameGenerator
     */
    public function __construct(Language $language, private TableViewNameGenerator $viewNameGenerator)
    {
        $oxidLanguages = $language->getLanguageArray();
        foreach ($oxidLanguages as $oxidLanguage) {
            $this->languageMap[$oxidLanguage->abbr] = $oxidLanguage->id;
        }
    }

    /**
     * @param TableTranslator $tableTranslator
     *
     * @return void
     */
    public function configure(TableTranslator $tableTranslator): void
    {
        $tableTranslator->setViewNameGenerator(
            fn($table, $language, $shopId = null) => $this->viewNameGenerator->getViewName(
                $table,
                $this->mapLanguage($language),
                $shopId
            )
        );
    }

    /**
     * @param int|string $language
     *
     * @return string|null
     */
    private function mapLanguage(int | string $language): ?string
    {
        if (is_numeric($language)) {
            return $language;
        }

        return $this->languageMap[$language] ?? null;
    }
}
