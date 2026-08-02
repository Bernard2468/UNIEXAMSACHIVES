<div class="col-xl-3 col-lg-3 col-md-12 app-sidebar" id="appSidebarDrawer">
    <div class="dashboard__inner sticky-top">
        {{-- Brand — shown at the top of the sidebar on MOBILE only (the header logo
             is hidden there to free up room). On tablet/desktop the logo stays in
             the header, so this is hidden to avoid duplicate branding. --}}
        <a href="{{ route('dashboard') }}" class="app-sidebar-brand" aria-label="Home">
            @if (isset($systemDetail) && count($systemDetail) > 0 && $systemDetail[0]->logo_image !== null)
                <img src="{{ asset('logo/'.$systemDetail[0]->logo_image) }}" alt="Catholic University of Ghana">
            @else
                <img src="{{ asset('img/cug_logo_new.jpeg') }}" alt="Catholic University of Ghana">
            @endif
        </a>
        <div class="sidebar-section-header welcome-header">
            <div class="section-header-content">
                <div class="section-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="section-text">
                    <h6 class="section-title">Welcome, {{auth()->user()->first_name}} {{auth()->user()->last_name}}</h6>
                    <span class="section-subtitle">Dashboard Overview</span>
                </div>
                <div class="section-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
        <div class="dashboard__nav">
            <ul>
                <li>
                    <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{route('dashboard')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762872840/dashboard_r0by47.png" alt="Dashboard" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        Dashboard</a>
                </li>
                <li>
                    <a class="{{ request()->routeIs('dashboard.profile') ? 'active' : '' }}" href="{{route('dashboard.profile')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762873945/profile_1_srj1hi.png" alt="My Profile" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        My Profile</a>
                </li>
                <li>
                    <a class="{{ request()->routeIs('dashboard.document') ? 'active' : '' }}" href="{{route('dashboard.document')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762873294/folder_smk8rg.png" alt="All Documents" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        All Documents</a>
                </li>

            </ul>
        </div>

        {{-- Internal Memo Management System (Users Only) --}}
        @auth
            @unless(auth()->user()->is_admin)
                <div class="sidebar-section-header">
                    <div class="section-header-content">
                        <div class="section-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
                                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"></path>
                            </svg>
                        </div>
                        <div class="section-text">
                            <h6 class="section-title">INTERNAL MEMO MANAGEMENT SYSTEM</h6>
                            <span class="section-subtitle">4 features</span>
                        </div>
                        <div class="section-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="dashboard__nav">
                    <ul>
                        <li>
                            <a class="{{ request()->routeIs('admin.communication.create') ? 'active' : '' }}" href="{{route('admin.communication.create')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762941334/bc12957e-52a0-4a05-8ee8-02bb753d6b58.png" alt="Compose Memo" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Compose Memo</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.uimms.*') && !request()->routeIs('dashboard.uimms.keep-in-view') ? 'active' : '' }}" href="{{route('dashboard.uimms.portal')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762943555/0f798328-ccf6-4f51-91b5-13873791d869.png" alt="Memos Portal" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Memos Portal
                            </a><span class="dashboard__label">{{ $unreadMemosCount ?? $newMessagesCount ?? 0 }}</span>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.uimms.keep-in-view') ? 'active' : '' }}" href="{{route('dashboard.uimms.keep-in-view')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762872342/image_pgg76v.png" alt="Keep in View" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Keep in View
                            </a><span class="dashboard__label">{{ $bookmarkedCount ?? 0 }}</span>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.communication.index') ? 'active' : '' }}" href="{{route('admin.communication.index')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762940231/message_uzbtkd.png" alt="Memos" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Memos</a>
                        </li>
                    </ul>
                </div>
            @endunless
        @endauth

        {{-- Internal Memo Management System (Admin Only) --}}
        @auth
            @if(auth()->user()->is_admin)
                <div class="sidebar-section-header">
                    <div class="section-header-content">
                        <div class="section-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
                                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"></path>
                            </svg>
                        </div>
                        <div class="section-text">
                            <h6 class="section-title">INTERNAL MEMO MANAGEMENT SYSTEM</h6>
                            <span class="section-subtitle">4 features</span>
                        </div>
                        <div class="section-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="dashboard__nav">
                    <ul>
                        <li>
                            <a class="{{ request()->routeIs('admin.communication-admin.create') ? 'active' : '' }}" href="{{route('admin.communication-admin.create')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762941334/bc12957e-52a0-4a05-8ee8-02bb753d6b58.png" alt="Compose Memo" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Compose Memo</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.uimms.*') && !request()->routeIs('dashboard.uimms.keep-in-view') ? 'active' : '' }}" href="{{route('dashboard.uimms.portal')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762943555/0f798328-ccf6-4f51-91b5-13873791d869.png" alt="Memos Portal" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Memos Portal
                            </a><span class="dashboard__label">{{ $unreadMemosCount ?? $newMessagesCount ?? 0 }}</span>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.uimms.keep-in-view') ? 'active' : '' }}" href="{{route('dashboard.uimms.keep-in-view')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762872342/image_pgg76v.png" alt="Keep in View" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Keep in View
                            </a><span class="dashboard__label">{{ $bookmarkedCount ?? 0 }}</span>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('admin.communication-admin.index') ? 'active' : '' }}" href="{{route('admin.communication-admin.index')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762940231/message_uzbtkd.png" alt="Memos" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Memos</a>
                        </li>
                    </ul>
                </div>
            @endif
        @endauth

        {{-- ===== FORMS WORKFLOW (Purchase/Works Authorization, Payment Requisition, etc.) ===== --}}
        @auth
            <div class="sidebar-section-header">
                <div class="section-header-content">
                    <div class="section-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                    </div>
                    <div class="section-text">
                        <h6 class="section-title">FORMS</h6>
                        <span class="section-subtitle">Workflow &amp; e-signing</span>
                    </div>
                    <div class="section-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                            <polyline points="6,9 12,15 18,9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="dashboard__nav">
                <ul>
                    <li>
                        <a class="{{ request()->routeIs('admin.forms.gallery') ? 'active' : '' }}" href="{{ route('admin.forms.gallery') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                            All Forms
                        </a>
                    </li>
                    <li>
                        <a class="{{ request()->routeIs('admin.forms.portal') ? 'active' : '' }}" href="{{ route('admin.forms.portal') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Forms Portal
                        </a>
                        @if(($awaitingFormsCount ?? 0) > 0)
                            <span class="dashboard__label">{{ $awaitingFormsCount }}</span>
                        @endif
                    </li>
                </ul>
            </div>
        @endauth

        {{-- Exams --}}
        <div class="sidebar-section-header">
            <div class="section-header-content">
                <div class="section-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"></path>
                    </svg>
                </div>
                <div class="section-text">
                    <h6 class="section-title">EXAMS CLASS PORTFOLIO</h6>
                    <span class="section-subtitle">3 categories</span>
                </div>
                <div class="section-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
        <div class="dashboard__nav">
            <ul>
                @auth
                @if(auth()->user()->is_admin)
                <li>
                    <a class="{{ request()->routeIs('dashboard.all.exams') ? 'active' : '' }}" href="{{route('dashboard.all.exams')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762892670/exam_esftn0.png" alt="My Exams" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        My Exams</a><span class="dashboard__label">{{$myExamsCount}}</span>
                </li>
                @endif
                @endauth
                @auth
                    @unless(auth()->user()->is_admin)
                        <li>
                            <a class="{{ request()->routeIs('dashboard.all.exams') ? 'active' : '' }}" href="{{route('dashboard.all.exams')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762892811/exam_1_jho0sq.png" alt="All Exams" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                All Exams
                            </a><span class="dashboard__label">{{$allExamsCount}}</span>
                        </li>
                    @endunless
                @endauth

            </ul>
        </div>

        {{-- File --}}
        <div class="sidebar-section-header">
            <div class="section-header-content">
                <div class="section-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"></path>
                    </svg>
                </div>
                <div class="section-text">
                    <h6 class="section-title">FILES CLASS PORTFOLIO</h6>
                    <span class="section-subtitle">3 categories</span>
                </div>
                <div class="section-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
        <div class="dashboard__nav">
            <ul>
                @auth
                @if(auth()->user()->is_admin)
                <li>
                    <a class="{{ request()->routeIs('dashboard.all.files') ? 'active' : '' }}" href="{{route('dashboard.all.files')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939971/approved_jjmla9.png" alt="My Files" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        My Files</a><span class="dashboard__label">{{$myFilesCount}}</span>
                </li>
                    <li>
                        <a class="{{ request()->routeIs('dashboard.folders.*') ? 'active' : '' }}" href="{{route('dashboard.folders.index')}}">
                            <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939707/folder_vta5tl.png" alt="My Folders" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                            My Folders</a>
                    </li>
                    <li>
                        <a class="{{ request()->routeIs('dashboard.departmental-folders') ? 'active' : '' }}" href="{{route('dashboard.departmental-folders')}}">
                            <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939707/folder_vta5tl.png" alt="Departmental Folders" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                            Departmental Folders</a>
                    </li>
                @endif
                @endauth
                @auth
                    @unless(auth()->user()->is_admin)
                        <li>
                            <a class="{{ request()->routeIs('dashboard.all.files') ? 'active' : '' }}" href="{{route('dashboard.all.files')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939473/file_k1pnab.png" alt="All Files" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                All Files
                            </a><span class="dashboard__label">{{$allFilesCount}}</span>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.folders.*') ? 'active' : '' }}" href="{{route('dashboard.folders.index')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939707/folder_vta5tl.png" alt="My Folders" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                My Folders</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.departmental-folders') ? 'active' : '' }}" href="{{route('dashboard.departmental-folders')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939707/folder_vta5tl.png" alt="Departmental Folders" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Departmental Folders</a>
                        </li>
                    @endunless
                @endauth

            </ul>
        </div>

        {{-- Committees & Boards --}}
        @auth
            @unless(auth()->user()->is_admin)
                {{-- Manage Committees & Boards (For users who can access Manage Users, Departments, etc.) --}}
                <div class="sidebar-section-header">
                    <div class="section-header-content">
                        <div class="section-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="section-text">
                            <h6 class="section-title">COMMITTEES & BOARDS MANAGEMENT SYSTEM (CBMS)</h6>
                            <span class="section-subtitle">Manage committees</span>
                        </div>
                        <div class="section-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="dashboard__nav">
                    <ul>
                        <li>
                            <a class="{{ request()->routeIs('committees.index') || request()->routeIs('committees.create') || request()->routeIs('committees.edit') ? 'active' : '' }}" href="{{route('committees.index')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                Manage Committees & Boards</a>
                        </li>
                    </ul>
                </div>
            @else
                {{-- My Committees & Boards (For normal users who cannot manage) --}}
                <div class="sidebar-section-header">
                    <div class="section-header-content">
                        <div class="section-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="section-text">
                            <h6 class="section-title">COMMITTEES AND BOARDS MANAGEMENT SYSTEM (CBMS)</h6>
                            <span class="section-subtitle">View your committees</span>
                        </div>
                        <div class="section-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="dashboard__nav">
                    <ul>
                        <li>
                            <a class="{{ request()->routeIs('committees.my-committees') ? 'active' : '' }}" href="{{route('committees.my-committees')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                My Committees & Boards</a>
                        </li>
                    </ul>
                </div>
            @endunless
        @endauth

        {{-- Users --}}
        <div class="sidebar-section-header">
            <div class="section-header-content">
                <div class="section-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
                        <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"></path>
                    </svg>
                </div>
                <div class="section-text">
                    <h6 class="section-title">MANAGEMENT</h6>
                    <!--<span class="section-subtitle">4 features</span>-->
                </div>
                <div class="section-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </div>
            </div>
        </div>

        <div class="dashboard__nav">
            <ul>
                @auth
                    @unless(auth()->user()->is_admin)
                        <li>
                            <a class="{{ request()->routeIs('dashboard.users') ? 'active' : '' }}" href="{{route('dashboard.users')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762942427/776ed950-3ea3-4bdb-97e6-8ade766c6ebd.png" alt="Manage Users" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Manage Users</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('departments.index') ? 'active' : '' }}" href="{{route('departments.index')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762944351/ce210acd-b07b-4e1b-a70f-6fdb86586806.png" alt="Department/Faculty" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                Department/Faculty</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('positions.index') ? 'active' : '' }}" href="{{route('positions.index')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762944556/48e4d7be-3f74-4aa9-9b87-8a7b435683d5.png" alt="Positions" style="width: 22px; height: 22px; object-fit: contain; margin-right: 10px;">
                                Positions</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('offices.*') ? 'active' : '' }}" href="{{route('offices.index')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                                    <path d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M9 18v.01M13 9v.01M13 12v.01M13 15v.01M13 18v.01"></path>
                                </svg>
                                Offices</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.system-letterheads.*') ? 'active' : '' }}" href="{{route('dashboard.system-letterheads.index')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1778686904/letterhead_d4ebcs.png" alt="System Letterheads" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                System Letterheads</a>
                        </li>
                        <li>
                            <a class="{{ request()->routeIs('dashboard.payment-history.*') ? 'active' : '' }}" href="{{route('dashboard.payment-history.index')}}">
                                <img src="https://img.icons8.com/plasticine/100/bank-card-back-side.png" alt="Payment History" style="width: 22px; height: 22px; object-fit: contain; margin-right: 10px;">
                                Payment History</a>
                        </li>
                    @endunless
                @endauth
                <li>
                    <a class="{{ request()->routeIs('dashboard.system-licences') ? 'active' : '' }}" href="{{route('dashboard.system-licences')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1782383799/da144d46-cd88-47d0-9ed9-d27de276284c.png" alt="System Licences" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        System Licences</a>
                </li>
                @auth
                    @unless(auth()->user()->is_admin)
                        <li>
                            <a class="{{ request()->routeIs('dashboard.system-documentation.manage') ? 'active' : '' }}" href="{{route('dashboard.system-documentation.manage')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1782383590/48f0d0a9-ec89-4e3f-8b33-0397182dea63.png" alt="University Policies" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                University Policies</a>
                        </li>
                    @else
                        <li>
                            <a class="{{ request()->routeIs('dashboard.system-documentation') ? 'active' : '' }}" href="{{route('dashboard.system-documentation')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1782383590/48f0d0a9-ec89-4e3f-8b33-0397182dea63.png" alt="University Policies" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                University Policies</a>
                        </li>
                    @endunless

                    @unless(auth()->user()->is_admin)
                        <li>
                            <a class="{{ request()->routeIs('dashboard.user-manual.manage') ? 'active' : '' }}" href="{{route('dashboard.user-manual.manage')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1782383352/4f8ebee8-0fa6-4fa2-9284-3b5d80c24cb1.png" alt="User Manual" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                User Manual</a>
                        </li>
                    @else
                        <li>
                            <a class="{{ request()->routeIs('dashboard.user-manual') ? 'active' : '' }}" href="{{route('dashboard.user-manual')}}">
                                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1782383352/4f8ebee8-0fa6-4fa2-9284-3b5d80c24cb1.png" alt="User Manual" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                                User Manual</a>
                        </li>
                    @endunless
                @endauth
                <li>
                    <a class="{{ request()->routeIs('dashboard.settings') ? 'active' : '' }}" href="{{route('dashboard.settings')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762941932/2d648212-6d23-4431-beb3-a679d2a6dc43.png" alt="Settings" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        Settings</a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                    <a href="{{route('logout')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762942345/d8fc56f0-cf0d-4ba9-b441-8d19dc1623d3.png" alt="Logout" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        Logout</a>
                    </form>
                </li>

            </ul>
        </div>


    </div>
</div>

{{-- Off-canvas drawer backdrop (mobile/tablet <992px only) --}}
<div class="app-sidebar-backdrop" id="appSidebarBackdrop" aria-hidden="true"></div>

<!-- Logo Modal -->
<div class="modal fade" id="myLogoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Logo and System Name</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- content --}}
                <form action="{{route('dashboard.details.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="col-xl-12">
                        <div class="dashboard__form__wraper">
                            <div class="dashboard__form__input">
                                <label >System Title</label>
                                <input type="text" placeholder="Enter Title" name="title">
                            </div>
                            <div class="dashboard__form__input">
                                <label >Logo</label>
                                <input type="file" placeholder="Choose a file" name="logo_image">
                            </div>
                            <button type="submit" class="btn btn-primary">Save changes</button>

                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Department Modal -->
<div class="modal fade" id="myDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="dm-modal">
            <div class="dm-modal__hd">
                <div>
                    <h5 class="dm-modal__title">Add department / faculty</h5>
                    <p class="dm-modal__sub">Enter the name of the department, faculty, or unit.</p>
                </div>
                <button type="button" class="dm-modal__close" data-bs-dismiss="modal">
                    <svg width="15" height="15" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                </button>
            </div>
            <div class="dm-modal__body">
                <form action="{{route('departments.store')}}" method="POST">
                    @csrf
                    <div class="dm-modal__field">
                        <label class="dm-modal__label">Department name</label>
                        <input class="dm-modal__input" type="text" name="name" placeholder="e.g. Faculty of Engineering" required autofocus>
                    </div>
                    <div class="dm-modal__foot">
                        <button type="button" class="dm-modal__btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="dm-modal__btn-save">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Save department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Academic Year Modal -->
<div class="modal fade" id="myAcademicModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Academic Year</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- content --}}
                <form action="{{route('dashboard.academic.store')}}" method="POST">
                    @csrf
                    <div class="col-xl-12">
                        <div class="dashboard__form__wraper">
                            <div class="dashboard__form__input">
                                <label >Academic Year</label>
                                <input type="text" placeholder="Enter Academic Year" name="year" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Save changes</button>

                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all sidebar section headers
    const sectionHeaders = document.querySelectorAll('.sidebar-section-header');
    
    sectionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Find the next navigation section
            const nextNav = this.nextElementSibling;
            
            if (nextNav && nextNav.classList.contains('dashboard__nav')) {
                // Toggle visibility
                if (nextNav.style.display === 'none') {
                    nextNav.style.display = 'block';
                    this.classList.remove('collapsed');
                } else {
                    nextNav.style.display = 'none';
                    this.classList.add('collapsed');
                }
            }
        });
        
        // Add initial state - all sections expanded by default
        const nextNav = header.nextElementSibling;
        if (nextNav && nextNav.classList.contains('dashboard__nav')) {
            nextNav.style.display = 'block';
        }
    });
    
    // Add smooth animations
    const navSections = document.querySelectorAll('.dashboard__nav');
    navSections.forEach(nav => {
        nav.style.transition = 'all 0.3s ease-in-out';
    });

    // Persist the sidebar's own scroll position across page navigations so it
    // doesn't jump back to the top on every click (matches top-app behaviour).
    const sidebarScroller = document.querySelector('.dashboard__inner.sticky-top');
    if (sidebarScroller) {
        const saved = sessionStorage.getItem('udts:sidebarScrollTop');
        if (saved !== null) {
            // Bypass CSS smooth-scroll for the instant restore
            sidebarScroller.style.scrollBehavior = 'auto';
            sidebarScroller.scrollTop = parseInt(saved, 10) || 0;
            sidebarScroller.style.scrollBehavior = '';
        } else {
            // First visit this session: bring the active menu item into view
            const activeLink = sidebarScroller.querySelector('.dashboard__nav a.active');
            if (activeLink) {
                const linkTop = activeLink.getBoundingClientRect().top - sidebarScroller.getBoundingClientRect().top;
                if (linkTop > sidebarScroller.clientHeight - 60) {
                    sidebarScroller.style.scrollBehavior = 'auto';
                    sidebarScroller.scrollTop = linkTop - (sidebarScroller.clientHeight / 2);
                    sidebarScroller.style.scrollBehavior = '';
                }
            }
        }
        let saveTimer = null;
        sidebarScroller.addEventListener('scroll', function() {
            clearTimeout(saveTimer);
            saveTimer = setTimeout(function() {
                sessionStorage.setItem('udts:sidebarScrollTop', String(sidebarScroller.scrollTop));
            }, 100);
        }, { passive: true });
    }
});

