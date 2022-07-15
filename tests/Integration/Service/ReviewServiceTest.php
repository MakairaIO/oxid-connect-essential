<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Service;

use Exception;
use Makaira\OxidConnectEssential\Service\ReviewService;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use OxidEsales\Eshop\Core\Registry;

class ReviewServiceTest extends IntegrationTestCase
{
    public function testGetReviews()
    {
        $oxidLanguage    = Registry::getLang();
        $oldBaseLanguage = $oxidLanguage->getBaseLanguage();
        $oldTplLanguage  = $oxidLanguage->getTplLanguage();
        $oxidLanguage->setBaseLanguage(1);
        $oxidLanguage->setTplLanguage(1);

        $reviewService = new ReviewService();
        $expected      = [
            [
                "reviewer_name" => "Marc Muster",
                "rating"        => "5",
                "text"          => "Fantastic kite with great performance!",
                "created_at"    => "2011-03-25 16:51:05",
            ],
        ];

        self::assertEquals($expected, $reviewService->getReviews('b56597806428de2f58b1c6c7d3e0e093'));

        $oxidLanguage->setBaseLanguage($oldBaseLanguage);
        $oxidLanguage->setTplLanguage($oldTplLanguage);
    }

    public function testReturnsEmptyArrayIfThe()
    {
        $reviewService = new ReviewService();

        self::assertEmpty($reviewService->getReviews('b56597806428de2f58b1c6c7d3e0e093'));
    }

    public function testThrowsExceptionIfCreateReviewForInvalidProductId()
    {
        $user = $this->loginToTestingUser();

        $reviewService = new ReviewService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Failed loading product");

        $reviewService->createReview('phpunit42', 5, 'PHPUnit Test', $user);
    }

    public function testThrowsExceptionIfGettingReviewsForInvalidProductId()
    {
        $reviewService = new ReviewService();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Failed loading product");

        $reviewService->getReviews('phpunit42');
    }

    public function testCreateReviews()
    {
        $user = $this->loginToTestingUser();

        $reviewService = new ReviewService();
        $reviewService->createReview('b56597806428de2f58b1c6c7d3e0e093', '5', 'testing review', $user);
        $expected = [
            [
                "reviewer_name" => 'John Doe',
                "rating" => "5",
                "text" => "testing review",
                "created_at" => "XXXX-XX-XX XX:XX:XX"
            ]
        ];

        $reviews = $reviewService->getReviews('b56597806428de2f58b1c6c7d3e0e093');
        $reviews[0]['created_at'] = "XXXX-XX-XX XX:XX:XX";
        self::assertEquals($expected, $reviews);
    }

    public function testGetOnlyPartialReviews()
    {
        $reviewService = new ReviewService();
        $reviews = $reviewService->getReviews('b56597806428de2f58b1c6c7d3e0e093', 1, 0);
        $expected = [
            [
                "reviewer_name" => 'John Doe',
                "rating" => "5",
                "text" => "testing review",
                "created_at" => "XXXX-XX-XX XX:XX:XX"
            ],
        ];
        $reviews[0]['created_at'] = "XXXX-XX-XX XX:XX:XX";
        self::assertEquals($expected, $reviews);
    }
}
