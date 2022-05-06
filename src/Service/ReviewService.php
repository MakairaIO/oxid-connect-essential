<?php

namespace Makaira\OxidConnectEssential\Service;

use Exception;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Rating;
use OxidEsales\Eshop\Application\Model\Review;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\Eshop\Core\Registry;

class ReviewService
{
    public function getReviews(string $productId, int $limit = null, int $offset = 0): array
    {
        $product = oxNew(Article::class);
        $isLoaded = $product->load($productId);

        if (!$isLoaded) {
            throw new Exception("Failed loading product");
        }

        /** @var ListModel $reviews */
        $reviews = $product->getReviews();
        $reviewsArray = $reviews->getArray();

        if ($limit > 0) {
            $reviewsArray = array_slice($reviewsArray, $offset, $limit);
        }

        $result = [];
        /** @var Review $review */
        foreach ($reviewsArray as $review) {
            /** @var string $userId */
            $userId = $review->getFieldData('oxuserid');

            $user = oxNew(User::class);
            $user->load($userId);

            /** @var string $firstName */
            $firstName = $user->getFieldData('oxfname');

            /** @var string $lastName */
            $lastName = $user->getFieldData('oxlname');

            $result[] = [
                'reviewer_name' => "{$firstName} {$lastName}",
                'rating'        => $review->getFieldData('oxrating'),
                'text'          => $review->getFieldData('oxtext'),
                'created_at'    => $review->getFieldData('oxcreate'),
            ];
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function createReview(string $productId, int $rating, string $text, User $user): void
    {
        $product = oxNew(Article::class);
        $isLoaded = $product->load($productId);

        if (!$isLoaded) {
            throw new Exception("Failed loading product");
        }

        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $ratingModel = oxNew(Rating::class);
            if ($ratingModel->allowRating($user->getId(), 'oxarticle', $product->getId())) {
                $ratingModel->assign(
                    [
                        'oxuserid'   => new Field($user->getId()),
                        'oxtype'     => 'oxarticle',
                        'oxobjectid' => $product->getId(),
                        'oxrating'   => $rating,
                    ]
                );
                $ratingModel->save();
                $product->addToRatingAverage($rating);
            }
        }

        if ($reviewText = trim($text)) {
            $review = oxNew(Review::class);
            $review->assign(
                [
                    'oxobjectid' => $product->getId(),
                    'oxtype'     => 'oxarticle',
                    'oxtext'     => $reviewText,
                    'oxlang'     => Registry::getLang()->getBaseLanguage(),
                    'oxuserid'   => $user->getId(),
                    'oxrating'   => $rating,
                ]
            );
            $review->save();
        }
    }
}
