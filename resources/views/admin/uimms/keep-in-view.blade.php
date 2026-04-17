@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding">
        <div class="row">
            @include('components.create_section')
        </div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                {{-- sidebar menu --}}
                @include('components.sidebar')
                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="dashboard__message__content__main">
                        <div class="dashboard__message__content__main__title dashboard__message__content__main__title__2">
                            <h3 style="text-align: center; margin-bottom: 20px;">Keep in View - Memos Portal</h3>
                        </div>
                        
                        <div class="dashboard__meessage__wraper">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                                    <div class="dashboard__meessage">
                                        <div class="dashboard__meessage__chat memos-toolbar">
                                            <div class="memos-title-container">
                                                <span class="memos-badge" id="section-badge">🔖 Keep in View </span>
                                            </div>
                                            <div class="memos-actions">
                                                <button class="responsive-btn refresh-btn" onclick="refreshMemos()">
                                                    <div class="svgWrapper">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                            <path stroke="#fff" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                        </svg>
                                                        <div class="text">Refresh</div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="dashboard__meessage__contact" id="memos-container">
                                            <div class="text-center py-5">
                                                <i class="icofont-chat" style="font-size: 48px; color: #ddd;"></i>
                                                <p class="text-muted mt-3">Loading keep in view memos...</p>
                                            </div>
                                        </div>

                                        <style>
                                        /* Reuse styles from portal */
                                        .memos-toolbar {
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            gap: 12px;
                                            position: sticky;
                                            top: 0;
                                            z-index: 5;
                                            background: #f8f9fa;
                                            padding: 15px 20px;
                                            border-bottom: 2px solid #eef2f7;
                                        }
                                        
                                        .memos-title-container {
                                            display: flex;
                                            align-items: center;
                                        }
                                        
                                        .memos-actions {
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                        }
                                        
                                        .memos-badge {
                                            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
                                            color: #f57c00;
                                            padding: 8px 16px;
                                            border-radius: 20px;
                                            font-size: 0.9rem;
                                            font-weight: 600;
                                            box-shadow: 0 2px 8px rgba(245, 124, 0, 0.15);
                                            border: 2px solid rgba(245, 124, 0, 0.2);
                                            display: inline-block;
                                        }
                                        
                                        .memo-item {
                                            border-bottom: 1px solid #e9ecef;
                                            transition: all 0.2s ease;
                                            cursor: pointer;
                                            background: #ffffff;
                                            margin-bottom: 8px;
                                            border-radius: 12px;
                                            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                                            border: 1px solid #f1f3f4;
                                        }
                                        
                                        .memo-item:hover {
                                            background: #f8f9ff;
                                            transform: translateY(-1px);
                                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                                            border-color: #e3f2fd;
                                        }
                                        
                                        .dashboard__meessage__contact__wrap {
                                            display: flex;
                                            gap: 16px;
                                            padding: 16px 20px;
                                            align-items: flex-start;
                                            position: relative;
                                        }
                                        
                                        .dashboard__meessage__chat__img img {
                                            width: 48px;
                                            height: 48px;
                                            border-radius: 50%;
                                            object-fit: cover;
                                            border: 2px solid #e9ecef;
                                            flex-shrink: 0;
                                        }
                                        
                                        .dashboard__meessage__meta {
                                            flex: 1;
                                            min-width: 0;
                                        }
                                        
                                        .memo-header {
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            margin-bottom: 6px;
                                        }
                                        
                                        .memo-sender-info {
                                            display: flex;
                                            align-items: center;
                                            gap: 8px;
                                        }
                                        
                                        .dashboard__meessage__meta h5 {
                                            margin: 0;
                                            font-size: 1rem;
                                            font-weight: 600;
                                            color: #333;
                                        }
                                        
                                        .memo-subject {
                                            font-weight: 600;
                                            color: #1a4a9b;
                                            margin-bottom: 4px;
                                            font-size: 0.95rem;
                                        }
                                        
                                        .memo-preview {
                                            color: #666;
                                            font-size: 0.85rem;
                                            margin-bottom: 8px;
                                            display: -webkit-box;
                                            -webkit-line-clamp: 2;
                                            -webkit-box-orient: vertical;
                                            overflow: hidden;
                                            line-height: 1.3;
                                        }
                                        
                                        .memo-footer {
                                            display: flex;
                                            align-items: center;
                                            justify-content: space-between;
                                            flex-wrap: wrap;
                                            gap: 8px;
                                        }
                                        
                                        .memo-left-info {
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                        }
                                        
                                        .memo-right-info {
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                        }
                                        
                                        .chat__time {
                                            font-size: 0.8rem;
                                            color: #999;
                                            font-weight: 500;
                                        }
                                        
                                        .memo-received-datetime {
                                            font-size: 0.75rem;
                                            color: #6b7280;
                                            font-weight: 500;
                                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                            letter-spacing: 0.3px;
                                            white-space: nowrap;
                                        }
                                        
                                        .memo-received-datetime:hover {
                                            color: #4b5563;
                                        }
                                        
                                        .memo-right-section {
                                            display: flex;
                                            align-items: center;
                                            gap: 10px;
                                        }
                                        
                                        .memo-participants {
                                            display: flex;
                                            align-items: center;
                                            gap: 3px;
                                            background: #f8f9fa;
                                            padding: 2px 5px;
                                            border-radius: 8px;
                                            border: 1px solid #e9ecef;
                                        }
                                        
                                        .memo-participants i {
                                            color: #6c757d;
                                            font-size: 0.65rem;
                                        }
                                        
                                        .participant-avatar-small {
                                            width: 16px;
                                            height: 16px;
                                            border-radius: 50%;
                                            object-fit: cover;
                                            border: 1px solid #fff;
                                            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
                                        }
                                        
                                        .participant-count {
                                            font-size: 0.65rem;
                                            color: #6c757d;
                                            font-weight: 600;
                                            background: #e9ecef;
                                            padding: 1px 4px;
                                            border-radius: 6px;
                                            margin-left: 2px;
                                        }
                                        
                                        .memo-right-badges {
                                            display: flex;
                                            align-items: center;
                                            gap: 4px;
                                        }
                                        
                                        .memo-status-badge {
                                            padding: 4px 8px;
                                            border-radius: 12px;
                                            font-size: 0.7rem;
                                            font-weight: 600;
                                            text-transform: uppercase;
                                            letter-spacing: 0.3px;
                                        }
                                        
                                        .status-pending { background: #e3f2fd; color: #1976d2; }
                                        .status-suspended { background: #fff8e1; color: #f57c00; }
                                        .status-completed { background: #e8f5e8; color: #388e3c; }
                                        .status-archived { background: #f5f5f5; color: #616161; }
                                        
                                        .bookmark-icon {
                                            color: #ffc107;
                                            font-size: 1.2rem;
                                            cursor: pointer;
                                            transition: all 0.2s ease;
                                        }
                                        
                                        .bookmark-icon:hover {
                                            transform: scale(1.2);
                                            color: #f57c00;
                                        }
                                        
                                        .bookmark-icon.bookmarked {
                                            color: #ffc107;
                                        }
                                        
                                        .bookmark-icon:not(.bookmarked) {
                                            color: #ccc;
                                        }
                                        
                                        .responsive-btn {
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            padding: 8px 16px;
                                            border: none;
                                            border-radius: 20px;
                                            cursor: pointer;
                                            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.15);
                                            background-color: #1a4a9b;
                                            transition: all 0.2s ease;
                                            flex-shrink: 0;
                                            min-width: 80px;
                                        }

                                        .responsive-btn:hover {
                                            background-color: #0f3a7a;
                                            box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.2);
                                        }

                                        .responsive-btn:active {
                                            transform: translate(1px, 1px);
                                            box-shadow: 1px 1px 6px rgba(0, 0, 0, 0.15);
                                        }

                                        .responsive-btn .svgWrapper {
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            gap: 6px;
                                        }

                                        .responsive-btn .svgIcon {
                                            width: 14px;
                                            height: 14px;
                                        }

                                        .responsive-btn .text {
                                            color: white;
                                            font-size: 14px;
                                            font-weight: 600;
                                            white-space: nowrap;
                                        }

                                        .refresh-btn {
                                            background-color: #1a4a9b;
                                        }

                                        .refresh-btn:hover {
                                            background-color: #0f3a7a;
                                        }
                                        </style>

                                        <script>
                                        // Load bookmarked memos on page load
                                        window.addEventListener('load', function() {
                                            loadBookmarkedMemos();
                                        });

                                        function loadBookmarkedMemos() {
                                            // Show loading
                                            document.getElementById('memos-container').innerHTML = `
                                                <div class="text-center py-5">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <p class="text-muted mt-3">Loading bookmarked memos...</p>
                                                </div>
                                            `;
                                            
                                            // Fetch bookmarked memos
                                            fetch(`/dashboard/uimms/bookmarked-memos`)
                                                .then(response => response.json())
                                                .then(memos => {
                                                    displayMemos(memos);
                                                })
                                                .catch(error => {
                                                    console.error('Error loading bookmarked memos:', error);
                                                    document.getElementById('memos-container').innerHTML = `
                                                        <div class="text-center py-5">
                                                            <i class="icofont-warning" style="font-size: 48px; color: #dc3545;"></i>
                                                            <p class="text-danger mt-3">Error loading bookmarked memos. Please try again.</p>
                                                        </div>
                                                    `;
                                                });
                                        }

                                        function displayMemos(memos) {
                                            if (memos.length === 0) {
                                                document.getElementById('memos-container').innerHTML = `
                                                    <div class="text-center py-5">
                                                        <i class="icofont-bookmark" style="font-size: 48px; color: #ddd;"></i>
                                                        <p class="text-muted mt-3">No bookmarked memos found. Bookmark memos from the Memos Portal to keep them in view.</p>
                                                    </div>
                                                `;
                                                return;
                                            }
                                            
                                            const memosHtml = `
                                                <ul>
                                                    ${memos.map(memo => {
                                                        const creator = memo.creator || {};
                                                        const creatorName = creator.first_name && creator.last_name 
                                                            ? `${creator.first_name} ${creator.last_name}` 
                                                            : 'System';
                                                        const creatorAvatar = creator.profile_picture_url || '/profile_pictures/default-profile.png';
                                                        const lastMessage = memo.last_message;
                                                        
                                                        // Format received date and time like chat system: "M d, Y H:i"
                                                        const receivedAt = memo.received_at || memo.created_at;
                                                        const receivedDate = new Date(receivedAt);
                                                        const receivedDateFormatted = receivedDate.toLocaleDateString('en-US', { 
                                                            month: 'short', 
                                                            day: 'numeric', 
                                                            year: 'numeric' 
                                                        });
                                                        const receivedTimeFormatted = receivedDate.toLocaleTimeString('en-US', { 
                                                            hour: '2-digit', 
                                                            minute: '2-digit',
                                                            hour12: false 
                                                        });
                                                        const receivedDateTime = `${receivedDateFormatted} ${receivedTimeFormatted}`;
                                                        
                                                        const participants = memo.active_participants || [];
                                                        
                                                        return `
                                                            <li class="memo-item" data-memo-id="${memo.id}">
                                                                <div class="dashboard__meessage__contact__wrap">
                                                                    <div class="dashboard__meessage__chat__img" onclick="openMemoChat(${memo.id})">
                                                                        <img src="${creatorAvatar}" alt="${creatorName}">
                                                                    </div>
                                                                    <div class="dashboard__meessage__meta" onclick="openMemoChat(${memo.id})">
                                                                        <div class="memo-header">
                                                                            <div class="memo-sender-info">
                                                                                <h5>${creatorName}</h5>
                                                                            </div>
                                                                            <div class="memo-right-section">
                                                                                ${participants.length > 1 ? `
                                                                                    <div class="memo-participants">
                                                                                        <i class="icofont-users"></i>
                                                                                        ${participants.slice(0, 3).map(p => `
                                                                                            <img src="${p.user?.profile_picture_url || '/profile_pictures/default-profile.png'}" 
                                                                                                 alt="${p.user?.first_name || 'User'}" 
                                                                                                 class="participant-avatar-small"
                                                                                                 title="${p.user?.first_name || ''} ${p.user?.last_name || ''}">
                                                                                        `).join('')}
                                                                                        ${participants.length > 3 ? `<span class="participant-count">+${participants.length - 3}</span>` : ''}
                                                                                    </div>
                                                                                ` : ''}
                                                                                <div class="memo-right-badges">
                                                                                    <span class="memo-status-badge status-${memo.memo_status}">${memo.memo_status}</span>
                                                                                    <i class="icofont-bookmark bookmark-icon bookmarked" onclick="event.stopPropagation(); toggleBookmark(${memo.id}, this)" title="Remove from Keep in View"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="memo-subject">${memo.subject}</div>
                                                                        <div class="memo-preview">${memo.message ? memo.message.substring(0, 120) : 'No content'}...</div>
                                                                        <div class="memo-footer">
                                                                            <div class="memo-left-info">
                                                                                <!-- Left side can be used for other info if needed -->
                                                                            </div>
                                                                            <div class="memo-right-info">
                                                                                <span class="memo-received-datetime">${receivedDateTime}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        `;
                                                    }).join('')}
                                                </ul>
                                            `;
                                            
                                            document.getElementById('memos-container').innerHTML = memosHtml;
                                        }

                                        function openMemoChat(memoId) {
                                            window.location.href = `/dashboard/uimms/chat/${memoId}`;
                                        }

                                        function refreshMemos() {
                                            loadBookmarkedMemos();
                                        }

                                        function toggleBookmark(memoId, iconElement) {
                                            fetch(`/dashboard/uimms/memo/${memoId}/toggle-bookmark`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                    'Accept': 'application/json',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                credentials: 'same-origin'
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    // If unbookmarked, remove from list
                                                    if (!data.is_bookmarked) {
                                                        const memoItem = document.querySelector(`[data-memo-id="${memoId}"]`);
                                                        if (memoItem) {
                                                            memoItem.remove();
                                                        }
                                                        // Reload if no memos left
                                                        const remainingMemos = document.querySelectorAll('.memo-item');
                                                        if (remainingMemos.length === 0) {
                                                            loadBookmarkedMemos();
                                                        }
                                                    }
                                                } else {
                                                    alert('Error: ' + (data.message || 'Failed to update bookmark'));
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error toggling bookmark:', error);
                                                alert('Error updating bookmark. Please try again.');
                                            });
                                        }
                                        </script>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

