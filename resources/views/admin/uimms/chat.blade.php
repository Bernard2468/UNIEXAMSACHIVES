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
                        {{-- Chat Header --}}
                        <div class="chat-header">
                            {{-- Top Section: Buttons --}}
                            <div class="chat-header-top">
                            <div class="chat-header-left">
                                    <a href="{{ route('dashboard.uimms.portal') }}" class="responsive-btn back-btn">
                                        <div class="svgWrapper">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                <path stroke="#fff" stroke-width="2" d="M19 12H5m7-7-7 7 7 7"></path>
                                            </svg>
                                            <div class="text">Back</div>
                                        </div>
                                    </a>
                                    
                                    <div class="header-separator"></div>
                                    <button class="responsive-btn export-btn" onclick="exportChatConversation()">
                                        <div class="svgWrapper">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                                <path stroke="#fff" stroke-width="2" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline stroke="#fff" stroke-width="2" points="14,2 14,8 20,8"></polyline>
                                                <line stroke="#fff" stroke-width="2" x1="16" y1="13" x2="8" y2="13"></line>
                                                <line stroke="#fff" stroke-width="2" x1="16" y1="17" x2="8" y2="17"></line>
                                                <polyline stroke="#fff" stroke-width="2" points="10,9 9,9 8,9"></polyline>
                                            </svg>
                                            <div class="text">Export & Print</div>
                                        </div>
                                    </button>
                            </div>
                            <div class="chat-header-right">
                                <div class="memo-status">
                                    <span class="status-badge status-{{ $memo->memo_status ?? 'pending' }}">
                                        {{ $memo->memo_status ?? 'pending' }}
                                    </span>
                                    @if(($memo->memo_status ?? 'pending') === 'pending')
                                        <span class="memo-status-separator" style="width: 1px; height: 16px; background-color: #dee2e6; margin: 0 6px; display: inline-block;"></span>
                                        <i class="icofont-flag urgency-flag-icon" onclick="showUrgencyAlertDialog({{ $memo->id }})" title="Send Urgency Alert" style="color: #dc3545; font-size: 1.1rem; cursor: pointer; transition: all 0.2s ease; margin-left: 0; padding: 4px; border: 1px solid #dc3545; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;"></i>
                                    @endif
                                </div>
                                <div class="chat-actions">
                                    @php
                                        $userId = auth()->id();
                                        $isCurrentAssignee = $memo->current_assignee_id == $userId;
                                        $isActiveParticipant = $memo->isActiveParticipant($userId);
                                        $canManageMemo = $isCurrentAssignee || $isActiveParticipant;
                                    @endphp
                                    
                                    @if(!in_array($memo->memo_status, ['completed', 'archived']) && $canManageMemo)
                                        <button class="btn btn-sm btn-outline-primary" onclick="showAssignModal()">
                                            <i class="icofont-user"></i> Minute - To
                                        </button>
                                    @else
                                        <span class="btn btn-sm btn-outline-primary disabled" title="{{ !$canManageMemo ? 'Only assignee or active participants can assign' : 'Memo is completed/archived' }}">
                                            <i class="icofont-user"></i> Minute - To
                                        </span>
                                    @endif
                                    <div class="btn-group">
                                        @if($memo->memo_status === 'suspended' && $canManageMemo && $memo->canUnsuspend(Auth::id()))
                                            {{-- When suspended, show only unsuspend button to the user who suspended it --}}
                                            <button class="btn btn-sm btn-success" onclick="confirmUnsuspendMemo()">
                                                <i class="icofont-play"></i> Unsuspend
                                            </button>
                                        @elseif(!in_array($memo->memo_status, ['completed', 'archived', 'suspended']) && $canManageMemo)
                                            {{-- When not suspended, completed, or archived, show normal buttons --}}
                                            <button class="btn btn-sm btn-outline-info" onclick="keepInViewMemo()">
                                                K.I.V
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" onclick="confirmCompleteMemo()">
                                                <i class="icofont-check-circled"></i> Complete
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="showSuspendModal()">
                                                <i class="icofont-pause"></i> Suspend
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="confirmArchiveMemo()">
                                                <i class="icofont-archive"></i> Archive
                                            </button>
                                        @else
                                            @if($memo->memo_status === 'completed')
                                                <span class="btn btn-sm btn-success disabled">
                                                    <i class="icofont-check-circled"></i> Completed
                                                </span>
                                            @elseif($memo->memo_status === 'archived')
                                                <span class="btn btn-sm btn-secondary disabled">
                                                    <i class="icofont-archive"></i> Archived
                                                </span>
                                            @elseif($memo->memo_status === 'suspended')
                                                @php
                                                    $suspendedBy = $memo->getSuspendedBy();
                                                    $suspendedByUser = $suspendedBy ? \App\Models\User::find($suspendedBy) : null;
                                                @endphp
                                                <span class="btn btn-sm btn-warning disabled" title="Suspended by {{ $suspendedByUser ? $suspendedByUser->first_name . ' ' . $suspendedByUser->last_name : 'Unknown user' }}">
                                                    <i class="icofont-pause"></i> Suspended
                                                </span>
                                            @elseif(!$canManageMemo)
                                                <span class="btn btn-sm btn-outline-success disabled" title="Only assignee or active participants can manage memo">
                                                    <i class="icofont-check-circled"></i> Complete
                                                </span>
                                                <span class="btn btn-sm btn-outline-warning disabled" title="Only assignee or active participants can manage memo">
                                                    <i class="icofont-pause"></i> Suspend
                                                </span>
                                                <span class="btn btn-sm btn-outline-secondary disabled" title="Only assignee or active participants can manage memo">
                                                    <i class="icofont-archive"></i> Archive
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Separator Line --}}
                            <div class="chat-header-separator"></div>
                            
                            {{-- Bottom Section: Subject and Participants --}}
                            <div class="chat-header-bottom">
                                <div class="chat-title">
                                    Subject: <h4>{{ $memo->subject }}</h4>
                                </div>
                                <div class="chat-participants">
                                    @foreach($memo->recipients as $participant)
                                        <img src="{{ $participant->user->profile_picture_url ?? asset('profile_pictures/default-profile.png') }}" 
                                             alt="{{ $participant->user->first_name }}" 
                                             class="participant-avatar"
                                             title="{{ $participant->user->first_name }} {{ $participant->user->last_name }}">
                                    @endforeach
                                    <span class="recipients-count">{{ $memo->recipients->count() }} Recipients</span>
                                </div>
                            </div>
                        </div>

                        {{-- Memo Details Section --}}
                        <div class="memo-details-section">
                            <div class="memo-details-content">
                                <div class="memo-details-single-row">
                                    <div class="memo-detail-item-inline">
                                        <label>From:</label>
                                        <span class="memo-sender">
                                            <img src="{{ $memo->creator->profile_picture_url ?? asset('profile_pictures/default-profile.png') }}" 
                                                 alt="{{ $memo->creator->first_name }}" class="sender-avatar">
                                            {{ $memo->creator->first_name }} {{ $memo->creator->last_name }}
                                        </span>
                                    </div>
                                    
                                    <div class="memo-detail-item-inline">
                                        <label>Created:</label>
                                        <span class="memo-date">{{ $memo->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    
                                    @if($canParticipate && $memo->workflow_history && count($memo->workflow_history) > 0)
                                        @php
                                            $lastAssignment = collect($memo->workflow_history)
                                                ->where('action', 'assigned')
                                                ->sortByDesc('timestamp')
                                                ->first();
                                        @endphp
                                        @if($lastAssignment)
                                            <div class="memo-detail-item-inline">
                                                <label>Assigned By:</label>
                                                <span class="memo-assigner">
                                                    @php
                                                        $assigner = \App\Models\User::find($lastAssignment['user_id']);
                                                    @endphp
                                                    @if($assigner)
                                                        <img src="{{ $assigner->profile_picture_url ?? asset('profile_pictures/default-profile.png') }}" 
                                                             alt="{{ $assigner->first_name }}" class="assigner-avatar">
                                                        {{ $assigner->first_name }} {{ $assigner->last_name }}
                                                    @else
                                                        <span class="text-muted">Unknown</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    @endif
                                    
                                    <div class="memo-detail-item-inline">
                                        <label>Assigned To:</label>
                                        <span class="memo-assignee">
                                            @if($memo->currentAssignee)
                                                <img src="{{ $memo->currentAssignee->profile_picture_url ?? asset('profile_pictures/default-profile.png') }}" 
                                                     alt="{{ $memo->currentAssignee->first_name }}" class="assignee-avatar">
                                                {{ $memo->currentAssignee->first_name }} {{ $memo->currentAssignee->last_name }}
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                
                                @if($memo->message)
                                <div class="memo-details-row">
                                    <div class="memo-detail-item full-width">
                                        <label>Message:</label>
                                        <div class="memo-message-content">
                                            {!! $memo->message !!}
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                @if($memo->attachments && count($memo->attachments) > 0)
                                <div class="memo-details-row">
                                    <div class="memo-detail-item full-width">
                                        <label>Attachments:</label>
                                        <div class="memo-attachments">
                                            @foreach($memo->attachments as $index => $attachment)
                                                <div class="attachment-item">
                                                    <div class="attachment-icon">
                                                        @if(str_contains($attachment['type'], 'image'))
                                                            <i class="icofont-image"></i>
                                                        @elseif(str_contains($attachment['type'], 'pdf'))
                                                            <i class="icofont-file-pdf"></i>
                                                        @elseif(str_contains($attachment['type'], 'word'))
                                                            <i class="icofont-file-document"></i>
                                                        @elseif(str_contains($attachment['type'], 'excel') || str_contains($attachment['type'], 'spreadsheet'))
                                                            <i class="icofont-file-excel"></i>
                                                        @else
                                                            <i class="icofont-file"></i>
                                                        @endif
                                                    </div>
                                                    <div class="attachment-info">
                                                        <span class="attachment-name">{{ $attachment['name'] }}</span>
                                                        <span class="attachment-size">{{ number_format($attachment['size'] / 1024, 1) }} KB</span>
                                                    </div>
                                                    <div class="attachment-actions">
                                                        <a href="{{ route('dashboard.uimms.chat.attachment.view', ['memo' => $memo->id, 'index' => $index]) }}" 
                                                           class="attachment-view" 
                                                           target="_blank" 
                                                           title="View {{ $attachment['name'] }}">
                                                            <i class="icofont-eye"></i>
                                                        </a>
                                                        <a href="{{ route('dashboard.uimms.chat.attachment.download', ['memo' => $memo->id, 'index' => $index]) }}" 
                                                           class="attachment-download" 
                                                           title="Download {{ $attachment['name'] }}">
                                                            <i class="icofont-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Chat Messages Container --}}
                        <div class="chat-container">
                            <div class="chat-messages" id="chat-messages">
                                @foreach($memo->replies as $message)
                                    <div class="message {{ $message->user_id === auth()->id() ? 'message-sent' : 'message-received' }}">
                                        <div class="message-avatar">
                                            <img src="{{ $message->user->profile_picture_url ?? asset('profile_pictures/default-profile.png') }}" 
                                                 alt="{{ $message->user->first_name }}">
                                        </div>
                                        <div class="message-content">
                                            <div class="message-header">
                                                <div class="message-header-left">
                                                    <span class="message-sender">{{ $message->user->first_name }} {{ $message->user->last_name }}</span>
                                                    @if($message->reply_mode === 'specific' && $message->specific_recipients)
                                                        @php
                                                            $recipientNames = [];
                                                            foreach($message->specific_recipients as $recipientId) {
                                                                $participant = $memo->active_participants->firstWhere('user.id', $recipientId);
                                                                if($participant) {
                                                                    $recipientNames[] = $participant['user']['first_name'] . ' ' . $participant['user']['last_name'];
                                                                }
                                                            }
                                                        @endphp
                                                        <span class="reply-to-indicator">to {{ implode(', ', $recipientNames) }}</span>
                                                    @elseif($message->reply_mode === 'all')
                                                        @php
                                                            // Check if there are only 2 participants - if so, show specific person instead of "All"
                                                            $currentUserId = auth()->id();
                                                            $otherParticipants = $memo->active_participants->filter(function($participant) use ($currentUserId) {
                                                                return $participant['user']['id'] != $currentUserId;
                                                            });
                                                        @endphp
                                                        @if($memo->active_participants->count() <= 2 && $otherParticipants->count() == 1)
                                                            @php $otherPerson = $otherParticipants->first(); @endphp
                                                            <span class="reply-to-indicator">to {{ $otherPerson['user']['first_name'] }} {{ $otherPerson['user']['last_name'] }}</span>
                                                        @else
                                                            <span class="reply-to-indicator">to All</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="message-header-right">
                                                    <span class="message-time">{{ $message->created_at->format('M d, Y H:i') }}</span>
                                                </div>
                                            </div>
                                            <div class="message-text">{!! $message->message !!}</div>
                                            @if($message->attachments && count($message->attachments) > 0)
                                                <div class="message-attachments">
                                                    @foreach($message->attachments as $index => $attachment)
                                                        @php
                                                            $isImage = str_contains($attachment['type'] ?? '', 'image');
                                                            $isPdf = str_contains($attachment['type'] ?? '', 'pdf');
                                                            $isWord = str_contains($attachment['type'] ?? '', 'word') || str_contains($attachment['type'] ?? '', 'document');
                                                            $isExcel = str_contains($attachment['type'] ?? '', 'excel') || str_contains($attachment['type'] ?? '', 'spreadsheet');
                                                            $fileSize = isset($attachment['size']) ? number_format($attachment['size'] / 1024, 1) . ' KB' : 'Unknown size';
                                                        @endphp
                                                        
                                                        @if($isImage)
                                                            {{-- Image Attachment - Show preview --}}
                                                            <div class="attachment-image-wrapper" onclick="downloadImage('{{ route('dashboard.uimms.chat.reply.attachment.download', ['reply' => $message->id, 'index' => $index]) }}', '{{ $attachment['name'] }}')">
                                                                <img src="{{ route('dashboard.uimms.chat.reply.attachment.view', ['reply' => $message->id, 'index' => $index]) }}" 
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
                                                                <a href="{{ route('dashboard.uimms.chat.reply.attachment.download', ['reply' => $message->id, 'index' => $index]) }}" 
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
                            </div>
                        </div>

                        {{-- Chat Input --}}
                        @if($canParticipate && !in_array($memo->memo_status, ['completed', 'archived', 'suspended']) && !$isAssignedToSomeoneElse)
                        <div class="chat-input-container">
                    <!-- Reply Mode Selector -->
                    <div class="reply-mode-selector">
                        <button type="button" class="reply-mode-btn active" data-mode="all">
                            <i class="icofont-users"></i>
                            All
                        </button>
                        <button type="button" class="reply-mode-btn" data-mode="specific" id="comment-to-btn">
                            <i class="icofont-user"></i>
                            Comment-to
                        </button>
                        
                        <!-- Inline Recipients Selector (hidden by default) -->
                        <div class="inline-recipients-selector" id="inline-recipients-selector" style="display: none;">
                            <div class="recipients-dropdown">
                                <div class="recipients-input-wrapper">
                                    <input type="text" 
                                           id="recipients-search" 
                                           placeholder="Search or select recipients..." 
                                           autocomplete="off">
                                    <div class="recipients-dropdown-icon">
                                        <i class="icofont-caret-down"></i>
                                    </div>
                                </div>
                                <div class="recipients-dropdown-menu" id="recipients-dropdown-menu">
                                    <!-- Recipients will be populated by JavaScript -->
                                </div>
                            </div>
                            <div class="selected-recipients" id="selected-recipients">
                                <!-- Selected recipients will appear here -->
                            </div>
                        </div>
                    </div>
                    
                            <form id="chat-form" enctype="multipart/form-data">
                                @csrf
                        <input type="hidden" name="reply_mode" id="reply-mode" value="all">
                        <input type="hidden" name="specific_recipients" id="specific-recipients" value="">
                        
                        {{-- File Preview Area --}}
                        <div id="file-preview-area" class="file-preview-area" style="display: none;">
                            <div class="file-preview-header">
                                <span class="preview-title">Selected Files</span>
                                <button type="button" class="clear-all-files-btn" onclick="clearAllFiles()">
                                    <i class="icofont-close-line"></i> Clear All
                                </button>
                            </div>
                            <div id="file-preview-list" class="file-preview-list"></div>
                        </div>
                        
                        <div class="telegram-style-input">
                                    <button type="button" class="attachment-btn" onclick="document.getElementById('file-input').click()">
                                        <svg viewBox="0 0 24 24" class="attachment-icon">
                                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66L9.64 16.2a2 2 0 0 1-2.83-2.83l8.49-8.49"></path>
                                        </svg>
                                    </button>
                                    
                                    <div class="input-field-wrapper">
                                        <textarea id="message-input" 
                                                  name="message" 
                                                  placeholder="Minute on Memo..." 
                                                  rows="1" 
                                                  required></textarea>
                                    </div>
                                    
                                    <button type="submit" class="send-btn">
                                        <svg viewBox="0 0 24 24" class="send-icon">
                                            <line x1="22" y1="2" x2="11" y2="13"></line>
                                            <polygon points="22,2 15,22 11,13 2,9"></polygon>
                                        </svg>
                                    </button>
                                    
                                    <input type="file" id="file-input" name="attachments[]" multiple style="display: none;">
                                </div>
                            </form>
                        </div>
                        @else
                        {{-- Blocked Chat State for Inactive Participants or Completed Memos --}}
                        <div class="chat-blocked-container">
                            <div class="chat-blocked-content">
                                <div class="blocked-icon">
                                    @if($memo->memo_status === 'completed')
                                        <i class="icofont-check-circled"></i>
                                    @elseif($memo->memo_status === 'archived')
                                        <i class="icofont-archive"></i>
                                    @elseif($memo->memo_status === 'suspended')
                                        <i class="icofont-pause"></i>
                                    @else
                                        <i class="icofont-lock"></i>
                                    @endif
                                </div>
                                <div class="blocked-message">
                                    @if($memo->memo_status === 'completed')
                                        <h4>Memo Completed</h4>
                                        <p>This memo has been marked as <strong>completed</strong> and is now read-only.</p>
                                        <p class="blocked-subtitle">No further actions or messages can be added to this memo</p>
                                    @elseif($memo->memo_status === 'archived')
                                        <h4>Memo Archived</h4>
                                        <p>This memo has been <strong>archived</strong> and is now read-only.</p>
                                        <p class="blocked-subtitle">No further actions or messages can be added to this memo</p>
                                    @elseif($memo->memo_status === 'suspended')
                                        @php
                                            $suspendedBy = $memo->getSuspendedBy();
                                            $suspendedByUser = $suspendedBy ? \App\Models\User::find($suspendedBy) : null;
                                        @endphp
                                        <h4>Memo Suspended</h4>
                                        <p>This memo has been <strong>suspended</strong> by <strong>{{ $suspendedByUser ? $suspendedByUser->first_name . ' ' . $suspendedByUser->last_name : 'Unknown user' }}</strong> and messaging is temporarily disabled.</p>
                                        <p class="blocked-subtitle">Only {{ $suspendedByUser ? $suspendedByUser->first_name . ' ' . $suspendedByUser->last_name : 'the user who suspended it' }} can unsuspend this memo to resume conversation</p>
                                    @elseif($isAssignedToSomeoneElse)
                                        <h4>Memo Assigned</h4>
                                        <p>This memo has been assigned to <strong>{{ $memo->currentAssignee ? $memo->currentAssignee->first_name . ' ' . $memo->currentAssignee->last_name : 'another user' }}</strong>.</p>
                                        <p class="blocked-subtitle">You can no longer participate until it is reassigned to you</p>
                                    @else
                                        <h4>Chat Locked</h4>
                                        <p>You are not an active participant in this memo conversation.</p>
                                        <p class="blocked-subtitle">Contact the memo creator or assignee for access</p>
                                    @endif
                                </div>
                                <div class="blocked-actions">
                                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                        <i class="icofont-arrow-left"></i> Back to Portal
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Assignment Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Memo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assign-form">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign to User(s) <span class="text-muted">(Select one or more)</span></label>
                        <div class="mb-2">
                            <input type="text" id="user-search-input" class="form-control" placeholder="Search by name or email..." autocomplete="off">
                        </div>
                        <div class="border rounded p-3" id="user-list-container" style="max-height: 300px; overflow-y: auto;">
                            @foreach($users as $user)
                                <div class="form-check mb-2 user-item" data-name="{{ strtolower($user->first_name . ' ' . $user->last_name) }}" data-email="{{ strtolower($user->email) }}">
                                    <input class="form-check-input" type="checkbox" name="assignee_ids[]" value="{{ $user->id }}" id="assignee_{{ $user->id }}">
                                    <label class="form-check-label" for="assignee_{{ $user->id }}">
                                        {{ $user->first_name }} {{ $user->last_name }} <span class="text-muted">({{ $user->email }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <!-- <small class="text-muted">Select at least one user to assign the memo.</small> -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Office/Department (Optional)</label>
                        <input type="text" name="office" class="form-control" placeholder="e.g., Finance Department">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignment Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Add a message explaining the assignment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Memo</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Suspend Modal --}}
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suspend Memo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="suspend-form">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Suspension</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain why this memo is being suspended..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suspend Memo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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
    flex-wrap: wrap;
    gap: 15px;
    overflow: visible;
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-header-right {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    min-width: 0;
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
    flex-wrap: wrap;
    gap: 15px;
    overflow: hidden;
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

.chat-participants {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
    max-width: 100%;
    overflow: hidden;
}

.participant-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recipients-count {
    color: #1976d2;
    font-weight: 500;
    background: #e3f2fd;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-left: 8px;
    white-space: nowrap;
}

.memo-status {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.memo-status-separator {
    align-self: center;
}

.urgency-flag-icon {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    vertical-align: middle;
    flex-shrink: 0;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: capitalize;
    white-space: nowrap;
}

.status-pending { background: #e3f2fd; color: #1976d2; }
.status-suspended { background: #fff8e1; color: #f57c00; }
.status-completed { background: #e8f5e8; color: #388e3c; }
.status-archived { background: #f5f5f5; color: #616161; }

.chat-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.chat-actions .btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

/* Assign Button - Neumorphic Style */
.chat-actions .btn-outline-primary {
    background-color: #e4e4e4;
    border: none;
    border-radius: 10px;
    box-shadow: inset 5px 5px 5px #c4c4c4,
                inset -5px -5px 5px #ffffff;
    color: #007bff;
    cursor: pointer;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 13px;
    font-weight: bold;
    margin: 3px;
    padding: 10px 15px;
    text-transform: uppercase;
    transition: all 0.2s ease;
    text-align: center;
    min-width: 80px;
}

.chat-actions .btn-outline-primary:hover {
    background-color: #007bff;
    color: #e4e4e4;
    box-shadow: none;
}

.chat-actions .btn-outline-primary i {
    color: #007bff;
    width: 20px;
    height: 20px;
    margin-right: 5px;
    font-size: 16px;
}

.chat-actions .btn-outline-primary:hover i {
    color: #e4e4e4;
}

/* Neumorphic Button Styling for Complete, Suspend, Archive */
.chat-actions .btn-outline-success,
.chat-actions .btn-outline-warning,
.chat-actions .btn-outline-secondary,
.chat-actions .btn-outline-info {
    background-color: #e4e4e4;
    border: none;
    border-radius: 10px;
    box-shadow: inset 5px 5px 5px #c4c4c4,
                inset -5px -5px 5px #ffffff;
    color: #333;
    cursor: pointer;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 13px;
    font-weight: bold;
    margin: 3px;
    padding: 10px 15px;
    text-transform: uppercase;
    transition: all 0.2s ease;
    text-align: center;
    min-width: 80px;
}

.chat-actions .btn-outline-success:hover,
.chat-actions .btn-outline-warning:hover,
.chat-actions .btn-outline-secondary:hover,
.chat-actions .btn-outline-info:hover {
    box-shadow: none;
}

.chat-actions .btn-outline-success i,
.chat-actions .btn-outline-warning i,
.chat-actions .btn-outline-secondary i,
.chat-actions .btn-outline-info i {
    width: 20px;
    height: 20px;
    margin-right: 5px;
    font-size: 16px;
}

/* Complete Button */
.chat-actions .btn-outline-success {
    color: #28a745;
}

.chat-actions .btn-outline-success:hover {
    background-color: #28a745;
    color: #e4e4e4;
}

.chat-actions .btn-outline-success i {
    color: #28a745;
}

.chat-actions .btn-outline-success:hover i {
    color: #e4e4e4;
}

/* Suspend Button */
.chat-actions .btn-outline-warning {
    color: #ffc107;
}

.chat-actions .btn-outline-warning:hover {
    background-color: #ffc107;
    color: #e4e4e4;
}

.chat-actions .btn-outline-warning i {
    color: #ffc107;
}

.chat-actions .btn-outline-warning:hover i {
    color: #e4e4e4;
}

/* Archive Button */
.chat-actions .btn-outline-secondary {
    color: #6c757d;
}

.chat-actions .btn-outline-secondary:hover {
    background-color: #6c757d;
    color: #e4e4e4;
}

.chat-actions .btn-outline-secondary i {
    color: #6c757d;
}

.chat-actions .btn-outline-secondary:hover i {
    color: #e4e4e4;
}

/* Keep in View (K.I.V) Button */
.chat-actions .btn-outline-info {
    color: #17a2b8;
}

.chat-actions .btn-outline-info:hover {
    background-color: #17a2b8;
    color: #e4e4e4;
}

.chat-actions .btn-outline-info i,
.chat-actions .btn-outline-info img {
    color: #17a2b8;
    width: 20px;
    height: 20px;
    margin-right: 5px;
    object-fit: contain;
}

.chat-actions .btn-outline-info:hover i {
    color: #e4e4e4;
}

.chat-actions .btn-outline-info:hover img {
    filter: brightness(0) invert(1);
}

/* Responsive Button Styling for Back Button */
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

/* Header Separator */
.header-separator {
    width: 1px;
    height: 30px;
    background: #ddd;
    margin: 0 12px;
    opacity: 0.6;
}

/* Export Button */
.export-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.export-btn:hover {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
    box-shadow: 2px 2px 12px rgba(40, 167, 69, 0.3);
}

/* ============================================
   RESPONSIVE DESIGN - MOBILE FIRST APPROACH
   ============================================ */

/* Mobile (0px - 767px) - Base styles */
.chat-header-top {
    padding: 10px 10px 6px 10px;
}

.chat-header-left {
    flex-wrap: wrap;
}

.chat-actions {
    gap: 3px;
}

.chat-actions .btn-outline-primary,
.chat-actions .btn-outline-success,
.chat-actions .btn-outline-warning,
.chat-actions .btn-outline-secondary,
.chat-actions .btn-outline-info {
    padding: 5px 6px;
    font-size: 9px;
    min-width: 50px;
    margin: 1px;
}

.chat-actions .btn-outline-primary i,
.chat-actions .btn-outline-success i,
.chat-actions .btn-outline-warning i,
.chat-actions .btn-outline-secondary i,
.chat-actions .btn-outline-info i,
.chat-actions .btn-outline-info img {
    width: 12px;
    height: 12px;
    font-size: 11px;
    margin-right: 2px;
}

.btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
}

.chat-header-bottom {
    padding: 8px 10px 10px 10px;
}

.chat-title h4 {
    font-size: 0.9rem;
}

.participant-avatar {
    width: 20px;
    height: 20px;
}

.recipients-count {
    font-size: 0.6rem;
    padding: 2px 4px;
}

/* Tablet - 768px to 1199px */
@media (min-width: 768px) {
    .chat-header-top {
        padding: 12px 12px 8px 12px;
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .chat-header-left {
        width: 100%;
        justify-content: flex-start;
        gap: 8px;
    }
    
    .chat-header-right {
        width: 100%;
        justify-content: flex-start;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        display: flex;
        flex-direction: row;
    }
    
    .memo-status {
        order: 0;
        flex-shrink: 0;
    }
    
    .chat-actions {
        order: 0;
        width: auto;
        justify-content: flex-start;
        gap: 4px;
        flex-wrap: wrap;
        margin-top: 0;
        margin-left: 8px;
    }
    
    .chat-actions .btn-outline-primary,
    .chat-actions .btn-outline-success,
    .chat-actions .btn-outline-warning,
    .chat-actions .btn-outline-secondary,
    .chat-actions .btn-outline-info {
        padding: 6px 8px;
        font-size: 10px;
        min-width: 55px;
        margin: 2px;
    }
    
    .chat-actions .btn-outline-primary i,
    .chat-actions .btn-outline-success i,
    .chat-actions .btn-outline-warning i,
    .chat-actions .btn-outline-secondary i,
    .chat-actions .btn-outline-info i,
    .chat-actions .btn-outline-info img {
        width: 14px;
        height: 14px;
        font-size: 12px;
        margin-right: 3px;
    }
    
    .responsive-btn {
        padding: 6px 10px;
        min-width: 60px;
    }
    
    .responsive-btn .text {
        font-size: 11px;
    }
    
    .header-separator {
        height: 20px;
        margin: 0 6px;
    }
    
    .status-badge {
        padding: 4px 10px;
        font-size: 0.75rem;
    }
    
    .chat-header-bottom {
        padding: 10px 12px 12px 12px;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .chat-title {
        width: 100%;
    }
    
    .chat-title h4 {
        font-size: 0.95rem;
    }
    
    .chat-participants {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    
    .chat-participants::-webkit-scrollbar {
        height: 4px;
    }
    
    .chat-participants::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;
    }
    
    .participant-avatar {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
    }
    
    .recipients-count {
        font-size: 0.65rem;
        padding: 2px 5px;
        flex-shrink: 0;
    }
}

/* Desktop - 1200px to 1599px */
@media (min-width: 1200px) {
    .chat-header-top {
        padding: 15px 15px 10px 15px;
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .chat-header-left {
        width: 100%;
        gap: 10px;
        flex-shrink: 0;
        justify-content: flex-start;
    }
    
    .chat-header-right {
        width: 100%;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: center;
        min-width: 0;
        display: flex;
        flex-direction: row;
    }
    
    .memo-status {
        order: 0;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .memo-status-separator {
        height: 14px;
        margin: 0 4px;
    }
    
    .urgency-flag-icon {
        font-size: 0.95rem !important;
        padding: 3px !important;
    }
    
    .chat-actions {
        order: 0;
        width: auto;
        gap: 5px;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: center;
        margin-top: 0;
        margin-left: 10px;
    }
    
    .chat-actions .btn-outline-primary,
    .chat-actions .btn-outline-success,
    .chat-actions .btn-outline-warning,
    .chat-actions .btn-outline-secondary,
    .chat-actions .btn-outline-info {
        padding: 7px 10px;
        font-size: 11px;
        min-width: 60px;
        margin: 2px;
    }
    
    .chat-actions .btn-outline-primary i,
    .chat-actions .btn-outline-success i,
    .chat-actions .btn-outline-warning i,
    .chat-actions .btn-outline-secondary i,
    .chat-actions .btn-outline-info i,
    .chat-actions .btn-outline-info img {
        width: 16px;
        height: 16px;
        font-size: 14px;
        margin-right: 4px;
    }
    
    .responsive-btn {
        padding: 6px 12px;
        min-width: 70px;
    }
    
    .responsive-btn .text {
        font-size: 12px;
    }
    
    .header-separator {
        height: 24px;
        margin: 0 8px;
    }
    
    .chat-header-bottom {
        padding: 12px 15px 15px 15px;
        flex-direction: row;
    }
    
    .chat-title {
        flex: 1;
        min-width: 0;
        width: auto;
    }
    
    .chat-title h4 {
        font-size: 1rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .chat-participants {
        max-width: 50%;
        flex-shrink: 1;
        width: auto;
    }
    
    .participant-avatar {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }
    
    .recipients-count {
        font-size: 0.7rem;
        padding: 3px 6px;
        white-space: nowrap;
    }
}

/* Large screens - 1600px and above */
@media (min-width: 1600px) {
    .chat-header-top {
        padding: 20px 20px 15px 20px;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
    
    .chat-header-left {
        width: auto;
        justify-content: flex-start;
    }
    
    .chat-header-right {
        width: auto;
        flex: 1;
        justify-content: flex-end;
        align-items: center;
    }
    
    .memo-status {
        align-self: center;
    }
    
    .chat-actions {
        gap: 8px;
        justify-content: flex-end;
        align-self: center;
        width: auto;
    }
    
    .chat-actions .btn-outline-primary,
    .chat-actions .btn-outline-success,
    .chat-actions .btn-outline-warning,
    .chat-actions .btn-outline-secondary,
    .chat-actions .btn-outline-info {
        padding: 10px 15px;
        font-size: 13px;
        min-width: 80px;
    }
    
    .chat-actions .btn-outline-primary i,
    .chat-actions .btn-outline-success i,
    .chat-actions .btn-outline-warning i,
    .chat-actions .btn-outline-secondary i,
    .chat-actions .btn-outline-info i,
    .chat-actions .btn-outline-info img {
        width: 20px;
        height: 20px;
        font-size: 16px;
        margin-right: 5px;
    }
    
    .chat-header-bottom {
        padding: 15px 20px 20px 20px;
    }
    
    .chat-title h4 {
        font-size: 1.1rem;
    }
    
    .participant-avatar {
        width: 28px;
        height: 28px;
    }
    
    .recipients-count {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
}

/* Memo Details Section */
.memo-details-section {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
}


.memo-details-content {
    padding: 20px;
}

.memo-details-row {
    display: flex;
    gap: 30px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.memo-details-row:last-child {
    margin-bottom: 0;
}

.memo-details-single-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.memo-detail-item-inline {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.memo-detail-item-inline label {
    display: inline-block;
    font-weight: 600;
    color: #2c3e50;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    font-size: 0.75rem;
    margin-bottom: 0;
    margin-right: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 8px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.memo-detail-item {
    flex: 1;
    min-width: 200px;
}

.memo-detail-item.full-width {
    flex: 100%;
    min-width: 100%;
}

.memo-detail-item label {
    display: inline-block; /* Badge style for all labels */
    font-weight: 600;
    color: #2c3e50;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    font-size: 0.75rem;
    margin-bottom: 5px;
    margin-right: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 4px 8px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.memo-detail-item.inline-label label {
    margin-bottom: 0;
}

.memo-subject {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    line-height: 1.4;
}

.memo-sender, .memo-assignee {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    color: #333;
}

.sender-avatar, .assigner-avatar, .assignee-avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}

.memo-date {
    color: #666;
    font-size: 0.9rem;
}

.memo-recipients-count {
    color: #1976d2;
    font-weight: 500;
    background: #e3f2fd;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
}

.memo-message-content {
    background: #F0F7FF;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 15px;
    color: #333;
    line-height: 1.6;
    font-size: 0.95rem;
}

.memo-attachments {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.attachment-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.attachment-item:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.attachment-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1976d2;
    color: white;
    border-radius: 8px;
    font-size: 1.2rem;
}

.attachment-info {
    flex: 1;
    min-width: 0;
}

.attachment-name {
    display: block;
    font-weight: 500;
    color: #333;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.attachment-size {
    display: block;
    color: #666;
    font-size: 0.8rem;
    margin-top: 2px;
}

.attachment-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.attachment-view,
.attachment-download {
    color: #1976d2;
    font-size: 1.1rem;
    padding: 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.attachment-view:hover,
.attachment-download:hover {
    background: #e3f2fd;
    color: #1565c0;
}

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
    align-items: flex-start;
    margin-bottom: 5px;
    font-size: 0.8rem;
    opacity: 0.8;
    flex-wrap: wrap;
    gap: 8px;
}

.message-header-left {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 4px;
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.message-sender {
    white-space: nowrap;
    flex-shrink: 0;
}

.message-header-right {
    display: flex;
    align-items: center;
    flex-shrink: 0;
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

/* WhatsApp-style Image Attachment */
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
    font-size: 2rem;
}

/* WhatsApp-style File Attachment */
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

.file-icon .icofont-file-pdf {
    color: #dc3545;
}

.file-icon .icofont-file-document {
    color: #0066cc;
}

.file-icon .icofont-file-excel {
    color: #28a745;
}

.file-icon .icofont-file-alt {
    color: #6c757d;
}

.file-info {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-size: 0.9rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 2px;
}

.file-size {
    font-size: 0.75rem;
    opacity: 0.7;
}

.file-download-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    color: inherit;
    text-decoration: none;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.file-download-btn:hover {
    background: rgba(0, 0, 0, 0.2);
    transform: scale(1.1);
}

.file-download-btn i {
    font-size: 1.1rem;
}

.attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    background: rgba(255,255,255,0.2);
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.85rem;
}

.chat-input-container {
    background: #f8f9fa;
    border: none;
    border-radius: 0 0 8px 8px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.chat-input-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text x="10" y="20" font-size="12" fill="rgba(0,0,0,0.03)">x² + y² = r²</text><text x="60" y="40" font-size="10" fill="rgba(0,0,0,0.02)">∫f(x)dx</text><text x="20" y="70" font-size="14" fill="rgba(0,0,0,0.02)">α + β = γ</text><text x="70" y="90" font-size="8" fill="rgba(0,0,0,0.01)">∂/∂x</text></svg>');
    opacity: 0.5;
    pointer-events: none;
}

/* Blocked Chat State Styles */
.chat-blocked-container {
    background: #f8f9fa;
    border: none;
    border-radius: 0 0 8px 8px;
    padding: 40px 20px;
    text-align: center;
    border-top: 1px solid #e9ecef;
}

.chat-blocked-content {
    max-width: 400px;
    margin: 0 auto;
}

.blocked-icon {
    font-size: 3rem;
    color: #6c757d;
    margin-bottom: 20px;
}

.blocked-message h4 {
    color: #495057;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 15px;
}

.blocked-message p {
    color: #6c757d;
    font-size: 1rem;
    line-height: 1.5;
    margin-bottom: 10px;
}

.blocked-subtitle {
    font-size: 0.9rem;
    color: #868e96;
    font-style: italic;
}

.blocked-actions {
    margin-top: 25px;
}

.blocked-actions .btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.blocked-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Reply Mode Selector */
.reply-mode-selector {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
    flex-wrap: wrap;
}

.reply-mode-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 20px;
    color: #333;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.reply-mode-btn:hover {
    background: #e3f2fd;
    border-color: #1976d2;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.reply-mode-btn.active {
    background: #1976d2;
    color: white;
    border-color: #1976d2;
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
}

.reply-mode-btn i {
    font-size: 0.9rem;
}

.reply-mode-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #f5f5f5;
    color: #999;
    border-color: #ddd;
    pointer-events: none;
}

.reply-mode-btn.disabled:hover {
    background: #f5f5f5;
    color: #999;
    border-color: #ddd;
    transform: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Inline Recipients Selector */
.inline-recipients-selector {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.recipients-dropdown {
    position: relative;
    min-width: 250px;
}

.recipients-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.recipients-input-wrapper input {
    width: 100%;
    padding: 8px 35px 8px 12px;
    border: 2px solid #e3f2fd;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.95);
    font-size: 0.85rem;
    color: #333;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.recipients-input-wrapper input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
    background: white;
}

.recipients-dropdown-icon {
    position: absolute;
    right: 10px;
    color: #666;
    pointer-events: none;
    transition: transform 0.3s ease;
}

.recipients-dropdown.active .recipients-dropdown-icon {
    transform: rotate(180deg);
}

.recipients-dropdown-menu {
    position: fixed;
    background: white;
    border: 1px solid #e3f2fd;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 9999;
    display: none;
    width: 320px;
}


.recipients-dropdown.active .recipients-dropdown-menu {
    display: block;
    animation: slideDown 0.2s ease-out;
}


@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}


.recipient-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f5f5f5;
    min-height: 50px;
    width: 100%;
    box-sizing: border-box;
}

.recipient-option:last-child {
    border-bottom: none;
}

.recipient-option:hover {
    background: #f8f9ff;
}

.recipient-option.selected {
    background: #e3f2fd;
    color: #1976d2;
}

.recipient-option img {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.recipient-option .recipient-info {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.recipient-option .recipient-name {
    font-weight: 500;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

.recipient-option .recipient-email {
    font-size: 0.75rem;
    color: #666;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}

.recipient-option .recipient-check {
    color: #1976d2;
    font-size: 0.9rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.recipient-option.selected .recipient-check {
    opacity: 1;
}

/* Selected Recipients Display */
.selected-recipients {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    max-width: 300px;
}

.selected-recipient-tag {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 8px;
    background: #1976d2;
    color: white;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 500;
    animation: slideIn 0.2s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.selected-recipient-tag img {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.selected-recipient-tag .remove-recipient {
    cursor: pointer;
    margin-left: 4px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.selected-recipient-tag .remove-recipient:hover {
    opacity: 1;
}

/* Reply-to Indicator */
.reply-to-indicator {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-left: 4px;
    border: 1px solid #bbdefb;
    display: inline-block;
    white-space: nowrap;
    flex-shrink: 0;
}

/* File Preview Area */
.file-preview-area {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 12px;
}

.file-preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
}

.preview-title {
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.clear-all-files-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
}

.clear-all-files-btn:hover {
    background: #c82333;
    transform: scale(1.05);
}

.file-preview-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.file-preview-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.file-preview-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.file-preview-icon.image {
    background: #e7f3ff;
    color: #0066cc;
}

.file-preview-icon.pdf {
    background: #ffe7e7;
    color: #dc3545;
}

.file-preview-icon.document {
    background: #e7f0ff;
    color: #0066cc;
}

.file-preview-icon.spreadsheet {
    background: #e7ffe7;
    color: #28a745;
}

.file-preview-icon.other {
    background: #f0f0f0;
    color: #6c757d;
}

.file-preview-info {
    flex: 1;
    min-width: 0;
}

.file-preview-name {
    font-size: 0.9rem;
    font-weight: 500;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-preview-size {
    font-size: 0.75rem;
    color: #6c757d;
}

.file-preview-remove {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(220, 53, 69, 0.1);
    border: none;
    border-radius: 50%;
    color: #dc3545;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.file-preview-remove:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
}

.telegram-style-input {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    padding: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.attachment-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #6c757d;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: none;
    margin-right: 8px;
}

.attachment-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.attachment-btn .attachment-icon {
    width: 20px;
    height: 20px;
    stroke: white;
    stroke-width: 2;
    fill: none;
    background: none !important;
}

.input-field-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    background: transparent;
    border-radius: 20px;
    padding: 8px 12px;
    position: relative;
}

.input-field-wrapper textarea {
    flex: 1;
    border: none;
    background: transparent;
    resize: none;
    outline: none;
    font-size: 14px;
    line-height: 1.4;
    max-height: 120px;
    min-height: 20px;
    color: #333;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.input-field-wrapper textarea::placeholder {
    color: #666;
}


.send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.3);
    margin-left: 8px;
}

.send-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(25, 118, 210, 0.4);
    background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
}

.send-icon {
    width: 20px;
    height: 20px;
    stroke: white;
    stroke-width: 2;
    fill: none;
}

.typing-indicator {
    padding: 10px 20px;
    font-style: italic;
    color: #666;
    font-size: 0.9rem;
}
</style>

<script>
const memoId = {{ $memo->id }};
let messageInterval;

// Auto-resize textarea
document.getElementById('message-input').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Reply Mode Functionality
let selectedRecipients = [];
let memoParticipants = [];

// Initialize reply mode functionality
function initializeReplyMode() {
    // Get memo participants from the page data
    memoParticipants = @json($memo->active_participants ?? []);
    
    // Fallback: if active_participants is empty, try to get from recipients
    if (!memoParticipants || memoParticipants.length === 0) {
        memoParticipants = @json($memo->recipients ?? []);
        
        // Transform recipients to match expected format
        if (memoParticipants && memoParticipants.length > 0) {
            memoParticipants = memoParticipants.map(recipient => ({
                user: recipient.user,
                is_active_participant: true
            }));
        }
    }
    
    // Check if comment-to button should be disabled (only 2 participants)
    checkCommentToButtonAvailability();
    
    // Set up reply mode button handlers
    document.querySelectorAll('.reply-mode-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mode = this.dataset.mode;
            setReplyMode(mode);
        });
    });
    
    // Populate recipients list
    populateRecipientsList();
}

function checkCommentToButtonAvailability() {
    const commentToBtn = document.getElementById('comment-to-btn');
    const currentUserId = {{ Auth::id() }};
    
    // Count participants excluding current user
    const otherParticipants = memoParticipants.filter(participant => 
        participant.user && participant.user.id !== currentUserId
    );
    
    // If there are only 2 participants total (current user + 1 other), disable comment-to
    if (memoParticipants.length <= 2) {
        commentToBtn.disabled = true;
        commentToBtn.classList.add('disabled');
        commentToBtn.title = 'Comment-to is not available with only 2 participants';
        
        // If comment-to was active, switch to "All" mode
        if (commentToBtn.classList.contains('active')) {
            setReplyMode('all');
        }
    } else {
        commentToBtn.disabled = false;
        commentToBtn.classList.remove('disabled');
        commentToBtn.title = '';
    }
}

function setReplyMode(mode) {
    // Prevent switching to 'specific' mode if comment-to button is disabled
    if (mode === 'specific') {
        const commentToBtn = document.getElementById('comment-to-btn');
        if (commentToBtn && commentToBtn.disabled) {
            return; // Don't allow switching to specific mode
        }
    }
    
    // Update button states
    document.querySelectorAll('.reply-mode-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-mode="${mode}"]`).classList.add('active');
    
    // Update hidden input
    document.getElementById('reply-mode').value = mode;
    
    // Show/hide inline recipients selector
    const inlineRecipientsSelector = document.getElementById('inline-recipients-selector');
    if (mode === 'specific') {
        inlineRecipientsSelector.style.display = 'flex';
        // Focus on the search input
        setTimeout(() => {
            document.getElementById('recipients-search').focus();
        }, 100);
    } else {
        inlineRecipientsSelector.style.display = 'none';
        selectedRecipients = [];
        updateSpecificRecipientsInput();
        updateSelectedRecipientsDisplay();
    }
}

function populateRecipientsList() {
    const dropdownMenu = document.getElementById('recipients-dropdown-menu');
    dropdownMenu.innerHTML = '';
    
    memoParticipants.forEach(participant => {
        if (participant.user && participant.user.id !== {{ Auth::id() }}) {
            const recipientOption = document.createElement('div');
            recipientOption.className = 'recipient-option';
            recipientOption.dataset.userId = participant.user.id;
            recipientOption.innerHTML = `
                <img src="${participant.user.profile_picture_url || '/profile_pictures/default-profile.png'}" 
                     alt="${participant.user.first_name}">
                <div class="recipient-info">
                    <div class="recipient-name">${participant.user.first_name} ${participant.user.last_name}</div>
                    <div class="recipient-email">${participant.user.email || ''}</div>
                </div>
                <i class="icofont-check recipient-check"></i>
            `;
            
            recipientOption.addEventListener('click', function() {
                toggleRecipientSelection(participant.user.id, this);
            });
            
            dropdownMenu.appendChild(recipientOption);
        }
    });
}

function toggleRecipientSelection(userId, element) {
    const index = selectedRecipients.indexOf(userId);
    if (index > -1) {
        selectedRecipients.splice(index, 1);
        element.classList.remove('selected');
    } else {
        selectedRecipients.push(userId);
        element.classList.add('selected');
    }
    updateSpecificRecipientsInput();
    updateSelectedRecipientsDisplay();
}

function updateSpecificRecipientsInput() {
    document.getElementById('specific-recipients').value = selectedRecipients.join(',');
}

function updateSelectedRecipientsDisplay() {
    const selectedRecipientsContainer = document.getElementById('selected-recipients');
    selectedRecipientsContainer.innerHTML = '';
    
    selectedRecipients.forEach(userId => {
        const participant = memoParticipants.find(p => p.user && p.user.id == userId);
        if (participant) {
            const tag = document.createElement('div');
            tag.className = 'selected-recipient-tag';
            tag.innerHTML = `
                <img src="${participant.user.profile_picture_url || '/profile_pictures/default-profile.png'}" 
                     alt="${participant.user.first_name}">
                <span>${participant.user.first_name}</span>
                <i class="icofont-close remove-recipient" onclick="removeRecipient(${userId})"></i>
            `;
            selectedRecipientsContainer.appendChild(tag);
        }
    });
}

function removeRecipient(userId) {
    const index = selectedRecipients.indexOf(userId);
    if (index > -1) {
        selectedRecipients.splice(index, 1);
        updateSpecificRecipientsInput();
        updateSelectedRecipientsDisplay();
        
        // Update dropdown option
        const option = document.querySelector(`[data-user-id="${userId}"]`);
        if (option) {
            option.classList.remove('selected');
        }
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeReplyMode();
    initializeDropdownFunctionality();
});

function initializeDropdownFunctionality() {
    const searchInput = document.getElementById('recipients-search');
    const dropdown = document.querySelector('.recipients-dropdown');
    const dropdownMenu = document.getElementById('recipients-dropdown-menu');
    
    // Simple positioning function - only positions below
    function positionDropdown() {
        const inputRect = searchInput.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const dropdownWidth = 320; // fixed width from CSS
        
        // Calculate horizontal position (center align with input)
        let left = inputRect.left;
        let right = 'auto';
        
        // Adjust if dropdown would go off screen
        if (left + dropdownWidth > viewportWidth) {
            left = 'auto';
            right = viewportWidth - inputRect.right;
        }
        
        // Set horizontal position
        dropdownMenu.style.left = typeof left === 'number' ? left + 'px' : left;
        dropdownMenu.style.right = right;
        dropdownMenu.style.width = dropdownWidth + 'px';
        
        // Always position below the input
        dropdownMenu.style.top = (inputRect.bottom + 4) + 'px';
    }
    
    // Toggle dropdown on input focus/click
    searchInput.addEventListener('focus', function() {
        dropdown.classList.add('active');
        populateRecipientsList();
        // Position dropdown after a short delay to ensure it's rendered
        setTimeout(positionDropdown, 10);
    });
    
    searchInput.addEventListener('click', function() {
        dropdown.classList.add('active');
        populateRecipientsList();
        setTimeout(positionDropdown, 10);
    });
    
    // Reposition on window resize
    window.addEventListener('resize', function() {
        if (dropdown.classList.contains('active')) {
            positionDropdown();
        }
    });
    
    // Reposition on scroll (since we're using fixed positioning)
    window.addEventListener('scroll', function() {
        if (dropdown.classList.contains('active')) {
            positionDropdown();
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = dropdownMenu.querySelectorAll('.recipient-option');
        
        options.forEach(option => {
            const name = option.querySelector('.recipient-name').textContent.toLowerCase();
            const email = option.querySelector('.recipient-email').textContent.toLowerCase();
            
            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                option.style.display = 'flex';
            } else {
                option.style.display = 'none';
            }
        });
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });
    
    // Prevent dropdown from closing when clicking inside
    dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
}

// Send message
// File Handling
let selectedFiles = [];

document.getElementById('file-input').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    files.forEach(file => {
        if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.push(file);
        }
    });
    updateFilePreview();
});

function updateFilePreview() {
    const previewArea = document.getElementById('file-preview-area');
    const previewList = document.getElementById('file-preview-list');
    
    if (selectedFiles.length === 0) {
        previewArea.style.display = 'none';
        return;
    }
    
    previewArea.style.display = 'block';
    previewList.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const fileType = getFileType(file);
        const fileSize = formatFileSize(file.size);
        
        const fileItem = document.createElement('div');
        fileItem.className = 'file-preview-item';
        fileItem.innerHTML = `
            <div class="file-preview-icon ${fileType}">
                ${getFileIcon(fileType)}
            </div>
            <div class="file-preview-info">
                <div class="file-preview-name">${file.name}</div>
                <div class="file-preview-size">${fileSize}</div>
            </div>
            <button type="button" class="file-preview-remove" onclick="removeFile(${index})">
                <i class="icofont-close"></i>
            </button>
        `;
        previewList.appendChild(fileItem);
    });
}

function getFileType(file) {
    if (file.type.startsWith('image/')) return 'image';
    if (file.type.includes('pdf')) return 'pdf';
    if (file.type.includes('word') || file.type.includes('document')) return 'document';
    if (file.type.includes('sheet') || file.type.includes('excel')) return 'spreadsheet';
    return 'other';
}

function getFileIcon(type) {
    const icons = {
        image: '<i class="icofont-image"></i>',
        pdf: '<i class="icofont-file-pdf"></i>',
        document: '<i class="icofont-file-document"></i>',
        spreadsheet: '<i class="icofont-file-excel"></i>',
        other: '<i class="icofont-file-alt"></i>'
    };
    return icons[type] || icons.other;
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFilePreview();
}

function clearAllFiles() {
    selectedFiles = [];
    document.getElementById('file-input').value = '';
    updateFilePreview();
}

document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('message-input');
    
    if (!messageInput.value.trim() && selectedFiles.length === 0) return;
    
    // Prevent double submission
    if (isSendingMessage) return;
    
    // Set flag to prevent auto-refresh during message sending
    isSendingMessage = true;
    
    // Create FormData manually to avoid duplicate files
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('message', messageInput.value);
    formData.append('reply_mode', document.getElementById('reply-mode').value);
    formData.append('specific_recipients', document.getElementById('specific-recipients').value);
    
    // Add selected files to formData
    selectedFiles.forEach((file, index) => {
        formData.append('attachments[]', file);
    });
    
    // Show typing indicator
    showTypingIndicator();
    
    fetch(`/dashboard/uimms/chat/${memoId}/message`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addMessageToChat(data.message);
            messageInput.value = '';
            messageInput.style.height = 'auto';
            clearAllFiles();
            hideTypingIndicator();
            
            // Update counters to prevent duplicate detection
            lastMessageCount++;
            lastMessageId = data.message.id;
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        hideTypingIndicator();
        alert('Error sending message. Please try again.');
    })
    .finally(() => {
        // Re-enable auto-refresh after a short delay
        setTimeout(() => {
            isSendingMessage = false;
        }, 2000);
    });
});

// Add message to chat (for sent messages)
function addMessageToChat(message) {
    const messagesContainer = document.getElementById('chat-messages');
    
    // Determine reply mode display
    let replyModeDisplay = '';
    if (message.reply_mode === 'specific' && message.specific_recipients) {
        // Handle different data types for specific_recipients
        let recipientIds = [];
        try {
            if (typeof message.specific_recipients === 'string') {
                recipientIds = message.specific_recipients.split(',');
            } else if (Array.isArray(message.specific_recipients)) {
                recipientIds = message.specific_recipients;
            } else {
                console.warn('Unexpected specific_recipients type:', typeof message.specific_recipients, message.specific_recipients);
                recipientIds = [];
            }
            
            const recipientNames = recipientIds.map(id => {
                const participant = memoParticipants.find(p => p.user && p.user.id == id);
                return participant ? `${participant.user.first_name} ${participant.user.last_name}` : 'Unknown';
            });
            replyModeDisplay = `<span class="reply-to-indicator">to ${recipientNames.join(', ')}</span>`;
        } catch (error) {
            console.error('Error processing specific recipients:', error, message.specific_recipients);
            replyModeDisplay = `<span class="reply-to-indicator">to Specific</span>`;
        }
    } else if (message.reply_mode === 'all') {
        // Check if there are only 2 participants - if so, show specific person instead of "All"
        const currentUserId = {{ Auth::id() }};
        const otherParticipants = memoParticipants.filter(participant => 
            participant.user && participant.user.id !== currentUserId
        );
        
        if (memoParticipants.length <= 2 && otherParticipants.length === 1) {
            // Only 2 people total, show the other person's name
            const otherPerson = otherParticipants[0];
            replyModeDisplay = `<span class="reply-to-indicator">to ${otherPerson.user.first_name} ${otherPerson.user.last_name}</span>`;
        } else {
            // More than 2 people, show "to All"
            replyModeDisplay = `<span class="reply-to-indicator">to All</span>`;
        }
    }
    
    // Build attachments HTML if any
    let attachmentsHtml = '';
    if (message.attachments && message.attachments.length > 0) {
        attachmentsHtml = '<div class="message-attachments">';
        message.attachments.forEach((attachment, index) => {
            const isImage = attachment.type && attachment.type.includes('image');
            const isPdf = attachment.type && attachment.type.includes('pdf');
            const isWord = attachment.type && (attachment.type.includes('word') || attachment.type.includes('document'));
            const isExcel = attachment.type && (attachment.type.includes('excel') || attachment.type.includes('spreadsheet'));
            const fileSize = attachment.size ? (attachment.size / 1024).toFixed(1) + ' KB' : 'Unknown size';
            
            if (isImage) {
                attachmentsHtml += `
                    <div class="attachment-image-wrapper" onclick="downloadImage('/dashboard/uimms/chat/reply/${message.id}/attachment/${index}/download', '${attachment.name}')">
                        <img src="/dashboard/uimms/chat/reply/${message.id}/attachment/${index}/view" 
                             alt="${attachment.name}"
                             class="attachment-image">
                        <div class="image-overlay">
                            <i class="icofont-download"></i>
                        </div>
                    </div>
                `;
            } else {
                attachmentsHtml += `
                    <div class="attachment-file-card">
                        <div class="file-icon">
                            ${isPdf ? '<i class="icofont-file-pdf"></i>' : 
                              isWord ? '<i class="icofont-file-document"></i>' :
                              isExcel ? '<i class="icofont-file-excel"></i>' : 
                              '<i class="icofont-file-alt"></i>'}
                        </div>
                        <div class="file-info">
                            <div class="file-name">${attachment.name}</div>
                            <div class="file-size">${fileSize}</div>
                        </div>
                        <a href="/dashboard/uimms/chat/reply/${message.id}/attachment/${index}/download" 
                           class="file-download-btn" title="Download">
                            <i class="icofont-download"></i>
                        </a>
                    </div>
                `;
            }
        });
        attachmentsHtml += '</div>';
    }
    
    const messageHtml = `
        <div class="message message-sent">
            <div class="message-avatar">
                <img src="${message.user.profile_picture_url || '/profile_pictures/default-profile.png'}" 
                     alt="${message.user.first_name}">
            </div>
            <div class="message-content">
                <div class="message-header">
                    <div class="message-header-left">
                        <span class="message-sender">${message.user.first_name} ${message.user.last_name}</span>
                        ${replyModeDisplay}
                    </div>
                    <div class="message-header-right">
                        <span class="message-time">${new Date(message.created_at).toLocaleString()}</span>
                    </div>
                </div>
                <div class="message-text">${message.message}</div>
                ${attachmentsHtml}
            </div>
        </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    scrollToBottom();
}

// Add new message to chat (for received messages with animation)
function addNewMessageToChat(message) {
    const messagesContainer = document.getElementById('chat-messages');
    const currentUserId = {{ Auth::id() }};
    const isOwnMessage = message.user_id === currentUserId;
    
    // Determine reply mode display
    let replyModeDisplay = '';
    if (message.reply_mode === 'specific' && message.specific_recipients) {
        // Handle different data types for specific_recipients
        let recipientIds = [];
        try {
            if (typeof message.specific_recipients === 'string') {
                recipientIds = message.specific_recipients.split(',');
            } else if (Array.isArray(message.specific_recipients)) {
                recipientIds = message.specific_recipients;
            } else {
                console.warn('Unexpected specific_recipients type:', typeof message.specific_recipients, message.specific_recipients);
                recipientIds = [];
            }
            
            const recipientNames = recipientIds.map(id => {
                const participant = memoParticipants.find(p => p.user && p.user.id == id);
                return participant ? `${participant.user.first_name} ${participant.user.last_name}` : 'Unknown';
            });
            replyModeDisplay = `<span class="reply-to-indicator">to ${recipientNames.join(', ')}</span>`;
        } catch (error) {
            console.error('Error processing specific recipients:', error, message.specific_recipients);
            replyModeDisplay = `<span class="reply-to-indicator">to Specific</span>`;
        }
    } else if (message.reply_mode === 'all') {
        // Check if there are only 2 participants - if so, show specific person instead of "All"
        const otherParticipants = memoParticipants.filter(participant => 
            participant.user && participant.user.id !== currentUserId
        );
        
        if (memoParticipants.length <= 2 && otherParticipants.length === 1) {
            // Only 2 people total, show the other person's name
            const otherPerson = otherParticipants[0];
            replyModeDisplay = `<span class="reply-to-indicator">to ${otherPerson.user.first_name} ${otherPerson.user.last_name}</span>`;
        } else {
            // More than 2 people, show "to All"
            replyModeDisplay = `<span class="reply-to-indicator">to All</span>`;
        }
    }
    
    // Build attachments HTML if any
    let attachmentsHtml = '';
    if (message.attachments && message.attachments.length > 0) {
        attachmentsHtml = '<div class="message-attachments">';
        message.attachments.forEach((attachment, index) => {
            const isImage = attachment.type && attachment.type.includes('image');
            const isPdf = attachment.type && attachment.type.includes('pdf');
            const isWord = attachment.type && (attachment.type.includes('word') || attachment.type.includes('document'));
            const isExcel = attachment.type && (attachment.type.includes('excel') || attachment.type.includes('spreadsheet'));
            const fileSize = attachment.size ? (attachment.size / 1024).toFixed(1) + ' KB' : 'Unknown size';
            
            if (isImage) {
                attachmentsHtml += `
                    <div class="attachment-image-wrapper" onclick="downloadImage('/dashboard/uimms/chat/reply/${message.id}/attachment/${index}/download', '${attachment.name}')">
                        <img src="/dashboard/uimms/chat/reply/${message.id}/attachment/${index}/view" 
                             alt="${attachment.name}"
                             class="attachment-image">
                        <div class="image-overlay">
                            <i class="icofont-download"></i>
                        </div>
                    </div>
                `;
            } else {
                attachmentsHtml += `
                    <div class="attachment-file-card">
                        <div class="file-icon">
                            ${isPdf ? '<i class="icofont-file-pdf"></i>' : 
                              isWord ? '<i class="icofont-file-document"></i>' :
                              isExcel ? '<i class="icofont-file-excel"></i>' : 
                              '<i class="icofont-file-alt"></i>'}
                        </div>
                        <div class="file-info">
                            <div class="file-name">${attachment.name}</div>
                            <div class="file-size">${fileSize}</div>
                        </div>
                        <a href="/dashboard/uimms/chat/reply/${message.id}/attachment/${index}/download" 
                           class="file-download-btn" title="Download">
                            <i class="icofont-download"></i>
                        </a>
                    </div>
                `;
            }
        });
        attachmentsHtml += '</div>';
    }
    
    const messageClass = isOwnMessage ? 'message-sent' : 'message-received';
    const messageHtml = `
        <div class="message ${messageClass} new-message">
            <div class="message-avatar">
                <img src="${message.user.profile_picture_url || '/profile_pictures/default-profile.png'}" 
                     alt="${message.user.first_name}">
            </div>
            <div class="message-content">
                <div class="message-header">
                    <div class="message-header-left">
                        <span class="message-sender">${message.user.first_name} ${message.user.last_name}</span>
                        ${replyModeDisplay}
                    </div>
                    <div class="message-header-right">
                        <span class="message-time">${new Date(message.created_at).toLocaleString()}</span>
                    </div>
                </div>
                <div class="message-text">${message.message}</div>
                ${attachmentsHtml}
            </div>
        </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
    
    // Add animation for new messages
    const newMessageElement = messagesContainer.lastElementChild;
    newMessageElement.style.opacity = '0';
    newMessageElement.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        newMessageElement.style.transition = 'all 0.3s ease';
        newMessageElement.style.opacity = '1';
        newMessageElement.style.transform = 'translateY(0)';
    }, 100);
    
    // Remove animation class after animation completes
    setTimeout(() => {
        newMessageElement.classList.remove('new-message');
    }, 300);
    
    scrollToBottom();
}

// Show typing indicator
function showTypingIndicator() {
    const messagesContainer = document.getElementById('chat-messages');
    const typingHtml = `
        <div class="typing-indicator" id="typing-indicator">
            <i class="icofont-spinner-alt icofont-spin"></i> Sending message...
        </div>
    `;
    messagesContainer.insertAdjacentHTML('beforeend', typingHtml);
    scrollToBottom();
}

// Hide typing indicator
function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

// Scroll to bottom
function scrollToBottom() {
    const container = document.querySelector('.chat-container');
    container.scrollTop = container.scrollHeight;
}

// Assignment functions
function showAssignModal() {
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
    
    // Clear search when modal opens
    const searchInput = document.getElementById('user-search-input');
    if (searchInput) {
        searchInput.value = '';
        filterUserList('');
    }
}

// User search filter functionality
function filterUserList(searchTerm) {
    const searchLower = searchTerm.toLowerCase().trim();
    const userItems = document.querySelectorAll('.user-item');
    let visibleCount = 0;
    
    userItems.forEach(function(item) {
        const name = item.getAttribute('data-name') || '';
        const email = item.getAttribute('data-email') || '';
        
        if (searchLower === '' || name.includes(searchLower) || email.includes(searchLower)) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show message if no results found
    const container = document.getElementById('user-list-container');
    let noResultsMsg = container.querySelector('.no-results-message');
    
    if (visibleCount === 0 && searchLower !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results-message text-muted text-center p-3';
            noResultsMsg.textContent = 'No users found matching your search.';
            container.appendChild(noResultsMsg);
        }
        noResultsMsg.style.display = 'block';
    } else {
        if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
}

// Add search input event listener
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('user-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            filterUserList(e.target.value);
        });
        
        // Clear search when modal is hidden
        const assignModal = document.getElementById('assignModal');
        if (assignModal) {
            assignModal.addEventListener('hidden.bs.modal', function() {
                searchInput.value = '';
                filterUserList('');
            });
        }
    }
});

document.getElementById('assign-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate that at least one user is selected
    const checkedBoxes = this.querySelectorAll('input[name="assignee_ids[]"]:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select at least one user to assign the memo.');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch(`/dashboard/uimms/chat/${memoId}/assign`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
            location.reload(); // Refresh to show new participant
        } else {
            alert(data.message || 'Error assigning memo. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error assigning memo:', error);
        alert('Error assigning memo. Please try again.');
    });
});

// Suspend functions
function showSuspendModal() {
    new bootstrap.Modal(document.getElementById('suspendModal')).show();
}

document.getElementById('suspend-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('status', 'suspended');
    
    updateMemoStatus('suspended', formData.get('reason'));
    bootstrap.Modal.getInstance(document.getElementById('suspendModal')).hide();
});

// Update memo status
function updateMemoStatus(status, reason = null) {
    const formData = new FormData();
    formData.append('status', status);
    if (reason) formData.append('reason', reason);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/dashboard/uimms/chat/${memoId}/status`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to show updated status
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        alert('Error updating memo status. Please try again.');
    });
}

// Keep in View function
function keepInViewMemo() {
    confirmAction(
        'Are you sure you want to mark this memo as Keep in View?',
        function() {
            bookmarkMemo();
        },
        null,
        {
            title: 'Keep in View',
            type: 'info',
            confirmText: 'Keep in View',
            subtitle: 'This memo will be sent to the Keep in View page for future reference. You can access it later from the Keep in View section.'
        }
    );
}

// Bookmark memo function
function bookmarkMemo() {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    fetch(`/dashboard/uimms/memo/${memoId}/toggle-bookmark`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Successfully bookmarked - memo will now appear in Keep in View section
            // Optionally reload or show success message
            location.reload();
        } else {
            alert(data.message || 'Error adding memo to Keep in View. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error bookmarking memo:', error);
        alert('Error adding memo to Keep in View. Please try again.');
    });
}

// Confirmation function for completing memo
function confirmCompleteMemo() {
    confirmAction(
        'Are you sure you want to mark this memo as completed?',
        function() {
            updateMemoStatus('completed');
        },
        null,
        {
            title: 'Complete Memo',
            type: 'success',
            confirmText: 'Complete',
            subtitle: 'This will mark the memo as finished and prevent further messages.'
        }
    );
}

// Confirmation function for archiving memo
function confirmArchiveMemo() {
    confirmAction(
        'Are you sure you want to archive this memo?',
        function() {
            updateMemoStatus('archived');
        },
        null,
        {
            title: 'Archive Memo',
            type: 'warning',
            confirmText: 'Archive',
            subtitle: 'This will move the memo to the archive and make it read-only.'
        }
    );
}

function confirmUnsuspendMemo() {
    confirmAction(
        'Are you sure you want to unsuspend this memo?',
        function() {
            updateMemoStatus('unsuspended');
        },
        null,
        {
            title: 'Unsuspend Memo',
            type: 'success',
            confirmText: 'Unsuspend',
            subtitle: 'This will resume the memo conversation and allow participants to continue messaging.'
        }
    );
}

// Silent refresh system for real-time chat updates
let lastMessageCount = {{ $memo->replies->count() }};
let lastMessageId = {{ $memo->replies->last() ? $memo->replies->last()->id : 0 }};
let isPolling = true;
let isSendingMessage = false; // Flag to prevent auto-refresh during message sending

// Auto-refresh messages every 3 seconds
messageInterval = setInterval(() => {
    if (!isPolling || isUserTyping || isSendingMessage) return;
    
    fetch(`/dashboard/uimms/chat/${memoId}/messages`)
        .then(response => response.json())
        .then(messages => {
            if (messages.length > lastMessageCount) {
                // New messages detected
                const newMessages = messages.slice(lastMessageCount);
                newMessages.forEach(message => {
                    if (message.id > lastMessageId) {
                        const currentUserId = {{ Auth::id() }};
                        // Only add messages from other users (not our own messages)
                        if (message.user_id !== currentUserId) {
                            addNewMessageToChat(message);
                        }
                        lastMessageId = message.id;
                    }
                });
                lastMessageCount = messages.length;
                
                // Play notification sound for new messages (not from current user)
                const currentUserId = {{ Auth::id() }};
                const hasNewMessagesFromOthers = newMessages.some(msg => msg.user_id !== currentUserId);
                if (hasNewMessagesFromOthers) {
                    playNotificationSound();
                }
            }
        })
        .catch(error => {
            console.error('Error refreshing messages:', error);
            // If there's an error, stop polling temporarily
            isPolling = false;
            setTimeout(() => {
                isPolling = true;
            }, 10000); // Resume polling after 10 seconds
        });
}, 3000);

// Scroll to bottom on load
window.addEventListener('load', scrollToBottom);

// Clean up interval on page unload
window.addEventListener('beforeunload', () => {
    if (messageInterval) {
        clearInterval(messageInterval);
    }
});

// Image Viewer Function
function viewImage(imageUrl, imageName) {
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'image-viewer-modal';
    modal.innerHTML = `
        <div class="image-viewer-overlay" onclick="closeImageViewer()"></div>
        <div class="image-viewer-content">
            <div class="image-viewer-header">
                <span class="image-viewer-title">${imageName}</span>
                <button onclick="closeImageViewer()" class="close-viewer-btn">
                    <i class="icofont-close"></i>
                </button>
            </div>
            <div class="image-viewer-body">
                <img src="${imageUrl}" alt="${imageName}">
            </div>
            <div class="image-viewer-footer">
                <a href="${imageUrl}" download="${imageName}" class="download-image-btn">
                    <i class="icofont-download"></i> Download
                </a>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Add fade-in animation
    setTimeout(() => modal.classList.add('active'), 10);
}

function closeImageViewer() {
    const modal = document.querySelector('.image-viewer-modal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';
        }, 300);
    }
}

