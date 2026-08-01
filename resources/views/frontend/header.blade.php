<header class="uda-header-sticky">
    {{-- `header__sticky` removed: the theme JS used to bolt a fixed clone-style
         `.sticky` state onto it at 245px scroll (fadeInDown animation). The header
         is now PERMANENTLY pinned via position: sticky below — no scroll
         animation, no consolidation. --}}
    <div class="headerarea headerarea__2 header__area">
        <div class="uda-clock-bar" data-live-clock="navbar">
            <div class="clock-left">
                <span class="clock-item">
                    <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 21s-6-5.686-6-10a6 6 0 1 1 12 0c0 4.314-6 10-6 10Z"></path>
                        <circle cx="12" cy="11" r="2"></circle>
                    </svg>
                    Fiapre - Sunyani, Bono Region, Ghana
                </span>
                <span class="clock-item">
                    <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span class="live-date">Fetching date...</span>
                </span>
                <span class="clock-item clock-item-time">
                    <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span class="live-time">--:-- --</span>
                </span>
            </div>
            <div class="clock-right">
                <span class="clock-item">
                    <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.08 5.18 2 2 0 0 1 4.05 3h3a2 2 0 0 1 2 1.72 12.44 12.44 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 10.91a16 16 0 0 0 5 5l1.27-1.27a2 2 0 0 1 2.11-.45 12.44 12.44 0 0 0 2.81.7 2 2 0 0 1 1.72 2z"></path>
                    </svg>
                    Hotline: (+233) 352 094 658
                </span>
                <span class="clock-item">
                    <i class="icofont-brand-whatsapp" aria-hidden="true"></i>
                    WhatsApp: (+233) 249 260 857
                </span>
                <span class="clock-item">
                    <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <polyline points="3 7 12 13 21 7"></polyline>
                    </svg>
                    cugadmin@cug.edu.gh
                </span>
                <div class="clock-social">
                    <a href="https://cug.edu.gh" target="_blank" rel="noreferrer" aria-label="CUG Website">
                        <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M2 12h20"></path>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/p/Catholic-University-of-Ghanafiapre-100063596018619/" target="_blank" rel="noreferrer" aria-label="Facebook">
                        <svg class="lucide-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 2h-3a4 4 0 0 0-4 4v3H8v4h3v9h4v-9h3l1-4h-4V6a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="container desktop__menu__wrapper uda-header-container">
            <div class="uda-navbar">
                    <!-- Left: Logo -->
                    <div class="uda-nav-left">
                        @if (Auth::check())
                            @if (count($systemDetail) > 0 && $systemDetail[0]->logo_image !== null)
                                <a href="{{route('dashboard')}}"><img loading="lazy" src="{{asset('logo/'.$systemDetail[0]->logo_image)}}" class="uda-logo" alt="logo"></a>
                            @else
                                <a href="{{route('dashboard')}}"><img loading="lazy" src="{{asset('img/cug_logo_new.jpeg')}}" class="uda-logo" alt="logo"></a>
                            @endif
                        @else
                            @if (count($systemDetail) > 0 && $systemDetail[0]->logo_image !== null)
                                <a href="{{route('frontend.welcome')}}"><img loading="lazy" src="{{asset('logo/'.$systemDetail[0]->logo_image)}}" class="uda-logo" alt="logo"></a>
                            @else
                                <a href="{{route('frontend.welcome')}}"><img loading="lazy" src="{{asset('img/cug_logo_new.jpeg')}}" class="uda-logo" alt="logo"></a>
                            @endif
                        @endif
                    </div>

                    <!-- Center: Title Pill -->
                    <div class="uda-nav-center">
                        <div class="uda-title-pill">University Digital Transformation Suite (UDTS)</div>
                    </div>

                    <!-- Right: Auth Buttons & Notifications -->
                    <div class="uda-nav-right">
                        @if (Auth::check())
                            @php
                                // Drive the badge off `is_admin` (the field the app actually gates on),
                                // because the `role` column defaults to 'user' and registration never
                                // sets it — so it's unreliable for telling accounts apart.
                                // Invariant (see ROLE_TERMINOLOGY.md + role migration):
                                //   is_admin = 1  -> normal/regular user -> UI "User"
                                //   is_admin = 0  -> system administrator -> UI "Admin"
                                //   role 'super_admin' (set explicitly) -> "Super Admin"
                                if (Auth::user()->role === 'super_admin') {
                                    $roleBadge = ['label' => 'Super Admin', 'class' => 'role-badge--super'];
                                } elseif (Auth::user()->is_admin) {
                                    $roleBadge = ['label' => 'User', 'class' => 'role-badge--user'];
                                } else {
                                    $roleBadge = ['label' => 'Admin', 'class' => 'role-badge--admin'];
                                }
                            @endphp
                            <span class="role-badge {{ $roleBadge['class'] }}" title="Account type: {{ $roleBadge['label'] }}">
                                <span class="role-badge__dot"></span>
                                {{ $roleBadge['label'] }}
                            </span>
                            {{-- Search launcher — always visible (opens the Ctrl+K command palette) --}}
                            <button type="button" class="uds-trigger uds-trigger--header is-shown" data-udsl-open aria-label="Search everything" title="Search — Ctrl K">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                            </button>
                            @include('components.notification-tray')

                            {{-- Account menu — avatar trigger + dropdown (profile, settings, theme, sign out) --}}
                            <div class="uda-user-menu" data-uda-usermenu>
                                <button type="button" class="uda-user-trigger" aria-haspopup="true" aria-expanded="false" aria-label="Account menu">
                                    <img class="uda-user-trigger__avatar" src="{{ Auth::user()->profile_picture_url }}" alt="{{ Auth::user()->first_name }}">
                                    <svg class="uda-user-trigger__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div class="uda-user-dropdown" hidden>
                                    <div class="uda-user-dropdown__head">
                                        <img class="uda-user-dropdown__avatar" src="{{ Auth::user()->profile_picture_url }}" alt="">
                                        <div class="uda-user-dropdown__id">
                                            <span class="uda-user-dropdown__name">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</span>
                                            <span class="uda-user-dropdown__email">{{ Auth::user()->email }}</span>
                                            <span class="role-badge {{ $roleBadge['class'] }} uda-user-dropdown__role">
                                                <span class="role-badge__dot"></span>
                                                {{ $roleBadge['label'] }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="uda-user-dropdown__sep"></div>
                                    <nav class="uda-user-dropdown__nav">
                                        <a href="{{ route('dashboard.profile') }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            My Profile
                                        </a>
                                        <a href="{{ route('dashboard.settings') }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                            Settings
                                        </a>
                                        <button type="button" data-uda-theme-toggle>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                                            <span data-uda-theme-label>Dark mode</span>
                                        </button>
                                    </nav>
                                    <div class="uda-user-dropdown__sep"></div>
                                    <a class="uda-user-dropdown__logout" href="{{ route('logout') }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                        Sign out
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="{{route('frontend.login')}}" class="uda-btn uda-btn-primary">Register / Login</a>
                        @endif
                    </div>
                </div>
            </div>
            </div>
        </div>
        </div>


        <div class="container-fluid mob_menu_wrapper">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="mobile-logo">
                    @if (Auth::check())
                        @if (count($systemDetail) > 0 && $systemDetail[0]->logo_image !== null)
                        <a href="{{route('dashboard')}}"><img loading="lazy"  src="{{asset('logo/'.$systemDetail[0]->logo_image)}}" style="width:200px; heigth:200px;" alt="logo"></a>
                        @else
                            <a href="{{route('dashboard')}}"><img loading="lazy"  src="{{asset('img/cug_logo_new.jpeg')}}" style="width:200px; heigth:200px;" alt="logo"></a>

                        @endif

                    @else
                        @if (count($systemDetail) > 0 && $systemDetail[0]->logo_image !== null)
                            <a href="{{route('frontend.welcome')}}"><img loading="lazy"  src="{{asset('logo/'.$systemDetail[0]->logo_image)}}" style="width:200px; heigth:200px;" alt="logo"></a>
                        @else
                            <a href="{{route('frontend.welcome')}}"><img loading="lazy"  src="{{asset('img/cug_logo_new.jpeg')}}" style="width:200px; heigth:200px;" alt="logo"></a>

                        @endif
                    @endif
                    </div>
                </div>
                <div class="col-6">
                    <div class="header-right-wrap">

                        <div class="headerarea__right">

                            {{-- <div class="header__cart">
                                <a href="#"> <i class="icofont-cart-alt"></i></a>
                                <div class="header__right__dropdown__wrapper">
                                    <div class="header__right__dropdown__inner">
                                        <div class="single__header__right__dropdown">

                                            <div class="header__right__dropdown__img">
                                                <a href="#">
                                                    <img loading="lazy"  src="img/grid/cart1.jpg" alt="photo">
                                                </a>
                                            </div>
                                            <div class="header__right__dropdown__content">

                                                <a href="shop-product.html">Web Directory</a>
                                                <p>1 x <span class="price">$ 80.00</span></p>

                                            </div>
                                            <div class="header__right__dropdown__close">
                                                <a href="#"><i class="icofont-close-line"></i></a>
                                            </div>
                                        </div>

                                        <div class="single__header__right__dropdown">

                                            <div class="header__right__dropdown__img">
                                                <a href="#">
                                                    <img loading="lazy"  src="img/grid/cart2.jpg" alt="photo">
                                                </a>
                                            </div>
                                            <div class="header__right__dropdown__content">

                                                <a href="shop-product.html">Design Minois</a>
                                                <p>1 x <span class="price">$ 60.00</span></p>

                                            </div>
                                            <div class="header__right__dropdown__close">
                                                <a href="#"><i class="icofont-close-line"></i></a>
                                            </div>
                                        </div>

                                        <div class="single__header__right__dropdown">

                                            <div class="header__right__dropdown__img">
                                                <a href="#">
                                                    <img loading="lazy"  src="img/grid/cart3.jpg" alt="photo">
                                                </a>
                                            </div>
                                            <div class="header__right__dropdown__content">

                                                <a href="shop-product.html">Crash Course</a>
                                                <p>1 x <span class="price">$ 70.00</span></p>

                                            </div>
                                            <div class="header__right__dropdown__close">
                                                <a href="#"><i class="icofont-close-line"></i></a>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="dropdown__price">Total: <span>$1,100.00</span>
                                    </p>
                                    <div class="header__right__dropdown__button">
                                        <a href="#" class="white__color">VIEW
                                    CART</a>
                                        <a href="#" class="blue__color">CHECKOUT</a>
                                    </div>
                                </div>
                            </div> --}}
                        </div>

                        <div class="mobile-off-canvas">
                            {{-- <a class="mobile-aside-button" href="#"><i class="icofont-navigation-menu"></i></a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</header>


<style>
/* Old notification styles removed - now using notification-tray component */

/* ═══ Permanently pinned header (no scroll animation / consolidation) ═══
   Both rows — the utility clock bar and the main navbar — are always fixed
   to the top of the viewport on desktop. The page content scrolls beneath
   them. `--udts-header-h` is kept in sync by JS so the dashboard sidebar
   and other sticky panels can position themselves just below the header. */
@media (min-width: 992px) {
    header.uda-header-sticky {
        position: sticky;
        top: 0;
        z-index: 1030; /* above the dashboard sidebar (1020), below modals (1050) */
        background: var(--whiteColor, #fff);
        box-shadow: 0 1px 0 rgba(148, 163, 184, 0.16),
                    0 10px 30px -18px rgba(15, 23, 42, 0.25);
    }
    /* Anchor jumps land below the pinned header */
    html {
        scroll-padding-top: calc(var(--udts-header-h, 116px) + 12px);
    }
}

/* ═══ Account menu (avatar trigger + dropdown) ═══ */
.uda-user-menu {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.uda-user-trigger {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: none;
    border: none;
    padding: 3px;
    cursor: pointer;
    border-radius: 999px;
    transition: background .15s ease;
}
.uda-user-trigger:hover { background: rgba(100, 116, 139, 0.1); }
.uda-user-trigger__avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.12);
    transition: border-color .15s ease;
}
.uda-user-trigger:hover .uda-user-trigger__avatar,
.uda-user-trigger[aria-expanded="true"] .uda-user-trigger__avatar {
    border-color: #6366f1;
}
.uda-user-trigger__chevron {
    width: 14px;
    height: 14px;
    color: #64748b;
    transition: transform .18s ease;
}
.uda-user-trigger[aria-expanded="true"] .uda-user-trigger__chevron {
    transform: rotate(180deg);
}
.uda-user-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 288px;
    background: #fff;
    border: 1.5px solid #ebebeb;
    border-radius: 16px;
    box-shadow: 0 18px 50px -12px rgba(15, 23, 42, 0.25);
    padding: 8px;
    z-index: 1040;
    animation: uda-user-dropdown-in .16s ease;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    text-align: left;
}
@keyframes uda-user-dropdown-in {
    from { opacity: 0; transform: translateY(-6px) scale(.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.uda-user-dropdown__head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 12px 10px;
}
.uda-user-dropdown__avatar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #eef2f7;
    flex-shrink: 0;
}
.uda-user-dropdown__id {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}
.uda-user-dropdown__name {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.uda-user-dropdown__email {
    font-size: 12px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.uda-user-dropdown .uda-user-dropdown__role {
    margin: 3px 0 0;
    align-self: flex-start;
}
.uda-user-dropdown__sep {
    height: 1px;
    background: #f1f5f9;
    margin: 4px 8px;
}
.uda-user-dropdown__nav {
    display: flex;
    flex-direction: column;
}
.uda-user-dropdown__nav a,
.uda-user-dropdown__nav button,
.uda-user-dropdown__logout {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    padding: 9px 12px;
    border: none;
    background: none;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 500;
    color: #334155;
    text-decoration: none;
    cursor: pointer;
    transition: background .13s ease, color .13s ease;
    text-align: left;
}
.uda-user-dropdown__nav a svg,
.uda-user-dropdown__nav button svg,
.uda-user-dropdown__logout svg {
    width: 17px;
    height: 17px;
    color: #94a3b8;
    flex-shrink: 0;
    transition: color .13s ease;
}
.uda-user-dropdown__nav a:hover,
.uda-user-dropdown__nav button:hover {
    background: #f4f6fb;
    color: #0f172a;
}
.uda-user-dropdown__nav a:hover svg,
.uda-user-dropdown__nav button:hover svg {
    color: #6366f1;
}
.uda-user-dropdown__logout {
    color: #dc2626;
}
.uda-user-dropdown__logout svg { color: #dc2626; }
.uda-user-dropdown__logout:hover {
    background: #fef2f2;
    color: #b91c1c;
}

/* Dark mode */
.is_dark header.uda-header-sticky {
    box-shadow: 0 1px 0 rgba(148, 163, 184, 0.14),
                0 10px 30px -18px rgba(0, 0, 0, 0.6);
}
.is_dark .uda-user-trigger:hover { background: rgba(148, 163, 184, 0.12); }
.is_dark .uda-user-trigger__avatar { border-color: #2d3748; }
.is_dark .uda-user-dropdown {
    background: #111827;
    border-color: #1e2330;
    box-shadow: 0 18px 50px -12px rgba(0, 0, 0, 0.7);
}
.is_dark .uda-user-dropdown__name { color: #f3f4f6; }
.is_dark .uda-user-dropdown__sep { background: #1e2330; }
.is_dark .uda-user-dropdown__nav a,
.is_dark .uda-user-dropdown__nav button { color: #cbd5e1; }
.is_dark .uda-user-dropdown__nav a:hover,
.is_dark .uda-user-dropdown__nav button:hover {
    background: #1e2330;
    color: #f3f4f6;
}
.is_dark .uda-user-dropdown__logout { color: #f87171; }
.is_dark .uda-user-dropdown__logout svg { color: #f87171; }
.is_dark .uda-user-dropdown__logout:hover { background: rgba(220, 38, 38, 0.12); }

@media (max-width: 575px) {
    .uda-user-trigger__avatar { width: 36px; height: 36px; }
    .uda-user-dropdown { width: 260px; }
}

/* ===== ROLE BADGE (Admin / User / Super Admin) =====
   Inlined here (not in css/style.css) so the badge is styled the instant the
   header renders — same approach as the notification-tray component. The base
   rule below sets NO background/colour; those come only from the --admin /
   --user / --super modifiers. When this lived in the large, un-cache-busted
   style.css it would render as raw unstyled text whenever that file was stale
   or still loading, which is what made the badge intermittently show as text. */
.role-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 26px;
  padding: 0 11px;
  margin-right: 12px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: 0.2px;
  line-height: 1;
  white-space: nowrap;
  user-select: none;
  border: 1px solid transparent;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.role-badge__dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}
/* Admin (DB 'user') — light gold */
.role-badge--admin {
  background: #fbf3da;
  color: #92710f;
  border-color: #efdca5;
}
/* User (DB 'admin') — soft slate */
.role-badge--user {
  background: #eef1f6;
  color: #475569;
  border-color: #dde3ec;
}
/* Super Admin — soft violet */
.role-badge--super {
  background: #f3eefe;
  color: #6d28d9;
  border-color: #e4d9fb;
}
/* Dark header support */
.is_dark .role-badge--admin {
  background: rgba(202, 158, 30, 0.16);
  color: #e8c45f;
  border-color: rgba(202, 158, 30, 0.35);
}
.is_dark .role-badge--user {
  background: rgba(148, 163, 184, 0.16);
  color: #cbd5e1;
  border-color: rgba(148, 163, 184, 0.3);
}
.is_dark .role-badge--super {
  background: rgba(124, 58, 237, 0.18);
  color: #c4b5fd;
  border-color: rgba(124, 58, 237, 0.4);
}
@media (max-width: 575px) {
  .role-badge {
    margin-right: 8px;
    padding: 0 9px;
    height: 24px;
    font-size: 11px;
  }
}

.uda-clock-bar {
    width: 100%;
    background: linear-gradient(90deg, #fbfcf9 0%, #fffaf4 50%, #fff8ef 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 24px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.25);
    box-shadow: inset 0 -1px 0 rgba(148, 163, 184, 0.15);
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    z-index: 20;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.uda-clock-bar .clock-left,
.uda-clock-bar .clock-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: nowrap;
    flex-shrink: 0;
}

.uda-clock-bar .clock-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: #1f2937;
    white-space: nowrap;
    flex-shrink: 0;
}

.uda-clock-bar .clock-item .lucide-icon,
.uda-clock-bar .clock-social .lucide-icon {
    width: 15px;
    height: 15px;
    margin-right: 5px;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
    flex-shrink: 0;
}

.uda-clock-bar .clock-social .lucide-icon {
    margin-right: 0;
}

.uda-clock-bar .clock-item i {
    font-size: 15px;
    margin-right: 5px;
    color: inherit;
    flex-shrink: 0;
}

.uda-clock-bar .clock-item-time {
    color: #1d4ed8;
}

.uda-clock-bar .clock-social {
    display: inline-flex;
    gap: 6px;
    flex-shrink: 0;
}

.uda-clock-bar .clock-social a {
    width: 26px;
    height: 26px;
    border-radius: 999px;
    background: rgba(99, 102, 241, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #374151;
    text-decoration: none;
    flex-shrink: 0;
}

.uda-clock-bar .clock-social a:hover {
    background: rgba(99, 102, 241, 0.3);
}

/* Medium desktop screens - ensure no wrapping */
@media (min-width: 993px) and (max-width: 1400px) {
    .uda-clock-bar {
        padding: 6px 16px;
        font-size: 12px;
    }
    
    .uda-clock-bar .clock-left,
    .uda-clock-bar .clock-right {
        gap: 10px;
    }
    
    .uda-clock-bar .clock-item {
        gap: 4px;
    }
    
    .uda-clock-bar .clock-item .lucide-icon,
    .uda-clock-bar .clock-item i {
        width: 14px;
        height: 14px;
        font-size: 14px;
    }
    
    /* Ensure clock-right doesn't overflow */
    .uda-clock-bar .clock-right {
        flex-shrink: 1;
        min-width: 0;
    }
    
    .uda-clock-bar .clock-left {
        flex-shrink: 1;
        min-width: 0;
    }
}

/* Tablet and below - allow wrapping */
@media (max-width: 992px) {
    .uda-clock-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 16px;
    }
    
    .uda-clock-bar .clock-left,
    .uda-clock-bar .clock-right {
        flex-wrap: wrap;
        width: 100%;
        gap: 10px;
    }
    
    .uda-clock-bar .clock-right {
        justify-content: space-between;
    }
}
</style>

<script>
// Notification sound and polling for unread count
let udaBellAudio;
function initBellAudio(){
  try{
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    udaBellAudio = function(){
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain); gain.connect(ctx.destination);
      osc.type = 'triangle'; osc.frequency.value = 880;
      gain.gain.setValueAtTime(0, ctx.currentTime);
      gain.gain.linearRampToValueAtTime(0.2, ctx.currentTime + 0.01);
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.6);
      osc.start(); osc.stop(ctx.currentTime + 0.6);
      setTimeout(()=>{
        const o2 = ctx.createOscillator(); const g2 = ctx.createGain();
        o2.connect(g2); g2.connect(ctx.destination);
        o2.type='sine'; o2.frequency.value=1320;
        g2.gain.setValueAtTime(0, ctx.currentTime);
        g2.gain.linearRampToValueAtTime(0.15, ctx.currentTime + 0.01);
        g2.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.4);
        o2.start(); o2.stop(ctx.currentTime + 0.4);
      }, 120);
    }
  }catch(e){ udaBellAudio = function(){}; }
}

let lastUnread = Number({{ $newMessagesCount ?? 0 }});
let lastReplyNotifications = Number({{ $newReplyNotifications ?? 0 }});
let unreadPollingEnabled = true;

let lastBadgeTotal = Number({{ ($newMessagesCount ?? 0) + ($newReplyNotifications ?? 0) }});

function updateNotificationBadge(unreadCount, replyCount = 0) {
  const badge   = document.querySelector('.notification-badge');
  const ping    = document.querySelector('.notification-badge-ping');
  const trigger = document.querySelector('.notification-tray-trigger');
  if (!badge || !trigger) return;

  const totalCount = Number(unreadCount || 0) + Number(replyCount || 0);
  const display    = totalCount > 99 ? '99+' : String(totalCount);

  if (totalCount > 0) {
    badge.textContent = display;
    badge.style.display = 'inline-block';
    badge.classList.toggle('is-single', totalCount < 10);
    badge.classList.toggle('is-multi',  totalCount >= 10);
    if (ping) ping.style.display = 'block';
    trigger.classList.add('has-unread');
    trigger.setAttribute(
      'aria-label',
      totalCount + ' unread notification' + (totalCount === 1 ? '' : 's')
    );

    // Count increased → ring the bell + pop the badge (matches the audio cue).
    if (totalCount > lastBadgeTotal) {
      trigger.classList.remove('ringing');
      badge.classList.remove('popping');
      // Force reflow so the animation can replay even if the class was just removed.
      void trigger.offsetWidth;
      trigger.classList.add('ringing');
      badge.classList.add('popping');
      setTimeout(() => {
        trigger.classList.remove('ringing');
        badge.classList.remove('popping');
      }, 950);
    }
  } else {
    badge.style.display = 'none';
    if (ping) ping.style.display = 'none';
    trigger.classList.remove('has-unread');
    trigger.setAttribute('aria-label', 'Notifications, no unread');
  }

  lastBadgeTotal = totalCount;

  // Broadcast the new count so the favicon dot + page-title flash component
  // (and any other listener) can react without polling the DOM.
  window.dispatchEvent(new CustomEvent('notification:count', { detail: { total: totalCount } }));
}

function fetchJsonSafe(url, options = {}) {
  return fetch(url, options).then(async (response) => {
    const contentType = response.headers.get('content-type') || '';
    const bodyText = await response.text();

    if (!response.ok) {
      const err = new Error(`HTTP ${response.status}`);
      err.status = response.status;
      err.bodyText = bodyText;
      throw err;
    }

    if (!contentType.includes('application/json')) {
      const err = new Error('Expected JSON response but received non-JSON payload');
      err.status = response.status;
      err.bodyText = bodyText;
      throw err;
    }

    try {
      return JSON.parse(bodyText);
    } catch (parseError) {
      const err = new Error('Invalid JSON response payload');
      err.status = response.status;
      err.bodyText = bodyText;
      throw err;
    }
  });
}

function pollUnread(){
  if (!unreadPollingEnabled) return;

  // Poll for memos
  fetchJsonSafe('{{ route('dashboard.memos.unreadCount') }}', {
    credentials: 'same-origin',
    cache: 'no-cache',
    headers: {
      'Cache-Control': 'no-cache',
      'Pragma': 'no-cache'
    }
  })
    .then(data => {
      const unread = Number(data.unread || 0);
      
      // Poll for reply notifications
      fetchJsonSafe('/dashboard/notifications/check', {
        credentials: 'same-origin',
        cache: 'no-cache',
        headers: {
          'Cache-Control': 'no-cache',
          'Pragma': 'no-cache'
        }
      })
      .then(notificationData => {
        const replyCount = notificationData.reply_count || 0;
        updateNotificationBadge(unread, replyCount);
        
        if ((unread > lastUnread || replyCount > lastReplyNotifications) && typeof udaBellAudio === 'function') {
          udaBellAudio();
        }
        lastUnread = unread;
        lastReplyNotifications = replyCount;
        
        // Refresh the notification tray if open
        if (typeof refreshNotificationTray === 'function') {
          refreshNotificationTray();
        }
      })
      .catch(err => {
        console.log('Error polling reply notifications:', err);
        updateNotificationBadge(unread);
        lastUnread = unread;
        refreshMemoList();
      });
    })
    .catch(err => {
      if (err && (err.status === 401 || err.status === 403 || err.status === 419)) {
        unreadPollingEnabled = false;
      }
      console.log('Error polling unread count:', err);
    });
}

function refreshMemoList(){
  // Refresh notification tray if it's open
  if (typeof refreshNotificationTray === 'function') {
    refreshNotificationTray();
  }
}

const liveClockDateFormatter = new Intl.DateTimeFormat(undefined, {
  weekday: 'long',
  month: 'short',
  day: 'numeric',
  year: 'numeric'
});

const liveClockTimeFormatter = new Intl.DateTimeFormat(undefined, {
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit'
});

function updateLiveDateTimeWidgets() {
  const now = new Date();
  const dateText = liveClockDateFormatter.format(now);
  const timeText = liveClockTimeFormatter.format(now);
  
  document.querySelectorAll('[data-live-clock]').forEach(clock => {
    const dateEl = clock.querySelector('.live-date');
    const timeEl = clock.querySelector('.live-time');
    
    if (dateEl) {
      dateEl.textContent = dateText;
    }
    if (timeEl) {
      timeEl.textContent = timeText;
    }
  });
}

function startLiveDateTimeTicker() {
  updateLiveDateTimeWidgets();
  if (window.__liveClockInterval) {
    clearInterval(window.__liveClockInterval);
  }
  window.__liveClockInterval = setInterval(updateLiveDateTimeWidgets, 1000);
}

// Mark all as read is now handled in the notification-tray component

document.addEventListener('DOMContentLoaded', function(){
  initBellAudio();
  startLiveDateTimeTicker();
  
  // Poll immediately on page load
  setTimeout(pollUnread, 100);
  
  // Poll frequently for better responsiveness
  setInterval(pollUnread, 2000);
  
  // Poll when page becomes visible
  document.addEventListener('visibilitychange', function(){
    if (!document.hidden) {
      setTimeout(pollUnread, 50);
    }
  });
  
  // Poll when returning from a memo view - more aggressive
  if (document.referrer && document.referrer.includes('/dashboard/memos/')) {
    setTimeout(pollUnread, 50);
    setTimeout(pollUnread, 500);
    setTimeout(pollUnread, 1000);
  }
  
  // Poll when window regains focus
  window.addEventListener('focus', function(){
    setTimeout(pollUnread, 50);
  });
  
  // Poll when coming back from navigation
  window.addEventListener('pageshow', function(event) {
    setTimeout(pollUnread, 50);
  });
  
  // Poll when user clicks anywhere (indicating activity)
  let lastClickTime = 0;
  document.addEventListener('click', function(){
    const now = Date.now();
    if (now - lastClickTime > 1000) { // Throttle to once per second
      lastClickTime = now;
      setTimeout(pollUnread, 100);
    }
  });
});

// ═══ Pinned header: publish the real header height as --udts-header-h ═══
// The dashboard sidebar and other sticky panels read this variable to position
// themselves right below the permanently pinned header.
function udtsSyncHeaderHeight(){
  const el = document.querySelector('header.uda-header-sticky');
  if (!el) return;
  document.documentElement.style.setProperty('--udts-header-h', el.offsetHeight + 'px');
}
document.addEventListener('DOMContentLoaded', function(){
  udtsSyncHeaderHeight();
  // Re-measure once fonts/images have settled
  setTimeout(udtsSyncHeaderHeight, 350);
  window.addEventListener('resize', udtsSyncHeaderHeight);
});

// ═══ Account menu (avatar dropdown) ═══
document.addEventListener('DOMContentLoaded', function(){
  const menu = document.querySelector('[data-uda-usermenu]');
  if (!menu) return;
  const trigger  = menu.querySelector('.uda-user-trigger');
  const dropdown = menu.querySelector('.uda-user-dropdown');

  function setOpen(open){
    dropdown.hidden = !open;
    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  trigger.addEventListener('click', function(e){
    e.stopPropagation();
    setOpen(dropdown.hidden);
  });
  document.addEventListener('click', function(e){
    if (!dropdown.hidden && !menu.contains(e.target)) setOpen(false);
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && !dropdown.hidden) { setOpen(false); trigger.focus(); }
  });

  // Dark / light mode toggle — same mechanism as the head script in layout.app
  // (html.is_dark class + localStorage "theme-color")
  const themeBtn   = menu.querySelector('[data-uda-theme-toggle]');
  const themeLabel = menu.querySelector('[data-uda-theme-label]');
  function syncThemeLabel(){
    if (themeLabel) {
      themeLabel.textContent = document.documentElement.classList.contains('is_dark') ? 'Light mode' : 'Dark mode';
    }
  }
  syncThemeLabel();
  if (themeBtn) {
    themeBtn.addEventListener('click', function(){
      const nowDark = document.documentElement.classList.toggle('is_dark');
      try { localStorage.setItem('theme-color', nowDark ? 'dark' : 'light'); } catch (e) {}
      syncThemeLabel();
    });
  }
});
</script>

