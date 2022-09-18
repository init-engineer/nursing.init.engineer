<?php

namespace App\Domains\Social\Http\Controllers\Api\Cards;

use App\Domains\Social\Jobs\Publish\DiscordPublishJob;
use App\Domains\Social\Jobs\Publish\FacebookPublishJob;
use App\Domains\Social\Jobs\Publish\PlurkPublishJob;
use App\Domains\Social\Jobs\Publish\TelegramPublishJob;
use App\Domains\Social\Jobs\Publish\TumblrPublishJob;
use App\Domains\Social\Jobs\Publish\TwitterPublishJob;
use App\Domains\Social\Models\Cards;
use App\Domains\Social\Models\Platform;
use App\Domains\Social\Services\CardsService;
use App\Domains\Social\Services\ReviewService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class ReviewController.
 */
class ReviewController extends Controller
{
    /**
     * @var CardsService
     */
    protected $cardsService;

    /**
     * @var ReviewService
     */
    protected $reviewService;

    /**
     * ReviewController constructor.
     *
     * @param CardsService $cardsService
     * @param ReviewService $reviewService
     */
    public function __construct(CardsService $cardsService, ReviewService $reviewService)
    {
        $this->cardsService = $cardsService;
        $this->reviewService = $reviewService;
    }

    /**
     * @param Request $request
     * @param Cards $card
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function haveVoted(Request $request, Cards $card)
    {
        $voted = $this->reviewService->haveVoted($card, $request->user());
        if ($voted['voted'] || $request->user()->isAdmin()) {
            $voted['count'] = [
                'yes' => $this->reviewService->findYesByVoted($card),
                'no' => $this->reviewService->findNoByVoted($card),
            ];
        }

        return response()->json($voted, 200);
    }

    /**
     * @param Request $request
     * @param Cards $card
     * @param $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function voting(Request $request, Cards $card, $status)
    {
        $voted = $this->reviewService->haveVoted($card, $request->user());
        if ($voted['voted']) {
            return response()->json([
                'voted' => true,
                'count' => [
                    'yes' => $this->reviewService->findYesByVoted($card),
                    'no' => $this->reviewService->findNoByVoted($card),
                ],
            ], 200);
        }

        /**
         * 如果投票的是管理者，並且投的是通過票
         * 那就需要附帶文章直接通過審核的決議
         */
        if ($request->user()->isAdmin() && (bool) $status) {
            /**
             * 將文章切換為已認證狀態
             */
            $model = $this->cardsService->mark($card, true);
        }

        $this->reviewService->store([
            'model_id' => $request->user()->id,
            'card_id' => $card->id,
            'point' => ((bool) $status) ? 1 : -1,
        ]);

        return response()->json([
            'voted' => true,
            'count' => [
                'yes' => $this->reviewService->findYesByVoted($card),
                'no' => $this->reviewService->findNoByVoted($card),
            ],
        ], 200);
    }
}