// Close on ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeImageViewer();
    }
});

// Play notification sound for new messages
function playNotificationSound() {
    try {
        const audio = new Audio('/sounds/notification.mp3');
        audio.volume = 0.3; // Lower volume to avoid being too intrusive
        audio.play().catch(e => {
            console.log('Could not play notification sound:', e);
        });
    } catch (e) {
        console.log('Notification sound not available:', e);
    }
}

// Download image function
function downloadImage(downloadUrl, fileName) {
    console.log('Download function called with:', downloadUrl, fileName);
    
    // Prevent event bubbling
    event.preventDefault();
    event.stopPropagation();
    
    // Prevent multiple simultaneous downloads
    if (window.downloadInProgress) {
        console.log('Download already in progress, ignoring click');
        return;
    }
    
    window.downloadInProgress = true;
    
    try {
        // Create a temporary link element for download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = fileName || 'image';
        link.style.display = 'none';
        
        // Append to body, click, and remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        console.log('Download initiated successfully');
        
        // Reset the flag after a short delay
        setTimeout(() => {
            window.downloadInProgress = false;
        }, 1000);
        
    } catch (e) {
        console.error('Error downloading image:', e);
        // Reset the flag immediately on error
        window.downloadInProgress = false;
        // Fallback: open in new tab
        window.open(downloadUrl, '_blank');
    }
}