// Dropdown functionality removed - using direct link now
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.dashboard__inner,
.dashboard__inner * {
    font-family: 'Outfit', sans-serif !important;
}

/* ── Independent sidebar scrolling ──
   The sidebar is pinned to the viewport and scrolls on its own, completely
   decoupled from the page scroll. Its scrollbar is ultra-thin and nearly
   invisible until the cursor is over the sidebar (Slack / Notion / Linear
   pattern). Applies only on desktop widths where the sidebar is a side
   column — on mobile it stacks above the content and flows naturally. */
@media (min-width: 992px) {
    .dashboard__inner.sticky-top {
        /* Sit just below the permanently pinned header (height published as
           --udts-header-h by frontend/header.blade.php) */
        top: calc(var(--udts-header-h, 116px) + 16px);
        max-height: calc(100vh - var(--udts-header-h, 116px) - 32px);
        overflow-y: auto;
        overflow-x: hidden;
        /* Reaching the sidebar's end must never chain-scroll the page (and
           page scrolling never moves the sidebar) */
        overscroll-behavior: contain;
        scroll-behavior: smooth;
        /* Modern browsers (Chrome 121+, Firefox, Safari 18.2+) */
        scrollbar-width: thin;
        scrollbar-color: rgba(100, 116, 139, 0.14) transparent;
    }
    .dashboard__inner.sticky-top:hover,
    .dashboard__inner.sticky-top:focus-within {
        scrollbar-color: rgba(100, 116, 139, 0.45) transparent;
    }

    /* WebKit fallback (older Safari / Chromium that ignore scrollbar-color) */
    .dashboard__inner.sticky-top::-webkit-scrollbar {
        width: 6px;
    }
    .dashboard__inner.sticky-top::-webkit-scrollbar-track {
        background: transparent;
    }
    .dashboard__inner.sticky-top::-webkit-scrollbar-thumb {
        background-color: rgba(100, 116, 139, 0.14);
        border-radius: 999px;
    }
    .dashboard__inner.sticky-top:hover::-webkit-scrollbar-thumb {
        background-color: rgba(100, 116, 139, 0.45);
    }
    .dashboard__inner.sticky-top::-webkit-scrollbar-thumb:hover,
    .dashboard__inner.sticky-top::-webkit-scrollbar-thumb:active {
        background-color: rgba(100, 116, 139, 0.7);
    }
}

