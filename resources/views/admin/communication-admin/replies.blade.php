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
                    <div class="dashboard__content__wraper">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        {{-- Chat Header --}}
                        <div class="chat-header">
                            {{-- Top Section: Buttons --}}
                            <div class="chat-header-top">
                                <div class="chat-header-left">
                                    <a href="{{ route('admin.communication-admin.index') }}" class="responsive-btn back-btn">
                                        <div class="svgWrapper">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                <path stroke="#fff" stroke-width="2" d="M19 12H5m7-7-7 7 7 7"></path>
                                            </svg>
                                            <div class="text">Back</div>
                                        </div>
                                    </a>
                                </div>
                                <div class="chat-header-right">
                                    <div class="memo-stats">
                                        <span class="stat-badge">{{ $replies->total() }} Replies</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Separator Line --}}
                            <div class="chat-header-separator"></div>
                            
                            {{-- Bottom Section: Subject and Details --}}
                            <div class="chat-header-bottom">
                                <div class="chat-title">
                                    Subject: <h4>{{ $campaign->subject }}</h4>
                                </div>
                                <div class="chat-details">
                                    <div class="detail-item">
                                        <i class="icofont-user"></i>
                                        <span>From: {{ $campaign->creator->first_name }} {{ $campaign->creator->last_name }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="icofont-calendar"></i>
                                        <span>{{ $campaign->created_at->format('M d, Y \a\t h:i A') }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="icofont-tag"></i>
                                        <span>Ref: {{ $campaign->reference }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Chat Messages Container --}}
                        <div class="chat-container">
                            <div class="chat-messages" id="chat-messages">
                                @if($replies->count() > 0)
                                    @foreach($replies as $reply)
                                        <div class="message message-received">
                                            <div class="message-avatar">
                                                <img src="{{ $reply->user->profile_picture_url ?? asset('profile_pictures/default-profile.png') }}" 
                                                     alt="{{ $reply->user->first_name }}">
                                            </div>
                                            <div class="message-content">
                                                <div class="message-header">
                                                    <span class="message-sender">{{ $reply->user->first_name }} {{ $reply->user->last_name }}</span>
                                                    <span class="message-time">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                                                </div>
                                                <div class="message-text">{!! nl2br(e($reply->message)) !!}</div>
                                                @if($reply->attachments && count($reply->attachments) > 0)
                                                    <div class="message-attachments">
                                                        @foreach($reply->attachments as $index => $attachment)
                                                            @php
                                                                $isImage = str_contains($attachment['type'] ?? '', 'image');
                                                                $isPdf = str_contains($attachment['type'] ?? '', 'pdf');
                                                                $isWord = str_contains($attachment['type'] ?? '', 'word') || str_contains($attachment['type'] ?? '', 'document');
                                                                $isExcel = str_contains($attachment['type'] ?? '', 'excel') || str_contains($attachment['type'] ?? '', 'spreadsheet');
                                                                $fileSize = isset($attachment['size']) ? number_format($attachment['size'] / 1024, 1) . ' KB' : 'Unknown size';
                                                            @endphp
                                                            
                                                            @if($isImage)
                                                                {{-- Image Attachment - Show preview --}}
                                                                <div class="attachment-image-wrapper" onclick="downloadImage('{{ route('dashboard.memo.reply.download-attachment', ['reply' => $reply->id, 'index' => $index]) }}', '{{ $attachment['name'] }}')">
                                                                    <img src="{{ route('dashboard.memo.reply.download-attachment', ['reply' => $reply->id, 'index' => $index]) }}" 
                                                                         alt="{{ $attachment['name'] }}"
                                                                         class="attachment-image">
                                                                    <div class="image-overlay">
                                                                        <i class="icofont-download"></i>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                {{-- File Attachment - Show file card --}}
                                                                <div class="attachment-file-card">
                                                                    <div class="file-icon">
                                                                        @if($isPdf)
                                                                            <i class="icofont-file-pdf"></i>
                                                                        @elseif($isWord)
                                                                            <i class="icofont-file-document"></i>
                                                                        @elseif($isExcel)
                                                                            <i class="icofont-file-excel"></i>
                                                                        @else
                                                                            <i class="icofont-file-alt"></i>
                                                                        @endif
                                                                    </div>
                                                                    <div class="file-info">
                                                                        <div class="file-name">{{ $attachment['name'] }}</div>
                                                                        <div class="file-size">{{ $fileSize }}</div>
                                                                    </div>
                                                                    <a href="{{ route('dashboard.memo.reply.download-attachment', ['reply' => $reply->id, 'index' => $index]) }}" 
                                                                       class="file-download-btn"
                                                                       title="Download">
                                                                        <i class="icofont-download"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="no-messages">
                                        <div class="no-messages-icon">
                                            <i class="icofont-chat"></i>
                                        </div>
                                        <h4>No replies yet</h4>
                                        <p>No one has replied to this memo yet.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

<style>
/* Chat Header Styles - Exact match to chat page */
.chat-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 8px 8px 0 0;
    margin-bottom: 0;
    padding: 0;
}

