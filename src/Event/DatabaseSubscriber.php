<?php

declare(strict_types=1);

namespace Makaira\OxidConnectEssential\Event;

use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\RevisionHandler\ModelDataExtractor;
use Makaira\OxidConnectEssential\RevisionHandler\ModelNotSupportedException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Event\AbstractShopAwareEventSubscriber;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\AfterModelDeleteEvent;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\AfterModelInsertEvent;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\AfterModelUpdateEvent;
use OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\BeforeHeadersSendEvent;
use Symfony\Component\EventDispatcher\Event;

use function array_replace;

class DatabaseSubscriber extends AbstractShopAwareEventSubscriber
{
    /**
     * @var array<array<string, Revision>>
     */
    private array $revisions = [];

    private ModelDataExtractor $dataExtractor;

    private RevisionRepository $revisionRepository;

    /**
     * @param ModelDataExtractor $dataExtractor
     */
    public function __construct(
        ModelDataExtractor $dataExtractor,
        RevisionRepository $revisionRepository
    ) {
        $this->revisionRepository = $revisionRepository;
        $this->dataExtractor      = $dataExtractor;
    }

    /**
     * @param AfterModelUpdateEvent|AfterModelInsertEvent|AfterModelDeleteEvent $event
     *
     * @return void
     */
    public function onChange(Event $event): void
    {
        $this->processModel($event->getModel());
    }

    private function processModel(BaseModel $model): void
    {
        try {
            $this->revisions[] = $this->dataExtractor->extractData($model);
        } catch (ModelNotSupportedException $e) {
        }
    }

    public function writeRevisions(): void
    {
        $revisions = array_replace([], ...$this->revisions);
        if (0 < count($revisions)) {
            $this->revisionRepository->storeRevisions($revisions);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterModelUpdateEvent::NAME => 'onChange',
            AfterModelInsertEvent::NAME => 'onChange',
            AfterModelDeleteEvent::NAME => 'onChange',
            BeforeHeadersSendEvent::NAME => 'writeRevisions',
        ];
    }
}
