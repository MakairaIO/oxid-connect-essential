<?php

namespace Makaira\OxidConnectEssential\Utils;

use Closure;

class TableTranslator
{
    /**
     * @var array<string>
     */
    private array $searchTables;

    /**
     * @var string
     */
    private string $language = 'de';

    /**
     * @var int | string | null
     */
    private int | string | null $shopId;

    /**
     * @var Closure
     */
    private Closure $viewNameGenerator;

    /**
     * TableTranslator constructor.
     *
     * @param string[] $searchTables
     */
    public function __construct(array $searchTables)
    {
        $this->searchTables = $searchTables;

        $this->viewNameGenerator = static function ($table, $language, $shopId = null) {
            if (null !== $shopId) {
                $table = "{$table}_{$shopId}";
            }

            return "oxv_{$table}_{$language}";
        };
    }

    /**
     * @param Closure $viewNameGenerator
     *
     * @return TableTranslator
     */
    public function setViewNameGenerator(Closure $viewNameGenerator): static
    {
        $this->viewNameGenerator = $viewNameGenerator;

        return $this;
    }

    /**
     * Set the language
     *
     * @param string $language
     *
     * @return TableTranslator
     */
    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param int|string|null $shopId
     *
     * @return TableTranslator
     */
    public function setShopId(int | string | null $shopId): static
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Translate an sql query
     *
     * @param string $sql
     *
     * @return string
     */
    public function translate(string $sql): string
    {
        foreach ($this->searchTables as $searchTable) {
            $viewNameGenerator = $this->viewNameGenerator;
            $replaceTable = $viewNameGenerator($searchTable, $this->language, $this->shopId);
            $sql          = preg_replace_callback(
                "((?P<tableName>{$searchTable})(?P<end>[^A-Za-z0-9_]|$))",
                static function ($match) use ($replaceTable) {
                    return $replaceTable . $match['end'];
                },
                $sql
            );
        }

        return $sql;
    }
}
