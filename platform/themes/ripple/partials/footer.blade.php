<footer class="page-footer bg-dark pt-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                <aside class="widget widget--transparent widget__footer widget__about">
                    <div class="widget__header">
                        <h3 class="widget__title">{{ __('About us') }}</h3>
                    </div>
                    <div class="widget__content">
                        <p>{{ theme_option('site_description') }}</p>
                        <div class="person-detail">
                            <p><i class="ion-home"></i>{{ theme_option('address') }}</p>
                            <p><i class="ion-earth"></i><a href="{{ theme_option('website') }}">{{ theme_option('website') }}</a></p>
                            <p><i class="ion-email"></i><a href="mailto:{{ theme_option('contact_email') }}">{{ theme_option('contact_email') }}</a></p>
                        </div>
                    </div>
                </aside>
            </div>
            {!! dynamic_sidebar('footer_sidebar') !!}
        </div>
    </div>
    <div class="page-footer__bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-sm-6">
                    <div class="page-copyright">
                        <p>{!! BaseHelper::clean(theme_option('copyright')) !!}</p>
                    </div>
                </div>
                @if (theme_option('social_links'))
                    <div class="col-md-4 col-sm-6">
                        <div class="page-footer__social">
                            <ul class="social social--simple">
                                @foreach(json_decode(theme_option('social_links'), true) as $socialLink)
                                    @if (count($socialLink) == 3)
                                        <li>
                                            <a href="{{ $socialLink[2]['value'] }}"
                                               title="{{ $socialLink[0]['value'] }}" target="_blank">
                                                <i class="hi-icon {{ $socialLink[1]['value'] }}"></i>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</footer>
<div id="back2top"><i class="fa fa-angle-up"></i></div>

<!-- JS Library-->
{!! Theme::footer() !!}

</body>
</html>