/* ── Department Modal ── */
.dm-modal {
    background: #fff; border-radius: 18px; overflow: hidden;
    border: 1.5px solid #ebebeb;
    font-family: 'Outfit', sans-serif !important;
    pointer-events: auto;
}
.dm-modal * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.dm-modal__hd {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 22px 24px 16px; border-bottom: 1.5px solid #f5f5f5;
}
.dm-modal__title { font-size: 1rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 0 0 4px; }
.dm-modal__sub   { font-size: 0.82rem; color: #9ca3af; margin: 0; }
.dm-modal__close {
    background: none; border: none; cursor: pointer; padding: 5px; color: #9ca3af;
    border-radius: 7px; display: flex; align-items: center; transition: all .15s; flex-shrink: 0;
}
.dm-modal__close:hover { background: #f3f4f6; color: #374151; }
.dm-modal__body  { padding: 20px 24px 24px; }
.dm-modal__field { margin-bottom: 20px; }
.dm-modal__label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 7px; }
.dm-modal__input {
    display: block; width: 100%; padding: 10px 13px;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 0.88rem; color: #111827; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.dm-modal__input:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.08); }
.dm-modal__input::placeholder { color: #d4d7de; }
.dm-modal__foot { display: flex; justify-content: flex-end; gap: 10px; padding-top: 4px; }
.dm-modal__btn-cancel {
    padding: 9px 18px; background: none; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; color: #6b7280; cursor: pointer;
    transition: all .15s; font-family: 'Outfit', sans-serif !important;
}
.dm-modal__btn-cancel:hover { border-color: #d1d5db; color: #374151; background: #f9fafb; }
.dm-modal__btn-save {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
    background: #0c0c0c; color: #fff; border: none; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; cursor: pointer;
    transition: all .15s; font-family: 'Outfit', sans-serif !important;
}
.dm-modal__btn-save:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }

/* ════════════════════════════════════════════════════════════
   OFF-CANVAS SIDEBAR DRAWER  (mobile + tablet, <992px)
   Above 992px the sidebar is a normal sticky column (rules above).
   Below 992px — where Bootstrap's grid already stacks it to col-md-12 —
   it becomes a slide-in drawer opened by the header hamburger, so the
   long menu no longer sits on top of every page.
   Boundary is 991.98px to match Bootstrap's own lg breakpoint
   (documented grid exception in CLAUDE.md).
   ════════════════════════════════════════════════════════════ */
.app-sidebar-backdrop { display: none; }

/* Sidebar brand (logo) — mobile only; header keeps the logo on tablet+ */
.app-sidebar-brand { display: none; }
@media (max-width: 767px) {
    .app-sidebar-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px 8px 16px;
        margin-bottom: 6px;
        border-bottom: 1px solid #eef1f6;
    }
    .app-sidebar-brand img {
        max-height: 54px;
        max-width: 100%;
        object-fit: contain;
    }
}

@media (max-width: 991.98px) {
    #appSidebarDrawer.app-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        height: 100dvh;
        width: 300px;
        max-width: 86vw;
        z-index: 1045;              /* above header (1030), below modals (1050) */
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        transform: translateX(-100%);
        transition: transform .32s cubic-bezier(.4, 0, .2, 1);
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        padding: 0;                 /* drop Bootstrap col gutter; inner owns padding */
        will-change: transform;
    }
    #appSidebarDrawer.app-sidebar.is-open {
        transform: translateX(0);
    }
    #appSidebarDrawer .dashboard__inner {
        height: auto;
        min-height: 100%;
        padding: 18px 14px calc(28px + env(safe-area-inset-bottom));
    }

    .app-sidebar-backdrop {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
        z-index: 1044;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .3s ease, visibility .3s ease;
    }
    .app-sidebar-backdrop.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
}

