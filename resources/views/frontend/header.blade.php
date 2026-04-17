<header>
    <div class="headerarea headerarea__2 header__sticky header__area">
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
                            @include('components.notification-tray')
                            <a href="{{route('logout')}}" class="uda-btn uda-btn-primary">Logout</a>
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

function updateNotificationBadge(unreadCount, replyCount = 0) {
  const badge = document.querySelector('.notification-badge');
  if (badge) {
    const totalCount = unreadCount + replyCount;
    if (totalCount > 0) {
      badge.textContent = totalCount;
      badge.style.display = 'inline-block';
    } else {
      badge.style.display = 'none';
    }
  }
}

function pollUnread(){
  // Poll for memos
  fetch('{{ route('dashboard.memos.unreadCount') }}', {
    credentials: 'same-origin',
    cache: 'no-cache',
    headers: {
      'Cache-Control': 'no-cache',
      'Pragma': 'no-cache'
    }
  })
    .then(r => r.json())
    .then(data => {
      const unread = Number(data.unread || 0);
      
      // Poll for reply notifications
      fetch('/dashboard/notifications/check', {
        credentials: 'same-origin',
        cache: 'no-cache',
        headers: {
          'Cache-Control': 'no-cache',
          'Pragma': 'no-cache'
        }
      })
      .then(r => r.json())
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
</script>

