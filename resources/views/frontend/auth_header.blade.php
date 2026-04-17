<header>
    <div class="headerarea headerarea__2 header__sticky header__area">
        <div class="container desktop__menu__wrapper uda-header-container">
            <div class="uda-navbar">
                <!-- Left: Logo Only -->
                <div class="uda-nav-left">
                    @if (count($systemDetail) > 0 && $systemDetail[0]->logo_image !== null)
                        <a href="{{route('frontend.welcome')}}"><img loading="lazy" src="{{asset('logo/'.$systemDetail[0]->logo_image)}}" class="uda-logo" alt="logo"></a>
                    @else
                        <a href="{{route('frontend.welcome')}}"><img loading="lazy" src="{{asset('img/cug_logo_new.jpeg')}}" class="uda-logo" alt="logo"></a>
                    @endif
                </div>

                <!-- Center: Empty - No System Title -->
                <div class="uda-nav-center">
                    <!-- Intentionally left empty for auth pages -->
                </div>

                <!-- Right: Empty on auth pages -->
                <div class="uda-nav-right">
                </div>
            </div>
        </div>

        <div class="container-fluid mob_menu_wrapper">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="mobile-logo">
                        @if (count($systemDetail) > 0 && $systemDetail[0]->logo_image !== null)
                            <a href="{{route('frontend.welcome')}}"><img loading="lazy"  src="{{asset('logo/'.$systemDetail[0]->logo_image)}}" style="width:200px; heigth:200px;" alt="logo"></a>
                        @else
                            <a href="{{route('frontend.welcome')}}"><img loading="lazy"  src="{{asset('img/cug_logo_new.jpeg')}}" style="width:200px; heigth:200px;" alt="logo"></a>
                        @endif
                    </div>
                </div>
                <div class="col-6">
                    <div class="header-right-wrap">
                        <div class="headerarea__right">
                        </div>
                        <div class="mobile-off-canvas">
                            <!-- Intentionally left empty for auth pages -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</header>
