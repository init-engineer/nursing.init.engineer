<?php

namespace App\Domains\Social\Jobs\Publish;

use App\Domains\Social\Jobs\Push\TwitterPushCommentJob;
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
 * Class TwitterPublishJob.
 *
 * @implements ShouldQueue
 */
class TwitterPublishJob implements ShouldQueue
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
     * 發表到 Twitter
     * 字數限制 280 字元，因此需要留言補充連結宣傳。
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
                'handler' => $stack,
                'auth' => 'oauth',
            ]);

        /**
         * 判斷文章是否已經發表出去
         */
        if ($platformCard = $platformCardService->findPlatformCardById($this->platform->id, $this->cards->id)) {
            /**
             * 在這個 Twitter 已經將文章發表出去，並且記錄起來了
             */
            activity('social cards - twitter post published')
                ->performedOn($this->cards)
                ->log(json_encode($platformCard));

            return;
        }

        /**
         * 先判斷媒體是圖片(jpg、jpeg、png)還是動畫(gif)
         */
        $tweetType = explode('.', $this->cards->getPicture());
        $tweetType = array_pop($tweetType);
        $tweetType = ($tweetType === 'gif') ? 'tweet_gif' : 'tweet_image';

        /**
         * 先將圖片透過 multipart/form-data 的方式上傳到 Twitter
         */
        $pictureArray = explode('/', $this->cards->getPicture(),);
        $pictureResponse = $client->asMultipart()->post('https://upload.twitter.com/1.1/media/upload.json?media_category=' . $tweetType, [
            [
                'name' => 'media',
                'contents' => Storage::get(str_replace(appUrl() . '/storage', 'public', $this->cards->getPicture())),
                'filename' => array_pop($pictureArray),
            ],
        ]);

        /**
         * 紀錄 picture response 資訊
         */
        activity('social cards - twitter publish - picture')
            ->performedOn($this->cards)
            ->log($pictureResponse->body());

        /**
         * 整理文章通知的內容
         */
        $status = $contentFluent->reset()
            ->header('投稿網址： https://cowbanursing.soci.vip/')
            ->hr()
            ->header($this->cards->id)
            ->hr()
            ->body(Str::limit($this->cards->content, 200, ' ...'))
            ->build();

        /**
         * 將圖片拼到推文當中發表出去
         */
        $tweetResponse = $client->asForm()->post('https://api.twitter.com/1.1/statuses/update.json', [
            'status' => $status,
            'media_ids' => $pictureResponse['media_id_string'],
        ]);

        /**
         * 紀錄 picture response 資訊
         */
        activity('social cards - twitter publish - tweet')
            ->performedOn($this->cards)
            ->log($tweetResponse->body());

        /**
         * 建立 PlatformCards 紀錄
         */
        $platformCard = $platformCardService->store([
            'platform_type' => Platform::TYPE_TWITTER,
            'platform_id' => $this->platform->id,
            'platform_string_id' => $tweetResponse->json()['id_str'],
            'platform_url' => sprintf(
                'https://twitter.com/%s/status/%s',
                $this->platform->config['pages_name'],
                $tweetResponse->json()['id_str'],
            ),
            'card_id' => $this->cards->id,
        ]);

        /**
         * 紀錄 PlatformCards
         */
        activity('social cards - twitter platform card')
            ->performedOn($platformCard)
            ->log(json_encode($platformCard));

        return;
    }
}
