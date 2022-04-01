<?php

namespace Makaira\OxidConnectEssential\Service;

use Exception;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Rating;
use OxidEsales\Eshop\Application\Model\Review;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;

class ReviewService
{
    public function getReviews($productId, $limit = null, $offset = null): array
    {
        $product = oxNew(Article::class);
        $product->load($productId);

        $reviews = $product->getReviews();
        $reviewsArray = $reviews ? $reviews->getArray() : [];
        if ((int)$limit > 0) {
            $reviewsArray = array_slice($reviewsArray, $offset ?? 0, (int)$limit);
        }

        $result = [];
        /** @var Review $review */
        foreach ($reviewsArray as $review) {
            $user = oxNew(User::class);
            $user->load($review->getFieldData('oxuserid'));
            $result[] = [
                'reviewer_name' => "{$user->getFieldData('oxfname')} {$user->getFieldData('oxlname')}",
                'rating' => $review->getFieldData('oxrating'),
                'text' => $review->getFieldData('oxtext'),
                'created_at' => $review->getFieldData('oxcreate'),
            ];
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function createReview($productId, $rating, $text, User $user)
    {
        $product = oxNew(Article::class);
        $isLoaded = $product->load($productId);

        if (!$isLoaded) {
            throw new Exception("Failed loading product");
        }

        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $ratingModel = oxNew(Rating::class);
            if ($ratingModel->allowRating($user->getId(), 'oxarticle', $product->getId())) {
                $ratingModel->__set('oxratings__oxuserid', new Field($user->getId()));
                $ratingModel->__set('oxratings__oxtype', new Field('oxarticle'));
                $ratingModel->__set('oxratings__oxobjectid', new Field($product->getId()));
                $ratingModel->__set('oxratings__oxrating', new Field($rating));
                $ratingModel->save();
                $product->addToRatingAverage($rating);
            }
        }

        if ($reviewText = trim($text)) {
            $review = oxNew(Review::class);
            $review->__set('oxreviews__oxobjectid', new Field($product->getId()));
            $review->__set('oxreviews__oxtype', new Field('oxarticle'));
            $review->__set('oxreviews__oxtext', new Field($reviewText, FieldAlias::T_RAW));
            $review->__set('oxreviews__oxlang', new Field(Registry::getLang()->getBaseLanguage()));
            $review->__set('oxreviews__oxuserid', new Field($user->getId()));
            $review->__set('oxreviews__oxrating', new Field(($rating !== null) ? $rating : 0));
            $review->save();
        }
    }
}
