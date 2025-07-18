<?php

namespace App\Domains\Social\Http\Controllers\Api\Cards;

use App\Domains\Social\Events\Cards\ArticleCreated;
use App\Domains\Social\Events\Cards\PictureCreated;
use App\Domains\Social\Http\Requests\Api\Publish\PublishArticleRequest;
use App\Domains\Social\Http\Requests\Api\Publish\PublishPictureRequest;
use App\Domains\Social\Http\Requests\Api\Publish\PublishPlatformRequest;
use App\Domains\Social\Http\Resources\CardResource;
use App\Domains\Social\Models\Ads;
use App\Domains\Social\Models\Cards;
use App\Domains\Social\Services\AdsService;
use App\Domains\Social\Services\CardsService;
use App\Domains\Social\Services\Image\ImagesGenerator;
use App\Http\Controllers\Controller;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

/**
 * Class PublishController.
 */
class PublishController extends Controller
{
    /**
     * @var CardsService
     */
    protected $service;

    /**
     * PublishController constructor.
     *
     * @param CardsService $service
     */
    public function __construct(CardsService $service)
    {
        $this->service = $service;
    }

    /**
     * 文字投稿
     *
     * @param PublishArticleRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function article(PublishArticleRequest $request)
    {
        /**
         * 整理文字投稿資訊
         */
        $data = $request->validated();

        /**
         * 透過 ImagesGenerator 去產生文字圖片
         */
        $container = Container::getInstance();
        $generator = $container->make(ImagesGenerator::class);
        $picture = $generator->content($data['content'])
            ->theme($data['config']['theme'])
            ->font($data['config']['font'])
            ->build();

        /**
         * 處理投稿資訊的文字資訊
         */
        $data['picture'] = [
            'local' => $picture['picture'],
            'storage' => null,
            'imgur' => null,
        ];

        /**
         * 處理投稿資訊的設定資訊
         */
        $data['config'] = [
            'type' => 'article',
            'theme' => $data['config']['theme'],
            'font' => $data['config']['font'],
            'ads' => $picture['ads'],
            'check_code' => $data['check_code'],
        ];
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');

        /**
         * 將文字投稿寫入
         */
        $card = $this->service->store($data);

        if ($picture['ads']['result']) {
            $adsService = $container->make(AdsService::class);
            $adsService->deploy(Ads::find($picture['ads']['data']['id']), $card);
        }

        event(new ArticleCreated($card));

        return new CardResource($card);
    }

    /**
     * 圖片投稿
     *
     * @param PublishPictureRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function picture(PublishPictureRequest $request)
    {
        /**
         * 將圖片儲存到 Local 當中
         */
        $path = $request->file('picture')->store('public/cards/custom');
        $path = str_replace('public', 'storage', $path);

        /**
         * 整理圖片投稿資訊
         */
        $data = $request->validated();
        $data['picture'] = [
            'local' => $path,
            'storage' => null,
            'imgur' => null,
        ];
        $data['config'] = [
            'type' => 'picture',
            'check_code' => $data['check_code'],
        ];
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');

        /**
         * 將圖片投稿寫入
         */
        $card = $this->service->store($data);

        event(new PictureCreated($card));

        return new CardResource($card);
    }
}
