<?php

namespace App\Domains\Social\Jobs\Publish;

use App\Domains\Social\Models\Cards;
use App\Domains\Social\Models\Platform;
use App\Domains\Social\Services\Content\ContentFluent;
use App\Domains\Social\Services\PlatformCardService;
use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Class TelegramPublishJob.
 */
class TelegramPublishJob implements ShouldQueue
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
     * @return void
     */
    public function handle()
    {
        /**
         * 判斷 Access token 與 Chat ID 是否為空
         */
        if (
            !isset($this->platform->config['chat_id']) ||
            !isset($this->platform->config['access_token'])
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
         * 整理文章通知的內容
         */
        $caption = $contentFluent->reset()
            ->header('投稿網址： https://cowbanursing.soci.vip/')
            ->hr()
            ->header($this->cards->id)
            ->hr()
            ->body($this->cards->content)
            ->build();

        /**
         * 判斷文章是否已經發表出去
         */
        if ($platformCard = $platformCardService->findPlatformCardById($this->platform->id, $this->cards->id)) {
            /**
             * 在這個 Telegram 已經將文章發表出去，並且記錄起來了
             */
            activity('social cards - telegram post published')
                ->performedOn($this->cards)
                ->log(json_encode($platformCard));

            return;
        }

        /**
         * 開始執行通知
         */
        $token = $this->platform->config['access_token'];
        $url = "https://api.telegram.org/bot$token/sendPhoto";
        $response = Http::post($url, [
            'chat_id' => $this->platform->config['chat_id'],
            'photo' => $this->cards->getPicture(),
            'caption' => $caption,
        ]);

        /**
         * 紀錄 response 資訊
         */
        activity('social cards - telegram publish')
            ->performedOn($this->cards)
            ->log($response->body());

        /**
         * 建立 PlatformCards 紀錄
         */
        $platformCard = $platformCardService->store([
            'platform_type' => Platform::TYPE_TELEGRAM,
            'platform_id' => $this->platform->id,
            'platform_string_id' => $response->json()['result']['message_id'],
            'platform_url' => sprintf(
                'https://t.me/%s/%s',
                $this->platform->config['pages_name'],
                $response->json()['result']['message_id'],
            ),
            'card_id' => $this->cards->id,
        ]);

        /**
         * 紀錄 PlatformCards
         */
        activity('social cards - telegram platform card')
            ->performedOn($platformCard)
            ->log(json_encode($platformCard));

        return;
    }
}
