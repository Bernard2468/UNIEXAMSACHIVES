<div class="col-xl-3 col-lg-3 col-md-12">
    <div class="dashboard__inner sticky-top">
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
                    <a class="{{ request()->routeIs('dashboard.my.exams') ? 'active' : '' }}" href="{{route('dashboard.my.exams')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762892670/exam_esftn0.png" alt="My Exams" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        My Exams</a><span class="dashboard__label">{{$myExamsCount}}</span>
                </li>
                    <li>
                        <a class="{{ request()->routeIs('dashboard.upload.document') ? 'active' : '' }}" href="{{route('dashboard.upload.document')}}">
                            <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762892811/exam_1_jho0sq.png" alt="All Exams Archive" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                            All Exams Archive</a><span class="dashboard__label">{{$allExamsCount}}</span>
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
                    <a class="{{ request()->routeIs('dashboard.my.files') ? 'active' : '' }}" href="{{route('dashboard.my.files')}}">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939971/approved_jjmla9.png" alt="My Files" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                        My Files</a><span class="dashboard__label">{{$myFilesCount}}</span>
                </li>
                    <li>
                        <a class="{{ request()->routeIs('dashboard.upload.file') ? 'active' : '' }}" href="{{route('dashboard.upload.file')}}">
                            <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939473/file_k1pnab.png" alt="All Files Archive" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                            All Files Archive</a><span class="dashboard__label">{{$allFilesCount}}</span>
                    </li>
                    <li>
                        <a class="{{ request()->routeIs('dashboard.folders.*') ? 'active' : '' }}" href="{{route('dashboard.folders.index')}}">
                            <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762939707/folder_vta5tl.png" alt="My Folders" style="width: 18px; height: 18px; object-fit: contain; margin-right: 10px;">
                            My Folders</a>
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
                            <a class="{{ request()->routeIs('dashboard.payment-history.*') ? 'active' : '' }}" href="{{route('dashboard.payment-history.index')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-credit-card">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                Payment History</a>
                        </li>
                    @endunless
                @endauth
                <li>
                    <a class="{{ request()->routeIs('dashboard.system-licences') ? 'active' : '' }}" href="{{route('dashboard.system-licences')}}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        System Licences</a>
                </li>
                @auth
                    @unless(auth()->user()->is_admin)
                        <li>
                            <a class="{{ request()->routeIs('dashboard.system-documentation.manage') ? 'active' : '' }}" href="{{route('dashboard.system-documentation.manage')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                                System Documentation</a>
                        </li>
                    @else
                        <li>
                            <a class="{{ request()->routeIs('dashboard.system-documentation') ? 'active' : '' }}" href="{{route('dashboard.system-documentation')}}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px;">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                                System Documentation</a>
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
});

// Dropdown functionality removed - using direct link now
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.dashboard__inner,
.dashboard__inner * {
    font-family: 'Outfit', sans-serif !important;
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

.is_dark .dm-modal { background: #111827; border-color: #1e2330; }
.is_dark .dm-modal__hd  { border-color: #1e2330; }
.is_dark .dm-modal__title { color: #f3f4f6; }
.is_dark .dm-modal__input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .dm-modal__input:focus { border-color: #f3f4f6; }
.is_dark .dm-modal__btn-save { background: #f3f4f6; color: #0c0c0c; }
</style>
