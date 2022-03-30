<?php

namespace Makaira\OxidConnectEssential\Controller;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use OxidEsales\Eshop\Application\Model\Rating;
use OxidEsales\Eshop\Application\Model\Review;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Field as FieldAlias;

class ReviewController extends BaseController
{
    public function getReviews()
    {
        ['id' => $productId, 'limit' => $limit, 'offset' => $offset] = $this->getRequestBody();

        $product = oxNew(Article::class);
        $product->load($productId);

        $reviews = $product->getReviews();
        $reviewsArray =  $reviews ? $reviews->getArray() : [];
        if ((int) $limit > 0) {
            $reviewsArray = array_slice($reviewsArray, $offset ?? 0, (int) $limit);
        }

        $response = [];

        /** @var Review $review */
        foreach ($reviewsArray as $review) {
            $user = oxNew(User::class);
            $user->load($review->getFieldData('oxuserid'));
            $response[] = [
                'reviewer_name' => "{$user->getFieldData('oxfname')} {$user->getFieldData('oxlname')}",
                'rating' => $review->getFieldData('oxrating'),
                'text' => $review->getFieldData('oxtext'),
                'created_at' => $review->getFieldData('oxcreate'),
            ];
        }

        $this->sendResponse($response);
    }

    /**
     * @throws Exception
     */
    #[NoReturn]
    public function createReview()
    {
        $user = $this->checkAndGetActiveUser();

        ['product_id' => $productId, 'rating' => $rating, 'text' => $text] = $this->getRequestBody();

        $product = oxNew(Article::class);
        $isLoaded = $product->load($productId);

        if (!$isLoaded) {
            $this->sendResponse(["message" => "Failed loading product."], 500);
        }

        if ($rating !== null && $rating >= 1 && $rating <= 5) {
            $ratingModel = oxNew(Rating::class);
            if ($ratingModel->allowRating($user->getId(), 'oxarticle', $product->getId())) {
                $ratingModel->oxratings__oxuserid = new Field($user->getId());
                $ratingModel->oxratings__oxtype = new Field('oxarticle');
                $ratingModel->oxratings__oxobjectid = new Field($product->getId());
                $ratingModel->oxratings__oxrating = new Field($rating);
                $ratingModel->save();
                $product->addToRatingAverage($rating);
            }
        }

        if ($reviewText = trim($text)) {
            $review = oxNew(Review::class);
            $review->oxreviews__oxobjectid = new Field($product->getId());
            $review->oxreviews__oxtype = new Field('oxarticle');
            $review->oxreviews__oxtext = new Field($reviewText, FieldAlias::T_RAW);
            $review->oxreviews__oxlang = new Field(Registry::getLang()->getBaseLanguage());
            $review->oxreviews__oxuserid = new Field($user->getId());
            $review->oxreviews__oxrating = new Field(($rating !== null) ? $rating : 0);
            $review->save();
        }

        $this->sendResponse(['success' => 'true']);
    }
}