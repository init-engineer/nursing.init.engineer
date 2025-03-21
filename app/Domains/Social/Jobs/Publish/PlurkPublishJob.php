<?php

namespace App\Domains\Social\Jobs\Publish;

use App\Domains\Social\Jobs\Push\PlurkPushCommentJob;
use App\Domains\Social\Models\Cards;
use App\Domains\Social\Models\Platform;
use App\Domains\Social\Services\Content\ContentFluent;
use App\Domains\Social\Services\PlatformCardService;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class PlurkPublishJob.
 *
 * @implements ShouldQueue
 */
class PlurkPublishJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    /**
     * @var Cards
     */
    protected $cards;

    /**
     * @var Platform
     */
    protected $platform;

    /**
     * Create a new job instance.
     *
     * @param Cards $cards
     * @param Platform $platform
     *
     * @return void
     */
    public function __construct(Cards $cards, Platform $platform)
    {
        $this->cards = $cards;
        $this->platform = $platform;
    }

    /**
     * Execute the job.
     *
     * 發表到 Plurk
     * 字數限制 360 字元，因此需要留言補充連結宣傳。
     *
     * @return void
     */
    public function handle()
    {
        /**
         * 判斷 Blog Name、Consumer Key、Consumer Secret、Token、Token Secret 是否為空
         */
        if (
            !isset($this->platform->config['consumer_app_key']) ||
            !isset($this->platform->config['consumer_app_secret']) ||
            !isset($this->platform->config['access_token']) ||
            !isset($this->platform->config['access_token_secret'])
        ) {
            /**
             * Config 有問題，無法處理
             */
            activity('social cards - publish error')
                ->performedOn($this->cards)
                ->log(json_encode($this->platform));

            return;
        }

        /**
         * 建立 Content 內容編排器
         */
        $container = Container::getInstance();
        $contentFluent = $container->make(ContentFluent::class);
        $platformCardService = $container->make(PlatformCardService::class);

        /**
         * 透過 Guzzle 的 HandlerStack 來建立堆疊
         */
        $stack = HandlerStack::create();

        /**
         * 透過 Guzzle 的 OAuth1 來建立請求
         */
        $middleware = new Oauth1([
            'consumer_key'    => $this->platform->config['consumer_app_key'],
            'consumer_secret' => $this->platform->config['consumer_app_secret'],
            'token'           => $this->platform->config['access_token'],
            'token_secret'    => $this->platform->config['access_token_secret'],
        ]);
        $stack->push($middleware);

        /**
         * 開始執行通知
         */
        $client = Http::withMiddleware($middleware)
            ->withOptions([
                'base_uri' => 'https://www.plurk.com',
                'handler' => $stack,
                'auth' => 'oauth',
            ]);

        /**
         * 判斷文章是否已經發表出去
         */
        if ($platformCard = $platformCardService->findPlatformCardById($this->platform->id, $this->cards->id)) {
            /**
             * 在這個 Plurk 已經將文章發表出去，並且記錄起來了
             */
            activity('social cards - plurk post published')
                ->performedOn($this->cards)
                ->log(json_encode($platformCard));

            return;
        }

        /**
         * 先將圖片透過 multipart/form-data 的方式上傳到 Plurk
         */
        $pictureArray = explode('/', $this->cards->getPicture());
        $pictureResponse = $client->asMultipart()->post('/APP/Timeline/uploadPicture', [
            [
                'name' => 'image',
                'contents' => Storage::get(str_replace(appUrl() . '/storage', 'public', $this->cards->getPicture())),
                'filename' => array_pop($pictureArray),
            ],
        ]);

        /**
         * 紀錄 picture response 資訊
         */
        activity('social cards - plurk publish - picture')
            ->performedOn($this->cards)
            ->log($pictureResponse->body());

        /**
         * 整理文章通知的內容
         */
        $content = $contentFluent->reset()
            ->image($pictureResponse['full'])
            ->body('投稿網址： https://portaly.cc/cowbanursing')
            ->hr()
            ->header($this->cards->id)
            ->hr()
            ->body(Str::limit($this->cards->content, 300, ' ...'))
            ->build();

        /**
         * 將圖片拼到噗文當中發表出去
         */
        $plurkResponse = $client->post('/APP/Timeline/plurkAdd', [
            'content' => $content,
            'qualifier' => 'says',
            'lang' => 'tr_ch',
        ]);

        /**
         * 紀錄 plurk response 資訊
         */
        activity('social cards - plurk publish - plurk')
            ->performedOn($this->cards)
            ->log($plurkResponse->body());

        /**
         * 建立 PlatformCards 紀錄
         */
        $platformCard = $platformCardService->store([
            'platform_type' => Platform::TYPE_PLURK,
            'platform_id' => $this->platform->id,
            'platform_string_id' => base_convert($plurkResponse->json()['plurk_id'], 10, 36),
            'platform_url' => sprintf(
                'https://www.plurk.com/p/%s',
                base_convert($plurkResponse->json()['plurk_id'], 10, 36),
            ),
            'card_id' => $this->cards->id,
        ]);

        /**
         * 紀錄 PlatformCards
         */
        activity('social cards - plurk platform card')
            ->performedOn($platformCard)
            ->log(json_encode($platformCard));

        return;
    }
}
