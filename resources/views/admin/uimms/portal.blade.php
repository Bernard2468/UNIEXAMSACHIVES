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
                            <h3 style="text-align: center; margin-bottom: 20px;">University Internal Memo Management System</h3>
                        </div>
                        
                        {{-- Status Cards --}}
                        <div class="row mb-4">
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 mb-3">
                                <div class="dashboard__card uimms-card pending" onclick="loadMemos('pending')" data-status="pending">
                                    <div class="dashboard__card__content">
                                        <div class="dashboard__card__icon">
                                            <i class="icofont-chat"></i>
                                        </div>
                                        <div class="dashboard__card__text">
                                            <h5>💬 Pending Memos</h5>
                                            <h3 class="count" id="count-pending">{{ $pendingCount }}</h3>
                                            <p>Pending conversations</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 mb-3">
                                <div class="dashboard__card uimms-card suspended" onclick="loadMemos('suspended')" data-status="suspended">
                                    <div class="dashboard__card__content">
                                        <div class="dashboard__card__icon">
                                            <i class="icofont-pause"></i>
                                        </div>
                                        <div class="dashboard__card__text">
                                            <h5>⏸️ Suspended Memos</h5>
                                            <h3 class="count" id="count-suspended">{{ $suspendedCount }}</h3>
                                            <p>Paused conversations</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 mb-3">
                                <div class="dashboard__card uimms-card completed" onclick="loadMemos('completed')" data-status="completed">
                                    <div class="dashboard__card__content">
                                        <div class="dashboard__card__icon">
                                            <i class="icofont-check-circled"></i>
                                        </div>
                                        <div class="dashboard__card__text">
                                            <h5>✅ Completed Memos</h5>
                                            <h3 class="count" id="count-completed">{{ $completedCount }}</h3>
                                            <p>Finished conversations</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12 mb-3">
                                <div class="dashboard__card uimms-card archived" onclick="loadMemos('archived')" data-status="archived">
                                    <div class="dashboard__card__content">
                                        <div class="dashboard__card__icon">
                                            <i class="icofont-archive"></i>
                                        </div>
                                        <div class="dashboard__card__text">
                                            <h5>📦 Memos Archive</h5>
                                            <h3 class="count" id="count-archived">{{ $archivedCount }}</h3>
                                            <p>Old conversations</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dashboard__meessage__wraper">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                                    <div class="dashboard__meessage">
                                        <div class="dashboard__meessage__chat memos-toolbar">
                                            <div class="memos-title-container">
                                                <span class="memos-badge" id="section-badge">💬 Active Chats</span>
                                                {{-- Pending: only show toggle (Unread | Read). Other statuses show section-badge only. --}}
                                                <div class="pending-sub-tabs" id="pending-sub-tabs" style="display: none;">
                                                    <span class="pending-sub-tab active" id="pending-tab-active" data-sub="active">New / Unread</span>
                                                    <span class="pending-sub-tab" id="pending-tab-read" data-sub="read">Read</span>
                                                </div>
                                            </div>
                                            <div class="memos-actions">
                                                <div class="selection-controls" id="selection-controls" style="display: none;">
                                                    <div class="select-all-container">
                                                        <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()">
                                                        <label for="select-all-checkbox" class="select-all-label">Select All</label>
                                                    </div>
                                                    <span class="selection-counter" id="selection-counter">0 selected</span>
                                                </div>
                                                <button class="responsive-btn bulk-archive-btn" id="bulk-archive-btn" onclick="bulkArchiveSelected()" style="display: none;">
                                                    <div class="svgWrapper">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                            <path stroke="#fff" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5zM10 11v6M14 11v6"></path>
                                                        </svg>
                                                        <div class="text">Archive Selected</div>
                                                    </div>
                                                </button>
                                                <button class="responsive-btn bulk-reactivate-btn" id="bulk-reactivate-btn" onclick="bulkReactivateSelected()" style="display: none;">
                                                    <div class="svgWrapper">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                            <path stroke="#fff" stroke-width="2" d="M8 3v3a2 2 0 002 2h4a2 2 0 002-2V3M8 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2h-2M8 3h8"></path>
                                                        </svg>
                                                        <div class="text">Reactivate Selected</div>
                                                    </div>
                                                </button>
                                                <button class="responsive-btn bulk-unarchive-btn" id="bulk-unarchive-btn" onclick="bulkUnarchiveSelected()" style="display: none;">
                                                    <div class="svgWrapper">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                            <path stroke="#fff" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                        </svg>
                                                        <div class="text">Unarchive Selected</div>
                                                    </div>
                                                </button>
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
                                                <p class="text-muted mt-3">Click on a card above to load memos</p>
                                            </div>
                                        </div>

                                        <style>
                                        /* Card Styles */
                                        .uimms-card {
                                            cursor: pointer;
                                            transition: all 0.3s ease;
                                            border: 2px solid transparent;
                                            border-radius: 12px;
                                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                                            position: relative;
                                            overflow: hidden;
                                        }

                                        .uimms-card::before {
                                            content: '';
                                            position: absolute;
                                            top: 0;
                                            left: 0;
                                            right: 0;
                                            bottom: 0;
                                            background: inherit;
                                            opacity: 0;
                                            transition: opacity 0.3s ease;
                                        }

                                        .uimms-card:hover {
                                            transform: translateY(-5px);
                                            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                                        }

                                        .uimms-card.active {
                                            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
                                            border-width: 3px;
                                        }

                                        .uimms-card.pending {
                                            border-color: #007bff;
                                            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                                        }

                                        .uimms-card.pending.active {
                                            border-width: 3px;
                                            border-color: #007bff;
                                            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                                            position: relative;
                                        }

                                        .uimms-card.pending.active::after {
                                            content: '●';
                                            position: absolute;
                                            top: 10px;
                                            right: 10px;
                                            color: #007bff;
                                            font-size: 16px;
                                            font-weight: bold;
                                        }

                                        .uimms-card.suspended {
                                            border-color: #ffc107;
                                            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
                                        }

                                        .uimms-card.suspended.active {
                                            border-width: 3px;
                                            border-color: #ffc107;
                                            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
                                            position: relative;
                                        }

                                        .uimms-card.suspended.active::after {
                                            content: '●';
                                            position: absolute;
                                            top: 10px;
                                            right: 10px;
                                            color: #ffc107;
                                            font-size: 16px;
                                            font-weight: bold;
                                        }

                                        .uimms-card.completed {
                                            border-color: #28a745;
                                            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
                                        }

                                        .uimms-card.completed.active {
                                            border-width: 3px;
                                            border-color: #28a745;
                                            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
                                            position: relative;
                                        }

                                        .uimms-card.completed.active::after {
                                            content: '●';
                                            position: absolute;
                                            top: 10px;
                                            right: 10px;
                                            color: #28a745;
                                            font-size: 16px;
                                            font-weight: bold;
                                        }

                                        .uimms-card.archived {
                                            border-color: #6c757d;
                                            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
                                        }

                                        .uimms-card.archived.active {
                                            border-width: 3px;
                                            border-color: #6c757d;
                                            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
                                            position: relative;
                                        }

                                        .uimms-card.archived.active::after {
                                            content: '●';
                                            position: absolute;
                                            top: 10px;
                                            right: 10px;
                                            color: #6c757d;
                                            font-size: 16px;
                                            font-weight: bold;
                                        }

                                        .dashboard__card__content {
                                            display: flex;
                                            align-items: center;
                                            padding: 20px;
                                            position: relative;
                                            z-index: 1;
                                        }

                                        .dashboard__card__icon {
                                            font-size: 48px;
                                            margin-right: 20px;
                                            opacity: 0.8;
                                        }

                                        .uimms-card.active .dashboard__card__icon {
                                            opacity: 1;
                                        }

                                        .dashboard__card__text h5 {
                                            margin: 0 0 10px 0;
                                            font-weight: 600;
                                            font-size: 1rem;
                                        }

                                        .dashboard__card__text h3 {
                                            margin: 0 0 5px 0;
                                            font-size: 2.5rem;
                                            font-weight: 700;
                                        }

                                        .dashboard__card__text p {
                                            margin: 0;
                                            font-size: 0.9rem;
                                            opacity: 0.8;
                                        }

                                        /* Laptop View - Reduce Card Sizes (xl breakpoint: 1200px+) */
                                        @media (min-width: 1200px) and (max-width: 1599px) {
                                            .dashboard__card__content {
                                                padding: 15px;
                                            }
                                            
                                            .dashboard__card__icon {
                                                font-size: 36px;
                                                margin-right: 15px;
                                            }
                                            
                                            .dashboard__card__text h3 {
                                                font-size: 2rem;
                                            }
                                            
                                            .dashboard__card__text h5 {
                                                font-size: 0.9rem;
                                                margin: 0 0 8px 0;
                                            }
                                            
                                            .dashboard__card__text p {
                                                font-size: 0.85rem;
                                            }
                                        }

                                        /* Responsive Adjustments */
                                        @media (max-width: 1199px) {
                                            .dashboard__card__icon {
                                                font-size: 40px;
                                                margin-right: 15px;
                                            }
                                            
                                            .dashboard__card__text h3 {
                                                font-size: 2rem;
                                            }
                                        }

                                        @media (max-width: 767px) {
                                            .dashboard__card__content {
                                                padding: 15px;
                                            }
                                            
                                            .dashboard__card__icon {
                                                font-size: 36px;
                                                margin-right: 12px;
                                            }
                                            
                                            .dashboard__card__text h3 {
                                                font-size: 1.8rem;
                                            }
                                            
                                            .dashboard__card__text h5 {
                                                font-size: 0.9rem;
                                            }
                                            
                                            .dashboard__card__text p {
                                                font-size: 0.8rem;
                                            }
                                        }

                                        @media (max-width: 575px) {
                                            .dashboard__card__content {
                                                flex-direction: column;
                                                text-align: center;
                                            }
                                            
                                            .dashboard__card__icon {
                                                margin-right: 0;
                                                margin-bottom: 10px;
                                            }
                                        }
                                        
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
                                        
                                        .bulk-archive-btn {
                                            background-color: #dc3545;
                                        }
                                        
                                        .bulk-archive-btn:hover {
                                            background-color: #c82333;
                                        }
                                        
                                        .bulk-reactivate-btn {
                                            background-color: #28a745;
                                        }
                                        
                                        .bulk-reactivate-btn:hover {
                                            background-color: #218838;
                                        }
                                        
                                        .pending-sub-tabs {
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 0;
                                            margin-left: 16px;
                                            background: #f1f3f4;
                                            border-radius: 20px;
                                            padding: 2px;
                                        }
                                        .pending-sub-tab {
                                            padding: 6px 14px;
                                            border-radius: 18px;
                                            font-size: 0.85rem;
                                            font-weight: 600;
                                            color: #5f6368;
                                            cursor: pointer;
                                            transition: background 0.2s, color 0.2s;
                                        }
                                        .pending-sub-tab:hover {
                                            color: #1a4a9b;
                                        }
                                        .pending-sub-tab.active {
                                            background: #fff;
                                            color: #1a4a9b;
                                            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
                                        }

                                        .memos-badge {
                                            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                                            color: #1a4a9b;
                                            padding: 8px 16px;
                                            border-radius: 20px;
                                            font-size: 0.9rem;
                                            font-weight: 600;
                                            box-shadow: 0 2px 8px rgba(26, 74, 155, 0.15);
                                            border: 2px solid rgba(26, 74, 155, 0.2);
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
                                        
                                        .badge.bg-success {
                                            background: #28a745 !important;
                                            color: white;
                                            padding: 4px 8px;
                                            border-radius: 10px;
                                            font-size: 0.7rem;
                                        }

                                        /* Text-based Refresh Button Styling */
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

                                        /* Selection Controls Styles */
                                        .selection-controls {
                                            display: flex;
                                            align-items: center;
                                            gap: 16px;
                                            background: #f8f9fa;
                                            padding: 8px 16px;
                                            border-radius: 20px;
                                            border: 2px solid #e9ecef;
                                        }

                                        .select-all-container {
                                            display: flex;
                                            align-items: center;
                                            gap: 8px;
                                        }

                                        .select-all-container input[type="checkbox"] {
                                            width: 18px;
                                            height: 18px;
                                            cursor: pointer;
                                            accent-color: #1a4a9b;
                                        }

                                        .select-all-label {
                                            font-size: 14px;
                                            font-weight: 600;
                                            color: #1a4a9b;
                                            cursor: pointer;
                                            margin: 0;
                                        }

                                        .selection-counter {
                                            font-size: 14px;
                                            font-weight: 600;
                                            color: #6c757d;
                                            background: #e9ecef;
                                            padding: 4px 12px;
                                            border-radius: 12px;
                                        }

                                        .memo-checkbox {
                                            width: 18px;
                                            height: 18px;
                                            cursor: pointer;
                                            accent-color: #1a4a9b;
                                            margin-right: 12px;
                                            flex-shrink: 0;
                                        }

                                        .memo-item.selected {
                                            background: #e3f2fd;
                                            border-color: #1a4a9b;
                                            box-shadow: 0 2px 8px rgba(26, 74, 155, 0.15);
                                        }

                                        .memo-item.selected:hover {
                                            background: #bbdefb;
                                        }
                                        
                                        .bookmark-icon {
                                            color: #ccc;
                                            font-size: 1.2rem;
                                            cursor: pointer;
                                            transition: all 0.2s ease;
                                            margin-left: 4px;
                                        }
                                        
                                        .bookmark-icon:hover {
                                            transform: scale(1.2);
                                        }
                                        
                                        .bookmark-icon.bookmarked {
                                            color: #ffc107;
                                        }
                                        
                                        .bookmark-icon:not(.bookmarked) {
                                            color: #ccc;
                                        }
                                        
                                        .bookmark-icon:not(.bookmarked):hover {
                                            color: #ffc107;
                                        }
                                        
                                        .urgency-flag-icon {
                                            color: #dc3545;
                                            font-size: 1.1rem;
                                            cursor: pointer;
                                            transition: all 0.2s ease;
                                            margin-left: 8px;
                                            padding: 4px;
                                            border: 1px solid #dc3545;
                                            border-radius: 4px;
                                            display: inline-flex;
                                            align-items: center;
                                            justify-content: center;
                                        }
                                        
                                        .urgency-flag-icon:hover {
                                            transform: scale(1.1);
                                            color: #c82333;
                                            border-color: #c82333;
                                            background-color: rgba(220, 53, 69, 0.1);
                                        }
                                        
                                        .memo-status-separator {
                                            width: 1px;
                                            height: 16px;
                                            background-color: #dee2e6;
                                            margin: 0 6px;
                                            display: inline-block;
                                        }
                                        
                                        /* Urgency Alert Dialog Styles */
                                        .urgency-dialog-overlay {
                                            display: none;
                                            position: fixed;
                                            top: 0;
                                            left: 0;
                                            width: 100%;
                                            height: 100%;
                                            background-color: rgba(0, 0, 0, 0.5);
                                            z-index: 10000;
                                            align-items: center;
                                            justify-content: center;
                                        }
                                        
                                        .urgency-dialog-overlay.show {
                                            display: flex;
                                        }
                                        
                                        .urgency-dialog {
                                            background: white;
                                            border-radius: 12px;
                                            padding: 24px;
                                            max-width: 400px;
                                            width: 90%;
                                            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                                            animation: slideIn 0.3s ease;
                                        }
                                        
                                        @keyframes slideIn {
                                            from {
                                                transform: translateY(-20px);
                                                opacity: 0;
                                            }
                                            to {
                                                transform: translateY(0);
                                                opacity: 1;
                                            }
                                        }
                                        
                                        .urgency-dialog-header {
                                            display: flex;
                                            align-items: center;
                                            gap: 12px;
                                            margin-bottom: 16px;
                                        }
                                        
                                        .urgency-dialog-icon {
                                            font-size: 24px;
                                            color: #dc3545;
                                        }
                                        
                                        .urgency-dialog-title {
                                            font-size: 18px;
                                            font-weight: 600;
                                            color: #333;
                                            margin: 0;
                                        }
                                        
                                        .urgency-dialog-message {
                                            font-size: 14px;
                                            color: #666;
                                            margin-bottom: 24px;
                                            line-height: 1.5;
                                        }
                                        
                                        .urgency-dialog-actions {
                                            display: flex;
                                            gap: 12px;
                                            justify-content: flex-end;
                                        }
                                        
                                        .urgency-dialog-btn {
                                            padding: 10px 20px;
                                            border: none;
                                            border-radius: 6px;
                                            font-size: 14px;
                                            font-weight: 600;
                                            cursor: pointer;
                                            transition: all 0.2s ease;
                                        }
                                        
                                        .urgency-dialog-btn-cancel {
                                            background-color: #e9ecef;
                                            color: #495057;
                                        }
                                        
                                        .urgency-dialog-btn-cancel:hover {
                                            background-color: #dee2e6;
                                        }
                                        
                                        .urgency-dialog-btn-confirm {
                                            background-color: #dc3545;
                                            color: white;
                                        }
                                        
                                        .urgency-dialog-btn-confirm:hover {
                                            background-color: #c82333;
                                        }
                                        
                                        .urgency-dialog-btn:disabled {
                                            opacity: 0.6;
                                            cursor: not-allowed;
                                        }
                                        </style>

                                        <script>
                                        let currentStatus = 'pending';
                                        let allPendingMemos = [];
                                        let pendingSubTab = 'active';

                                        // Load memos on page load
                                        window.addEventListener('load', function() {
                                            loadMemos('pending');
                                            // Pending sub-tab clicks: switch between Active Chat and Read (no refetch)
                                            document.getElementById('pending-tab-active')?.addEventListener('click', function() {
                                                if (currentStatus !== 'pending' || pendingSubTab === 'active') return;
                                                pendingSubTab = 'active';
                                                document.querySelectorAll('.pending-sub-tab').forEach(t => t.classList.remove('active'));
                                                this.classList.add('active');
                                                displayMemos(allPendingMemos.filter(m => m.has_new_activity));
                                                clearSelections();
                                                updateSelectionUI();
                                            });
                                            document.getElementById('pending-tab-read')?.addEventListener('click', function() {
                                                if (currentStatus !== 'pending' || pendingSubTab === 'read') return;
                                                pendingSubTab = 'read';
                                                document.querySelectorAll('.pending-sub-tab').forEach(t => t.classList.remove('active'));
                                                this.classList.add('active');
                                                displayMemos(allPendingMemos.filter(m => !m.has_new_activity));
                                                clearSelections();
                                                updateSelectionUI();
                                            });
                                        });

                                        function loadMemos(status) {
                                            currentStatus = status;
                                            
                                            // Update active card
                                            document.querySelectorAll('.uimms-card').forEach(card => {
                                                card.classList.remove('active');
                                            });
                                            document.querySelector(`.uimms-card[data-status="${status}"]`).classList.add('active');
                                            
                                            // Pending: show only toggle (Active Chat | Read); hide section badge. Other statuses: show badge, hide toggle.
                                            const sectionBadgeEl = document.getElementById('section-badge');
                                            const pendingSubTabsEl = document.getElementById('pending-sub-tabs');
                                            if (status === 'pending') {
                                                if (sectionBadgeEl) sectionBadgeEl.style.display = 'none';
                                                if (pendingSubTabsEl) pendingSubTabsEl.style.display = 'inline-flex';
                                            } else {
                                                if (sectionBadgeEl) sectionBadgeEl.style.display = '';
                                                if (pendingSubTabsEl) pendingSubTabsEl.style.display = 'none';
                                                const badges = {
                                                    'suspended': '⏸️ Suspended Conversations',
                                                    'completed': '✅ Completed Conversations',
                                                    'archived': '📦 Archived Conversations'
                                                };
                                                if (sectionBadgeEl) sectionBadgeEl.textContent = badges[status];
                                            }
                                            
                                            // Reset selection state on tab change
                                            if (typeof selectedMemos !== 'undefined' && selectedMemos) {
                                                selectedMemos.clear();
                                            }

                                            // Show/hide selection controls and bulk action buttons
                                            const selectionControls = document.getElementById('selection-controls');
                                            const bulkArchiveBtn = document.getElementById('bulk-archive-btn');
                                            const bulkReactivateBtn = document.getElementById('bulk-reactivate-btn');
                                            const bulkUnarchiveBtn = document.getElementById('bulk-unarchive-btn');
                                            
                                            if (status === 'completed') {
                                                selectionControls.style.display = 'flex';
                                                bulkArchiveBtn.style.display = 'flex';
                                                if (bulkReactivateBtn) bulkReactivateBtn.style.display = 'flex';
                                                if (bulkUnarchiveBtn) bulkUnarchiveBtn.style.display = 'none';
                                            } else if (status === 'archived') {
                                                selectionControls.style.display = 'flex';
                                                bulkArchiveBtn.style.display = 'none';
                                                if (bulkUnarchiveBtn) bulkUnarchiveBtn.style.display = 'flex';
                                            } else {
                                                selectionControls.style.display = 'none';
                                                bulkArchiveBtn.style.display = 'none';
                                                if (bulkReactivateBtn) bulkReactivateBtn.style.display = 'none';
                                                if (bulkUnarchiveBtn) bulkUnarchiveBtn.style.display = 'none';
                                                // Clear selections when switching away from selectable sections
                                                clearSelections();
                                            }

                                            // Reset selection UI counters/buttons on tab change
                                            const selectionCounter = document.getElementById('selection-counter');
                                            const selectAllCheckbox = document.getElementById('select-all-checkbox');
                                            if (selectionCounter) selectionCounter.textContent = '0 selected';
                                            if (selectAllCheckbox) {
                                                selectAllCheckbox.checked = false;
                                                selectAllCheckbox.indeterminate = false;
                                            }
                                            if (bulkArchiveBtn) bulkArchiveBtn.style.display = 'none';
                                            if (bulkUnarchiveBtn) bulkUnarchiveBtn.style.display = 'none';
                                            
                                            // Show loading
                                            document.getElementById('memos-container').innerHTML = `
                                                <div class="text-center py-5">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <p class="text-muted mt-3">Loading memos...</p>
                                                </div>
                                            `;
                                            
                                            // Fetch memos
                                            fetch(`/dashboard/uimms/memos/${status}`)
                                                .then(response => response.json())
                                                .then(memos => {
                                                    if (status === 'pending') {
                                                        allPendingMemos = memos;
                                                        pendingSubTab = 'active';
                                                        document.querySelectorAll('.pending-sub-tab').forEach(t => t.classList.remove('active'));
                                                        const activeTab = document.getElementById('pending-tab-active');
                                                        if (activeTab) activeTab.classList.add('active');
                                                        memos = memos.filter(m => m.has_new_activity);
                                                    }
                                                    displayMemos(memos);
                                                    // After rendering, ensure selection UI is clean
                                                    clearSelections();
                                                    updateSelectionUI();
                                                })
                                                .catch(error => {
                                                    console.error('Error loading memos:', error);
                                                    document.getElementById('memos-container').innerHTML = `
                                                        <div class="text-center py-5">
                                                            <i class="icofont-warning" style="font-size: 48px; color: #dc3545;"></i>
                                                            <p class="text-danger mt-3">Error loading memos. Please try again.</p>
                                                        </div>
                                                    `;
                                                });
                                        }

                                        function displayMemos(memos) {
                                            // Update the counter based on current status
                                            if (currentStatus === 'pending') {
                                                const pendingCounter = document.getElementById('count-pending');
                                                if (pendingCounter) {
                                                    pendingCounter.textContent = allPendingMemos.length;
                                                }
                                            } else if (currentStatus === 'suspended') {
                                                const suspendedCounter = document.getElementById('count-suspended');
                                                if (suspendedCounter) {
                                                    suspendedCounter.textContent = memos.length;
                                                }
                                            } else if (currentStatus === 'completed') {
                                                const completedCounter = document.getElementById('count-completed');
                                                if (completedCounter) {
                                                    completedCounter.textContent = memos.length;
                                                }
                                            } else if (currentStatus === 'archived') {
                                                const archivedCounter = document.getElementById('count-archived');
                                                if (archivedCounter) {
                                                    archivedCounter.textContent = memos.length;
                                                }
                                            }
                                            
                                            if (memos.length === 0) {
                                                document.getElementById('memos-container').innerHTML = `
                                                    <div class="text-center py-5">
                                                        <i class="icofont-chat" style="font-size: 48px; color: #ddd;"></i>
                                                        <p class="text-muted mt-3">No memos found in this section</p>
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
                                                        const isUnread = (currentStatus === 'pending' && memo.has_new_activity) || false;
                                                        
                                                        return `
                                                            <li class="memo-item" data-memo-id="${memo.id}">
                                                                <div class="dashboard__meessage__contact__wrap">
                                                                    ${currentStatus === 'completed' || currentStatus === 'archived' ? `
                                                                        <input type="checkbox" class="memo-checkbox" id="memo-${memo.id}" onchange="toggleMemoSelection(${memo.id})" onclick="event.stopPropagation()">
                                                                    ` : ''}
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
                                                                                    ${memo.memo_status === 'pending' ? `<span class="memo-status-separator"></span><i class="icofont-flag urgency-flag-icon" onclick="event.stopPropagation(); showUrgencyAlertDialog(${memo.id})" title="Send Urgency Alert"></i>` : ''}
                                                                                    ${isUnread ? '<span class="badge bg-success">New</span>' : ''}
                                                                                    <i class="icofont-bookmark bookmark-icon ${memo.is_bookmarked ? 'bookmarked' : ''}" onclick="event.stopPropagation(); toggleBookmark(${memo.id}, this)" title="${memo.is_bookmarked ? 'Remove from Keep in View' : 'Add to Keep in View'}"></i>
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
                                            // Reset selection state before refresh
                                            clearSelections();
                                            loadMemos(currentStatus);
                                        }
                                        
                                        // Selection management
                                        let selectedMemos = new Set();

                                        function toggleMemoSelection(memoId) {
                                            const memoItem = document.querySelector(`[data-memo-id="${memoId}"]`);
                                            const checkbox = document.getElementById(`memo-${memoId}`);
                                            
                                            if (!checkbox || !memoItem) return;
                                            
                                            if (checkbox.checked) {
                                                selectedMemos.add(memoId);
                                                memoItem.classList.add('selected');
                                            } else {
                                                selectedMemos.delete(memoId);
                                                memoItem.classList.remove('selected');
                                            }
                                            
                                            updateSelectionUI();
                                        }

                                        function toggleSelectAll() {
                                            const selectAllCheckbox = document.getElementById('select-all-checkbox');
                                            const memoCheckboxes = document.querySelectorAll('.memo-checkbox');
                                            
                                            if (!selectAllCheckbox) return;
                                            
                                            if (selectAllCheckbox.checked) {
                                                // Select all
                                                memoCheckboxes.forEach(checkbox => {
                                                    const memoId = parseInt(checkbox.id.replace('memo-', ''));
                                                    selectedMemos.add(memoId);
                                                    checkbox.checked = true;
                                                    const memoItem = document.querySelector(`[data-memo-id="${memoId}"]`);
                                                    if (memoItem) {
                                                        memoItem.classList.add('selected');
                                                    }
                                                });
                                            } else {
                                                // Deselect all
                                                clearSelections();
                                            }
                                            
                                            updateSelectionUI();
                                        }

                                        function clearSelections() {
                                            selectedMemos.clear();
                                            document.querySelectorAll('.memo-checkbox').forEach(checkbox => {
                                                checkbox.checked = false;
                                            });
                                            document.querySelectorAll('.memo-item').forEach(item => {
                                                item.classList.remove('selected');
                                            });
                                            const selectAllCheckbox = document.getElementById('select-all-checkbox');
                                            if (selectAllCheckbox) {
                                                selectAllCheckbox.checked = false;
                                            }
                                        }

                                        function updateSelectionUI() {
                                            const selectedCount = selectedMemos.size;
                                            const totalCount = document.querySelectorAll('.memo-checkbox').length;
                                            const selectionCounter = document.getElementById('selection-counter');
                                            const selectAllCheckbox = document.getElementById('select-all-checkbox');
                                            const bulkArchiveBtn = document.getElementById('bulk-archive-btn');
                                            const bulkReactivateBtn = document.getElementById('bulk-reactivate-btn');
                                            const bulkUnarchiveBtn = document.getElementById('bulk-unarchive-btn');

                                            if (!selectionCounter || !selectAllCheckbox) {
                                                return;
                                            }

                                            // Update counter
                                            selectionCounter.textContent = `${selectedCount} selected`;

                                            // Update select all checkbox state
                                            if (selectedCount === 0) {
                                                selectAllCheckbox.checked = false;
                                                selectAllCheckbox.indeterminate = false;
                                            } else if (selectedCount === totalCount && totalCount > 0) {
                                                selectAllCheckbox.checked = true;
                                                selectAllCheckbox.indeterminate = false;
                                            } else {
                                                selectAllCheckbox.checked = false;
                                                selectAllCheckbox.indeterminate = true;
                                            }

                                            // Toggle bulk action buttons based on current section
                                            if (typeof currentStatus !== 'undefined') {
                                                if (currentStatus === 'completed') {
                                                    if (bulkArchiveBtn) {
                                                        if (selectedCount > 0) {
                                                            bulkArchiveBtn.style.display = 'flex';
                                                            const textElement = bulkArchiveBtn.querySelector('.text');
                                                            if (textElement) {
                                                                textElement.textContent = `Archive Selected (${selectedCount})`;
                                                            }
                                                        } else {
                                                            bulkArchiveBtn.style.display = 'none';
                                                        }
                                                    }
                                                    if (bulkReactivateBtn) {
                                                        if (selectedCount > 0) {
                                                            bulkReactivateBtn.style.display = 'flex';
                                                            const textElement = bulkReactivateBtn.querySelector('.text');
                                                            if (textElement) {
                                                                textElement.textContent = `Reactivate Selected (${selectedCount})`;
                                                            }
                                                        } else {
                                                            bulkReactivateBtn.style.display = 'none';
                                                        }
                                                    }
                                                    if (bulkUnarchiveBtn) bulkUnarchiveBtn.style.display = 'none';
                                                } else if (currentStatus === 'archived') {
                                                    if (bulkUnarchiveBtn) {
                                                        if (selectedCount > 0) {
                                                            bulkUnarchiveBtn.style.display = 'flex';
                                                            const textElement = bulkUnarchiveBtn.querySelector('.text');
                                                            if (textElement) {
                                                                textElement.textContent = `Unarchive Selected (${selectedCount})`;
                                                            }
                                                        } else {
                                                            bulkUnarchiveBtn.style.display = 'none';
                                                        }
                                                    }
                                                    if (bulkArchiveBtn) bulkArchiveBtn.style.display = 'none';
                                                } else {
                                                    if (bulkArchiveBtn) bulkArchiveBtn.style.display = 'none';
                                                    if (bulkReactivateBtn) bulkReactivateBtn.style.display = 'none';
                                                    if (bulkUnarchiveBtn) bulkUnarchiveBtn.style.display = 'none';
                                                }
                                            }
                                        }

                                        function bulkArchiveSelected() {
                                            if (selectedMemos.size === 0) {
                                                alert('Please select at least one memo to archive.');
                                                return;
                                            }
                                            
                                            const confirmMessage = selectedMemos.size === 1 
                                                ? 'Are you sure you want to archive the selected memo?'
                                                : `Are you sure you want to archive ${selectedMemos.size} selected memos?`;
                                            
                                            confirmAction(confirmMessage, function() {
                                                // Show loading state
                                                const bulkArchiveBtn = document.getElementById('bulk-archive-btn');
                                                const originalText = bulkArchiveBtn.innerHTML;
                                                bulkArchiveBtn.innerHTML = `
                                                    <div class="svgWrapper">
                                                        <div class="spinner-border spinner-border-sm text-white" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                        <div class="text">Archiving...</div>
                                                    </div>
                                                `;
                                                bulkArchiveBtn.disabled = true;
                                                
                                                fetch('/dashboard/uimms/bulk-archive-selected', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                        'Accept': 'application/json',
                                                        'X-Requested-With': 'XMLHttpRequest'
                                                    },
                                                    credentials: 'same-origin',
                                                    body: JSON.stringify({
                                                        memo_ids: Array.from(selectedMemos).map(id => parseInt(id))
                                                    })
                                                })
                                                .then(response => {
                                                    if (!response.ok) {
                                                        // For 422 errors, try to get the validation error details
                                                        if (response.status === 422) {
                                                            return response.json().then(errorData => {
                                                                throw new Error(`Validation Error: ${JSON.stringify(errorData)}`);
                                                            });
                                                        }
                                                        throw new Error(`HTTP error! status: ${response.status}`);
                                                    }
                                                    return response.json();
                                                })
                                                .then(data => {
                                                    if (data.success) {
                                                        alert(`Successfully archived ${data.archived_count} selected memos.`);
                                                        // Clear selections and refresh the completed memos list
                                                        clearSelections();
                                                        loadMemos('completed');
                                                        // Update the counts
                                                        if (data.counts) {
                                                            document.getElementById('count-completed').textContent = data.counts.completed;
                                                            document.getElementById('count-archived').textContent = data.counts.archived;
                                                        }
                                                    } else {
                                                        alert('Error archiving memos: ' + (data.message || 'Unknown error'));
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error:', error);
                                                    alert('Error archiving memos. Please check the console and try clearing the cache (run: php artisan route:clear).');
                                                })
                                                .finally(() => {
                                                    // Restore button state
                                                    bulkArchiveBtn.innerHTML = originalText;
                                                    bulkArchiveBtn.disabled = false;
                                                });
                                            }, null, {
                                                title: 'Archive Memos',
                                                type: 'warning',
                                                confirmText: 'Archive',
                                                subtitle: 'This will move the selected memos to the archive.'
                                            });
                                        }

                                        // Toggle bookmark function
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
                                                    // Toggle the bookmarked class
                                                    if (data.is_bookmarked) {
                                                        iconElement.classList.add('bookmarked');
                                                        iconElement.setAttribute('title', 'Remove from Keep in View');
                                                    } else {
                                                        iconElement.classList.remove('bookmarked');
                                                        iconElement.setAttribute('title', 'Add to Keep in View');
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

                                        // Urgency Alert Dialog Functions
                                        let currentUrgencyMemoId = null;
                                        
                                        function showUrgencyAlertDialog(memoId) {
                                            currentUrgencyMemoId = memoId;
                                            const dialog = document.getElementById('urgency-alert-dialog');
                                            if (dialog) {
                                                dialog.classList.add('show');
                                            }
                                        }
                                        
                                        function closeUrgencyAlertDialog() {
                                            const dialog = document.getElementById('urgency-alert-dialog');
                                            if (dialog) {
                                                dialog.classList.remove('show');
                                            }
                                            currentUrgencyMemoId = null;
                                        }
                                        
                                        function sendUrgencyAlert() {
                                            if (!currentUrgencyMemoId) return;
                                            
                                            const confirmBtn = document.getElementById('urgency-confirm-btn');
                                            const cancelBtn = document.getElementById('urgency-cancel-btn');
                                            
                                            // Disable buttons
                                            confirmBtn.disabled = true;
                                            cancelBtn.disabled = true;
                                            confirmBtn.textContent = 'Sending...';
                                            
                                            fetch(`/dashboard/uimms/memo/${currentUrgencyMemoId}/send-urgency-alert`, {
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
                                                    alert('Urgency alert sent successfully!');
                                                    closeUrgencyAlertDialog();
                                                } else {
                                                    alert('Error: ' + (data.message || 'Failed to send urgency alert'));
                                                    // Re-enable buttons on error
                                                    confirmBtn.disabled = false;
                                                    cancelBtn.disabled = false;
                                                    confirmBtn.textContent = 'Yes, Send Alert';
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error sending urgency alert:', error);
                                                alert('Error sending urgency alert. Please try again.');
                                                // Re-enable buttons on error
                                                confirmBtn.disabled = false;
                                                cancelBtn.disabled = false;
                                                confirmBtn.textContent = 'Yes, Send Alert';
                                            });
                                        }
                                        
                                        // Close dialog when clicking overlay
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const overlay = document.getElementById('urgency-alert-dialog');
                                            if (overlay) {
                                                overlay.addEventListener('click', function(e) {
                                                    if (e.target === overlay) {
                                                        closeUrgencyAlertDialog();
                                                    }
                                                });
                                            }
                                        });

                                        // Auto-refresh every 30 seconds
                                        setInterval(() => {
                                            if (currentStatus) {
                                                loadMemos(currentStatus);
                                            }
                                        }, 30000);

                                        // Confirmation function for unarchiving memo
                                        function confirmUnarchiveMemo(memoId) {
                                            confirmAction(
                                                'Are you sure you want to unarchive this memo?',
                                                function() {
                                                    unarchiveMemo(memoId);
                                                },
                                                null,
                                                {
                                                    title: 'Unarchive Memo',
                                                    type: 'primary',
                                                    confirmText: 'Unarchive',
                                                    subtitle: 'This will move the memo back to completed status.'
                                                }
                                            );
                                        }

                                        // Function to unarchive memo
                                        function unarchiveMemo(memoId) {
                                            const formData = new FormData();
                                            formData.append('status', 'completed');
                                            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                                            
                                            fetch(`/dashboard/uimms/chat/${memoId}/status`, {
                                                method: 'POST',
                                                body: formData
                                            })
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {
                                                    // Refresh the current view
                                                    loadMemos(currentStatus);
                                                } else {
                                                    alert('Error unarchiving memo. Please try again.');
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error unarchiving memo:', error);
                                                alert('Error unarchiving memo. Please try again.');
                                            });
                                        }

                                        // Bulk unarchive selected memos
                                        function bulkUnarchiveSelected() {
                                            if (selectedMemos.size === 0) {
                                                alert('Please select at least one memo to unarchive.');
                                                return;
                                            }
                                            
                                            const confirmMessage = selectedMemos.size === 1 
                                                ? 'Are you sure you want to unarchive the selected memo?'
                                                : `Are you sure you want to unarchive ${selectedMemos.size} selected memos?`;
                                            
                                            confirmAction(confirmMessage, function() {
                                                // Show loading state
                                                const bulkUnarchiveBtn = document.getElementById('bulk-unarchive-btn');
                                                const originalText = bulkUnarchiveBtn.innerHTML;
                                                bulkUnarchiveBtn.innerHTML = `
                                                    <div class="svgWrapper">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                            <path stroke="#fff" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                        </svg>
                                                        <div class="text">Unarchiving...</div>
                                                    </div>
                                                `;
                                                bulkUnarchiveBtn.disabled = true;
                                                
                                                // Prepare form data
                                                const formData = new FormData();
                                                formData.append('memo_ids', JSON.stringify(Array.from(selectedMemos)));
                                                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                                                
                                                // Send bulk unarchive request
                                                fetch('/dashboard/uimms/bulk-unarchive', {
                                                    method: 'POST',
                                                    body: formData
                                                })
                                                .then(response => {
                                                    if (!response.ok) {
                                                        if (response.status === 422) {
                                                            return response.json().then(errorData => {
                                                                throw new Error(`Validation Error: ${JSON.stringify(errorData)}`);
                                                            });
                                                        }
                                                        throw new Error(`HTTP error! status: ${response.status}`);
                                                    }
                                                    return response.json();
                                                })
                                                .then(data => {
                                                    if (data.success) {
                                                        alert(`Successfully unarchived ${data.unarchived_count} selected memos.`);
                                                        // Clear selections and refresh the archived memos list
                                                        clearSelections();
                                                        loadMemos('archived');
                                                        // Update the counts
                                                        if (data.counts) {
                                                            document.getElementById('count-completed').textContent = data.counts.completed;
                                                            document.getElementById('count-archived').textContent = data.counts.archived;
                                                        }
                                                    } else {
                                                        alert('Error unarchiving memos. Please try again.');
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error:', error);
                                                    alert('Error unarchiving memos. Please check the console and try again.');
                                                })
                                                .finally(() => {
                                                    // Restore button state
                                                    bulkUnarchiveBtn.innerHTML = originalText;
                                                    bulkUnarchiveBtn.disabled = false;
                                                });
                                            }, null, {
                                                title: 'Unarchive Memos',
                                                type: 'primary',
                                                confirmText: 'Unarchive',
                                                subtitle: 'This will move the selected memos back to completed status.'
                                            });
                                        }

                                        // Bulk reactivate selected memos (move from completed to pending)
                                        function bulkReactivateSelected() {
                                            if (selectedMemos.size === 0) {
                                                alert('Please select at least one memo to reactivate.');
                                                return;
                                            }
                                            
                                            const confirmMessage = selectedMemos.size === 1 
                                                ? 'Are you sure you want to reactivate the selected memo?'
                                                : `Are you sure you want to reactivate ${selectedMemos.size} selected memos?`;
                                            
                                            confirmAction(confirmMessage, function() {
                                                // Show loading state
                                                const bulkReactivateBtn = document.getElementById('bulk-reactivate-btn');
                                                const originalText = bulkReactivateBtn.innerHTML;
                                                bulkReactivateBtn.innerHTML = `
                                                    <div class="svgWrapper">
                                                        <div class="spinner-border spinner-border-sm text-white" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                        <div class="text">Resuming...</div>
                                                    </div>
                                                `;
                                                bulkReactivateBtn.disabled = true;
                                                
                                                // Prepare form data
                                                const formData = new FormData();
                                                formData.append('memo_ids', JSON.stringify(Array.from(selectedMemos)));
                                                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                                                
                                                // Send bulk reactivate request
                                                fetch('/dashboard/uimms/bulk-reactivate', {
                                                    method: 'POST',
                                                    body: formData
                                                })
                                                .then(response => {
                                                    if (!response.ok) {
                                                        if (response.status === 422) {
                                                            return response.json().then(errorData => {
                                                                throw new Error(`Validation Error: ${JSON.stringify(errorData)}`);
                                                            });
                                                        }
                                                        throw new Error(`HTTP error! status: ${response.status}`);
                                                    }
                                                    return response.json();
                                                })
                                                .then(data => {
                                                    if (data.success) {
                                                        alert(`Successfully reactivated ${data.reactivated_count} selected memos.`);
                                                        // Clear selections and refresh the completed memos list
                                                        clearSelections();
                                                        loadMemos('completed');
                                                        // Update the counts
                                                        if (data.counts) {
                                                            document.getElementById('count-pending').textContent = data.counts.pending;
                                                            document.getElementById('count-completed').textContent = data.counts.completed;
                                                        }
                                                    } else {
                                                        alert('Error resuming memos. Please try again.');
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error:', error);
                                                    alert('Error resuming memos. Please check the console and try again.');
                                                })
                                                .finally(() => {
                                                    // Restore button state
                                                    bulkReactivateBtn.innerHTML = originalText;
                                                    bulkReactivateBtn.disabled = false;
                                                });
                                            }, null, {
                                                title: 'Reactivate Memos',
                                                type: 'primary',
                                                confirmText: 'Reactivate',
                                                subtitle: 'This will move the selected memos back to pending status to reactivate conversations.'
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

<!-- Urgency Alert Dialog -->
<div class="urgency-dialog-overlay" id="urgency-alert-dialog">
    <div class="urgency-dialog" onclick="event.stopPropagation()">
        <div class="urgency-dialog-header">
            <i class="icofont-flag urgency-dialog-icon"></i>
            <h3 class="urgency-dialog-title">Send Urgency Alert</h3>
        </div>
        <p class="urgency-dialog-message">
            You are about to send an urgency alert email to all participants of this pending memo. This will notify them that the memo requires immediate attention.
        </p>
        <div class="urgency-dialog-actions">
            <button class="urgency-dialog-btn urgency-dialog-btn-cancel" id="urgency-cancel-btn" onclick="closeUrgencyAlertDialog()">Cancel</button>
            <button class="urgency-dialog-btn urgency-dialog-btn-confirm" id="urgency-confirm-btn" onclick="sendUrgencyAlert()">Yes, Send Alert</button>
        </div>
    </div>
</div>

@endsection