/* Lock page scroll behind an open drawer */
body.app-drawer-open { overflow: hidden; }
</style>

<script>
/* ═══ Off-canvas sidebar drawer (mobile/tablet) ═══
   Wires the header hamburger (#udaHamburger) to the sidebar drawer
   (#appSidebarDrawer) + backdrop. No-ops safely if either is absent. */
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        var drawer   = document.getElementById('appSidebarDrawer');
        var backdrop = document.getElementById('appSidebarBackdrop');
        var toggle   = document.getElementById('udaHamburger');
        if (!drawer || !backdrop) return;

        var MOBILE = '(max-width: 991.98px)';

        function isDrawerMode() {
            return window.matchMedia(MOBILE).matches;
        }

        function open() {
            drawer.classList.add('is-open');
            backdrop.classList.add('is-open');
            document.body.classList.add('app-drawer-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
            backdrop.setAttribute('aria-hidden', 'false');
        }

        function close() {
            drawer.classList.remove('is-open');
            backdrop.classList.remove('is-open');
            document.body.classList.remove('app-drawer-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            backdrop.setAttribute('aria-hidden', 'true');
        }

        function toggleDrawer() {
            if (drawer.classList.contains('is-open')) close();
            else open();
        }

        // The header hamburger lives in a separate component; wire it if present.
        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                toggleDrawer();
            });
        }

        // Close on backdrop tap
        backdrop.addEventListener('click', close);

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
        });

        // Tapping a destination link closes the drawer (page will navigate anyway).
        // Section headers (collapse toggles) must NOT close it.
        drawer.addEventListener('click', function (e) {
            var link = e.target.closest('a[href]');
            if (link && !link.closest('.sidebar-section-header')) close();
        });

        // If the viewport grows back to desktop, ensure a clean state.
        window.addEventListener('resize', function () {
            if (!isDrawerMode()) close();
        });
    });
})();
</script>
