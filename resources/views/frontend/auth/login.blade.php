@extends('frontend.layouts.app')

@section('title', __('Login'))
@section('meta_title', appName() . ' | ' . __('Login'))
@section('meta_description', appName() . ' | ' . __('Login'))

@push('after-scripts')
    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    </script>
@endpush

@section('content')
    <div class="container d-none d-md-block" id="container">
        <div class="form-container sign-in-container">
            <x-forms.post :action="route('frontend.auth.login')">
                <h1>{{ __('Login') }}</h1>
                <div class="social-container py-2">
                    @include('frontend.auth.includes.social')
                </div>
                <span class="py-2">目前不開放註冊帳號。</span>
                <div class="form-group row mb-2">
                    <label for="email" class="col-md-12 col-form-label text-md-right pb-1">@lang('E-mail Address')</label>

                    <div class="col-md-12">
                        <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="{{ __('E-mail Address') }}" value="{{ old('email') }}" maxlength="255" required autofocus autocomplete="email" />
                    </div>
                </div><!--form-group-->
                <div class="form-group row mb-2">
                    <label for="password" class="col-md-12 col-form-label text-md-right pb-1">@lang('Password')</label>

                    <div class="col-md-12">
                        <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="{{ __('Password') }}" maxlength="255" required autofocus autocomplete="email" />
                    </div>
                </div><!--form-group-->
                <x-utils.link :href="route('frontend.auth.password.request')" class="btn btn-link" :text="__('Forgot Your Password?')" />
                <button class="btn btn-dos btn-lg btn-block my-4" type="submit">{{ __('Login') }}</button>
            </x-forms.post>
        </div><!--sign-in-container-->

        <div class="overlay-container">
            <div class="overlay">
            </div>
        </div><!--overlay-container-->
    </div><!--container-->

    <div id="container" class="container d-block d-md-none" style="width: 90%; min-height: 640px;">
        <div class="form-container" style="width: 100%; left: 0px;">
            <x-forms.post :action="route('frontend.auth.login')" class="px-3">
                <h1>{{ __('Login') }}</h1>
                <div class="social-container py-2">
                    @include('frontend.auth.includes.social')
                </div>
                <span class="py-2">{{ __('or use your account') }}</span>
                <div class="form-group row mb-2 w-100">
                    <label for="email" class="col-md-12 col-form-label text-md-right pb-1">@lang('E-mail Address')</label>

                    <div class="col-md-12">
                        <input type="email" name="email" id="email" class="form-control form-control-lg" placeholder="{{ __('E-mail Address') }}" value="{{ old('email') }}" maxlength="255" required autofocus autocomplete="email" />
                    </div>
                </div><!--form-group-->
                <div class="form-group row mb-2 w-100">
                    <label for="password" class="col-md-12 col-form-label text-md-right pb-1">@lang('Password')</label>

                    <div class="col-md-12">
                        <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="{{ __('Password') }}" maxlength="255" required autofocus autocomplete="email" />
                    </div>
                </div><!--form-group-->
                <x-utils.link :href="route('frontend.auth.password.request')" class="btn btn-link" :text="__('Forgot Your Password?')" />
                <button class="btn btn-dos btn-lg btn-block my-4" type="submit">{{ __('Login') }}</button>
            </x-forms.post>
        </div><!--sign-in-container-->
    </div><!--container-->
@endsection
