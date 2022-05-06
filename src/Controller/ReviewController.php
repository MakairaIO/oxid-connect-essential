<?php

namespace Makaira\OxidConnectEssential\Controller;

use Exception;
use Makaira\OxidConnectEssential\Service\ReviewService;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;

class ReviewController extends BaseController
{
    private ReviewService $reviewService;

    public function __construct()
    {
        parent::__construct();
        /** @var ReviewService $reviewService */
        $reviewService       = ContainerFactory::getInstance()->getContainer()->get(ReviewService::class);
        $this->reviewService = $reviewService;
    }

    public function getReviews(): void
    {
        ['id' => $productId, 'limit' => $limit, 'offset' => $offset] = $this->getRequestBody();

        try {
            $this->sendResponse($this->reviewService->getReviews($productId, $limit, $offset));
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createReview(): void
    {
        $user = $this->checkAndGetActiveUser();
        ['product_id' => $productId, 'rating' => $rating, 'text' => $text] = $this->getRequestBody();

        try {
            $this->reviewService->createReview($productId, $rating, $text, $user);

            $this->sendResponse(['success' => 'true']);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