.chat-header-top {
    padding: 20px 20px 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Back Button Styles - Exact match to chat page */
.responsive-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
    flex-shrink: 0;
    min-width: 80px;
    text-decoration: none;
}

.responsive-btn:hover {
    text-decoration: none;
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

/* Back Button - Keep Current Grey Color */
.back-btn {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
}

.back-btn:hover {
    background: linear-gradient(135deg, #545b62 0%, #3d4449 100%);
    box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.2);
}

.chat-header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-header-separator {
    height: 1px;
    background: #e9ecef;
    margin: 0 20px;
}

.chat-header-bottom {
    padding: 15px 20px 20px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-title h4 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
    font-weight: 600;
}

.chat-details {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 0.9rem;
}

.detail-item i {
    color: #007bff;
    font-size: 0.8rem;
}

/* Chat Container Styles - Exact match to chat page */
.chat-container {
    background: #fff;
    border: 1px solid #e9ecef;
    border-top: none;
    height: 400px;
    overflow-y: auto;
    padding: 20px;
}

.chat-messages {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Message Styles - Exact match to chat page */
.message {
    display: flex;
    gap: 12px;
    max-width: 70%;
}

.message-sent {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-received {
    align-self: flex-start;
}

.message-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.message-content {
    background: #f8f9fa;
    padding: 12px 16px;
    border-radius: 18px;
    position: relative;
}

.message-sent .message-content {
    background: #d1e7dd;
    color: #333;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    font-size: 0.8rem;
    opacity: 0.8;
}

.message-text {
    line-height: 1.4;
}

.message-attachments {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Image Attachment Styles - Exact match to chat page */
.attachment-image-wrapper {
    position: relative;
    max-width: 280px;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease;
}

.attachment-image-wrapper:hover {
    transform: scale(1.02);
}

.attachment-image-wrapper:hover .image-overlay {
    opacity: 1;
}

.attachment-image {
    width: 100%;
    height: auto;
    max-height: 300px;
    object-fit: cover;
    display: block;
    border-radius: 12px;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.image-overlay i {
    color: white;
    font-size: 1.5rem;
}

/* File Attachment Styles - Exact match to chat page */
.attachment-file-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 12px;
    max-width: 300px;
    transition: all 0.2s ease;
}

.message-sent .attachment-file-card {
    background: rgba(0, 0, 0, 0.08);
}

.message-received .attachment-file-card {
    background: rgba(0, 0, 0, 0.05);
}

.file-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border-radius: 50%;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    color: #333;
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.file-size {
    color: #666;
    font-size: 0.8rem;
}

.file-download-btn {
    color: #007bff;
    text-decoration: none;
    padding: 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
}

.file-download-btn:hover {
    background: #007bff;
    color: white;
}

/* No Messages State */
.no-messages {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-messages-icon {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 20px;
}

.no-messages h4 {
    color: #666;
    margin-bottom: 10px;
}

.no-messages p {
    color: #999;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .chat-header-top {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .chat-details {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .message {
        max-width: 85%;
    }
    
    .chat-container {
        height: 500px;
    }
}

/* JavaScript for image downloads */
function downloadImage(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</style>
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