// Pause polling when user is typing to avoid conflicts
let isUserTyping = false;
let typingTimeout;

document.getElementById('message-input').addEventListener('input', function() {
    isUserTyping = true;
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        isUserTyping = false;
    }, 2000); // Resume polling 2 seconds after user stops typing
});

// Pause polling when user is actively interacting with the page
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        isPolling = false;
    } else {
        isPolling = true;
    }
});

// Export Chat Conversation Function
function exportChatConversation() {
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    
    // Get memo data
    const memoSubject = '{{ $memo->subject }}';
    const memoStatus = '{{ $memo->memo_status }}';
    const memoCreated = '{{ $memo->created_at->format("M d, Y H:i") }}';
    const memoCreator = '{{ $memo->creator->first_name }} {{ $memo->creator->last_name }}';
    const memoAssignee = '{{ $memo->currentAssignee ? $memo->currentAssignee->first_name . " " . $memo->currentAssignee->last_name : "Not assigned" }}';
    
    // Get all messages
    const messagesContainer = document.getElementById('chat-messages');
    const messages = messagesContainer.querySelectorAll('.message');
    
    let messagesHtml = '';
    messages.forEach(message => {
        const sender = message.querySelector('.message-sender').textContent;
        const time = message.querySelector('.message-time').textContent;
        const text = message.querySelector('.message-text').textContent;
        const replyTo = message.querySelector('.reply-to-indicator');
        const replyToText = replyTo ? replyTo.textContent : '';
        
        // Format the reply indicator to be closer to the sender name
        const formattedReplyTo = replyToText ? ` ${replyToText}` : '';
        
        // Check if this is a system message (status changes, assignments, etc.)
        const isSystemMessage = text.toLowerCase().includes('memo') && 
                               (text.toLowerCase().includes('completed') || 
                                text.toLowerCase().includes('archived') || 
                                text.toLowerCase().includes('suspended') || 
                                text.toLowerCase().includes('assigned') ||
                                text.toLowerCase().includes('status') ||
                                text.toLowerCase().includes('system'));
        
        const messageClass = isSystemMessage ? 'export-message system-message' : 'export-message';
        
        messagesHtml += `
            <div class="${messageClass}">
                <div class="export-message-header">
                    <strong>${sender}${formattedReplyTo}</strong>
                    <span class="export-message-time">${time}</span>
                </div>
                <div class="export-message-content">${text}</div>
            </div>
        `;
    });
    
    // Create the print content
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Chat Export - ${memoSubject}</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    background: white;
                    position: relative;
                }
                
                /* Watermark */
                body::before {
                    content: '';
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background-image: url('https://res.cloudinary.com/dsypclqxk/image/upload/v1756722559/catholic-university-ghana-logo_onhrgj.jpg');
                    background-size: 300px 300px;
                    background-repeat: no-repeat;
                    background-position: center;
                    width: 300px;
                    height: 300px;
                    opacity: 0.1;
                    z-index: -1;
                    pointer-events: none;
                }
                .export-header {
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 20px;
                    margin-bottom: 30px;
                }
                .export-logo-section {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid #e9ecef;
                }
                .export-logo {
                    height: 60px;
                    max-width: 200px;
                    margin-right: 20px;
                }
                .export-logo-text {
                    flex: 1;
                }
                .export-logo-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: #007bff;
                    margin-bottom: 5px;
                }
                .export-logo-tagline {
                    font-size: 14px;
                    color: #666;
                    font-style: italic;
                }
                .export-title {
                    font-size: 24px;
                    font-weight: bold;
                    color: #007bff;
                    margin-bottom: 10px;
                }
                .export-meta {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                .export-meta-item {
                    background: #f8f9fa;
                    padding: 10px;
                    border-radius: 5px;
                    border-left: 4px solid #007bff;
                }
                .export-meta-label {
                    font-weight: bold;
                    color: #666;
                    font-size: 12px;
                    text-transform: uppercase;
                    margin-bottom: 5px;
                }
                .export-meta-value {
                    color: #333;
                    font-size: 14px;
                }
                .export-messages {
                    margin-top: 30px;
                }
                .export-message {
                    margin-bottom: 25px;
                    padding: 15px;
                    border: 1px solid #e9ecef;
                    border-radius: 8px;
                    background: #f8f9fa;
                }
                .export-message-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 10px;
                    font-size: 14px;
                }
                .export-message-time {
                    color: #666;
                    font-size: 12px;
                }
                .export-message-content {
                    color: #333;
                    line-height: 1.5;
                }
                .export-message.system-message {
                    margin-bottom: 8px;
                    padding: 8px 12px;
                    border: 1px solid #dee2e6;
                    background-color: #f1f3f4;
                    font-size: 12px;
                    opacity: 0.8;
                }
                .export-message.system-message .export-message-header {
                    margin-bottom: 5px;
                    font-size: 12px;
                }
                .export-message.system-message .export-message-header strong {
                    font-size: 11px;
                    color: #6c757d;
                }
                .export-message.system-message .export-message-content {
                    font-size: 11px;
                    color: #6c757d;
                    font-style: italic;
                    line-height: 1.3;
                }
                .export-message.system-message .export-message-time {
                    font-size: 10px;
                    color: #999;
                }
                .export-footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #e9ecef;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
                @media print {
                    body { 
                        margin: 0; 
                        padding: 15px; 
                    }
                    .export-header { page-break-after: avoid; }
                    .export-message { page-break-inside: avoid; }
                    .export-logo { height: 50px; }
                    
                    /* Enhanced watermark for print */
                    body::before {
                        opacity: 0.15;
                        background-size: 400px 400px;
                        width: 400px;
                        height: 400px;
                    }
                }
                @media (max-width: 600px) {
                    .export-logo-section {
                        flex-direction: column;
                        text-align: center;
                    }
                    .export-logo {
                        margin-right: 0;
                        margin-bottom: 10px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="export-header">
                <div class="export-logo-section">
                    <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1761222538/cug_logo_new_e9d6v9.jpg" 
                         alt="University Digital Transformation Suite (UDTS)" 
                         class="export-logo" />
                    <div class="export-logo-text">
                        <div class="export-logo-title">University Internal Memo Management System</div>
                        <div class="export-logo-tagline">Excellence in Academic Digital Archiving, Advanced Communication and Internal Memo Management</div>
                    </div>
                </div>
                <div class="export-title">${memoSubject}</div>
                <div class="export-meta">
                    <div class="export-meta-item">
                        <div class="export-meta-label">Status</div>
                        <div class="export-meta-value">${memoStatus}</div>
                    </div>
                    <div class="export-meta-item">
                        <div class="export-meta-label">Created</div>
                        <div class="export-meta-value">${memoCreated}</div>
                    </div>
                    <div class="export-meta-item">
                        <div class="export-meta-label">From</div>
                        <div class="export-meta-value">${memoCreator}</div>
                    </div>
                    <div class="export-meta-item">
                        <div class="export-meta-label">Assigned To</div>
                        <div class="export-meta-value">${memoAssignee}</div>
                    </div>
                </div>
            </div>
            
            <div class="export-messages">
                <h3>Conversation History</h3>
                ${messagesHtml}
            </div>
            
            <div class="export-footer">
                <p>Exported on ${new Date().toLocaleString()} | UIMMS Chat Export</p>
            </div>
        </body>
        </html>
    `;
    
    // Write content to new window
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Wait for content to load, then trigger print dialog
    printWindow.onload = function() {
        setTimeout(() => {
            printWindow.print();
            // Close the window after printing (optional)
            // printWindow.close();
        }, 500);
    };
}
</script>

<style>
/* Image Viewer Modal */
.image-viewer-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-viewer-modal.active {
    opacity: 1;
}

.image-viewer-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    cursor: pointer;
}

.image-viewer-content {
    position: relative;
    width: 90%;
    max-width: 900px;
    height: 90%;
    margin: 5vh auto;
    display: flex;
    flex-direction: column;
    z-index: 10001;
}

.image-viewer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 8px 8px 0 0;
}

.image-viewer-title {
    color: white;
    font-weight: 500;
    font-size: 1rem;
}

.close-viewer-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.close-viewer-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.image-viewer-body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.8);
    overflow: hidden;
}

.image-viewer-body img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.image-viewer-footer {
    padding: 15px 20px;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 0 0 8px 8px;
    display: flex;
    justify-content: center;
}

.download-image-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.download-image-btn:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

/* New Message Animation */
.message.new-message {
    animation: slideInMessage 0.3s ease-out;
}

@keyframes slideInMessage {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Subtle pulse animation for new messages */
.message.new-message .message-content {
    animation: newMessagePulse 0.6s ease-out;
}

@keyframes newMessagePulse {
    0% {
        box-shadow: 0 0 0 0 rgba(25, 118, 210, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(25, 118, 210, 0.1);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(25, 118, 210, 0);
    }
}

/* Notification indicator for new messages */
.message.new-message::before {
    content: '';
    position: absolute;
    left: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    background: #1976d2;
    border-radius: 2px;
    animation: newMessageIndicator 1s ease-out;
}

@keyframes newMessageIndicator {
    0% {
        opacity: 0;
        transform: translateY(-50%) scaleY(0);
    }
    50% {
        opacity: 1;
        transform: translateY(-50%) scaleY(1);
    }
    100% {
        opacity: 0.3;
        transform: translateY(-50%) scaleY(1);
    }
}
</style>

<!-- Urgency Alert Dialog -->
<div class="urgency-dialog-overlay" id="urgency-alert-dialog" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div class="urgency-dialog" onclick="event.stopPropagation()" style="background: white; border-radius: 12px; padding: 24px; max-width: 400px; width: 90%; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
        <div class="urgency-dialog-header" style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <i class="icofont-flag" style="font-size: 24px; color: #dc3545;"></i>
            <h3 style="font-size: 18px; font-weight: 600; color: #333; margin: 0;">Send Urgency Alert</h3>
        </div>
        <p style="font-size: 14px; color: #666; margin-bottom: 24px; line-height: 1.5;">
            You are about to send an urgency alert email to all participants of this pending memo. This will notify them that the memo requires immediate attention.
        </p>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button class="urgency-dialog-btn-cancel" id="urgency-cancel-btn" onclick="closeUrgencyAlertDialog()" style="padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; background-color: #e9ecef; color: #495057;">Cancel</button>
            <button class="urgency-dialog-btn-confirm" id="urgency-confirm-btn" onclick="sendUrgencyAlert()" style="padding: 10px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; background-color: #dc3545; color: white;">Yes, Send Alert</button>
        </div>
    </div>
</div>

<script>
// Urgency Alert Dialog Functions for Chat View
let currentUrgencyMemoId = null;

function showUrgencyAlertDialog(memoId) {
    currentUrgencyMemoId = memoId;
    const dialog = document.getElementById('urgency-alert-dialog');
    if (dialog) {
        dialog.style.display = 'flex';
    }
}

function closeUrgencyAlertDialog() {
    const dialog = document.getElementById('urgency-alert-dialog');
    if (dialog) {
        dialog.style.display = 'none';
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
</script>

@endsection
