@extends('frontend.layouts.app')

@section('title', __('Home'))
@section('meta_title', appName() . ' | ' . __('Home'))
@section('meta_description', appName() . ' | ' . __('Home'))

@push('after-styles')
<style>
    @media (min-width: 768px) {
        .border-right-md {
            border-right: 2px solid var(--color-gray);
        }

        .padding-right-md {
            padding-right: 3rem;
        }

        .padding-left-md {
            padding-left: 3rem;
        }

        .margin-y-md {
            margin-top: 3rem;
            margin-bottom: 3rem;
        }
    }
</style>
@endpush

@push('after-scripts')
<script>
    /**
     * 讓右邊的 Title 也能有一點關心使用者的感覺
     */
    const now = new Date();
    let title = '想來靠北些什麼？';
    if (now.getHours() >= 0 && now.getHours() < 6) {
        title = '凌晨安安，' + title;
    } else if (now.getHours() >= 6 && now.getHours() < 12) {
        title = '早上安安，' + title;
    } else if (now.getHours() >= 12 && now.getHours() < 18) {
        title = '下午安安，' + title;
    } else {
        title = '晚上安安，' + title;
    }

    new Typed('#title', {
        /**
         * @property {string} stringsElement ID of element containing string children
         */
        strings: [title],

        /**
         * @property {number} typeSpeed type speed in milliseconds
         */
        typeSpeed: 120,

        /**
         * @property {boolean} showCursor show cursor
         * @property {string} cursorChar character for cursor
         * @property {boolean} autoInsertCss insert CSS for cursor and fadeOut into HTML <head>
         */
        showCursor: false
        , cursorChar: '|'
        , autoInsertCss: true
    , });

    /**
     * JavaScript 載入後先等 3 秒跑 Title，再去跑 Subtitle 的內容。
     * 透過 Typed.js 讓 #subtitle 能夠自動 Typeing 內容。
     */
    setTimeout(function() {
        new Typed('#subtitle', {
            /**
             * @property {array} strings strings to be typed
             */
            strings: [
                '媽的勒，學姊很討厭欸，一直亂電人，自己也沒有多厲害 ...',
                '幹，我的防疫獎金去哪裡了 ...',
                '護理師公會還存在做什麼啊？要不要廢掉？'
            ],

            /**
             * @property {number} typeSpeed type speed in milliseconds
             */
            typeSpeed: 120,

            /**
             * @property {number} backSpeed backspacing speed in milliseconds
             */
            backSpeed: 80,

            /**
             * @property {boolean} smartBackspace only backspace what doesn't match the previous string
             */
            smartBackspace: true,

            /**
             * @property {boolean} loop loop strings
             * @property {number} loopCount amount of loops
             */
            loop: true
            , loopCount: Infinity,

            /**
             * @property {boolean} showCursor show cursor
             * @property {string} cursorChar character for cursor
             * @property {boolean} autoInsertCss insert CSS for cursor and fadeOut into HTML <head>
             */
            showCursor: false
            , cursorChar: '|'
            , autoInsertCss: true
        , });
    }, 3000);
</script>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 100vw;">
    <div class="row justify-content-center">
        <div class="col-md-9 order-md-first order-last border-right-md padding-right-md">
            <h1 class="pb-2 mb-2">最新審核通過的文章👇</h1>
            @foreach($safeCards as $card)
                <div class="media my-2 pb-2">
                    <img class="rounded mr-2 thumb gallery-slideshow" style="width: 128px; height: 128px" src="{{ $card->getPicture() }}" alt="#{{ appName() . base_convert($card->id, 10, 36) }}" />
                    <div class="media-body pt-2">
                        <a class="text-decoration-none" style="color: var(--font-primary-color) !important;" href="{{ route('frontend.social.cards.show', ['id' => $card->id]) }}">
                            <div style="display: flow-root">
                                <h4 class="float-left my-0">#{{ appName() . base_convert($card->id, 10, 36) }}</h4>
                                <p class="float-right my-0">@displayDate($card->updated_at, 'Y/m/d h:s:i') ({{ $card->updated_at->diffForHumans() }})</p>
                            </div>
                            <p class="mb-0">{{ $card->getContent(200) }}</p>
                        </a>
                    </div>
                </div>
            @endforeach

            <hr class="border margin-y-md">

            <div class="w-100 text-center">
                <p class="pt-2 my-0">我沒有想寫懶加載的意思，所以給一個文章列表的連結，你們自己去看吧😎👍</p>
                <a class="btn btn-bg btn-lg h1 py-2 px-5 my-2" href="{{ route('frontend.social.cards.index') }}">@lang('Posts List')</a>
            </div>
            <!--more-->
        </div>
        <!--col-md-9-->

        <div class="col-md-3 order-md-last order-first text-center mb-5 padding-left-md">
            <h2 class="my-2 mx-auto" id="title"></h2>
            <div class="form-group">
                <textarea class="form-control form-control-lg" id="subtitle" rows="9" disabled></textarea>
            </div>
            <a class="btn btn-bg btn-lg h1 py-2 px-5 my-2" href="{{ route('frontend.social.cards.publish.article') }}">前往投稿</a>
        </div>
        <!--col-md-3-->
    </div>
    <!--row-->
</div>
<!--container-->
@endsection
