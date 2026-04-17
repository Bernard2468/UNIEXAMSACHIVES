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
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="dashboard__content__wraper">
                        <div class="dashboard__section__title">
                            <h4>Compose Memo</h4>
                            <div class="dashboard__section__actions">
                                <a href="{{route('admin.communication.index')}}" class="responsive-btn back-btn">
                                    <div class="svgWrapper">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            class="svgIcon"
                                        >
                                            <path
                                                stroke="#fff"
                                                stroke-width="2"
                                                d="M19 12H5m7-7-7 7 7 7"
                                            ></path>
                                        </svg>
                                        <div class="text">Back to Memos</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.communication.store') }}" enctype="multipart/form-data" id="emailForm">
                            @csrf
                            
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="compose-panel">
                                        <div class="panel-header">
                                            <h5><i class="icofont-edit"></i> Compose Your Memo</h5>
                                            <p class="panel-subtitle">Create engaging content for your audience</p>
                                        </div>

                                        <div class="form-section">
                                            <div class="form-group">
                                                <label for="subject" class="form-label">
                                                    <i class="icofont-email"></i> Memo Subject
                                                    <span class="required">*</span>
                                                </label>
                                                <input type="text" id="subject" name="subject" 
                                                       placeholder="Enter a compelling subject line..." 
                                                       value="{{ old('subject') }}" required class="form-input">
                                                <small class="form-help">This will be the memo subject line users see in their inbox</small>
                                            </div>

                                            <div class="form-group">
                                                <label for="message" class="form-label">
                                                    <i class="icofont-chat"></i> Memo Message
                                                    <span class="required">*</span>
                                                </label>
                                                <div class="message-editor">
                                                    <div class="editor-toolbar">
                                                        <div class="toolbar-group">
                                                            <button type="button" class="toolbar-btn" data-command="bold" title="Bold (Ctrl+B)">
                                                                <i class="icofont-bold"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="italic" title="Italic (Ctrl+I)">
                                                                <i class="icofont-italic"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="underline" title="Underline (Ctrl+U)">
                                                                <i class="icofont-underline"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="strikeThrough" title="Strikethrough">
                                                                <i class="fas fa-strikethrough"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <div class="toolbar-divider"></div>
                                                        
                                                        <div class="toolbar-group">
                                                            <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left">
                                                                <i class="icofont-align-left"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center">
                                                                <i class="icofont-align-center"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right">
                                                                <i class="icofont-align-right"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="justifyFull" title="Justify">
                                                                <i class="fas fa-align-justify"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <div class="toolbar-divider"></div>
                                                        
                                                        <div class="toolbar-group">
                                                            <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                                                                <i class="fas fa-list-ul"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                                                                <i class="fas fa-list-ol"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="outdent" title="Decrease Indent">
                                                                <i class="fas fa-outdent"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="indent" title="Increase Indent">
                                                                <i class="fas fa-indent"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <div class="toolbar-divider"></div>
                                                        
                                                        <div class="toolbar-group">
                                                            <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link">
                                                                <i class="icofont-link"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="insertImage" title="Insert Image">
                                                                <i class="icofont-image"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="insertTable" title="Insert Table">
                                                                <i class="icofont-table"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="insertHorizontalRule" title="Insert Line">
                                                                <i class="fas fa-minus"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <div class="toolbar-divider"></div>
                                                        
                                                        <div class="toolbar-group">
                                                            <button type="button" class="toolbar-btn" data-command="undo" title="Undo (Ctrl+Z)">
                                                                <i class="icofont-undo"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="redo" title="Redo (Ctrl+Y)">
                                                                <i class="icofont-redo"></i>
                                                            </button>
                                                            <button type="button" class="toolbar-btn" data-command="removeFormat" title="Clear Formatting">
                                                                <i class="icofont-eraser"></i>
                                                            </button>
                                                        </div>
                                                        
                                                        <div class="toolbar-divider"></div>
                                                        
                                                        <div class="toolbar-group">
                                                            <select class="toolbar-select" id="fontSize" title="Font Size">
                                                                <option value="1">Small</option>
                                                                <option value="3" selected>Normal</option>
                                                                <option value="5">Large</option>
                                                                <option value="7">Extra Large</option>
                                                            </select>
                                                            <input type="color" class="toolbar-color" id="textColor" title="Text Color" value="#000000">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="editor-content" id="editor-content" contenteditable="true" data-placeholder="Write your message here... HTML formatting is supported">
                                                        {{ old('message') }}
                                                    </div>
                                                    
                                                    <textarea id="message" name="message" style="display: none;">{{ old('message') }}</textarea>
                                                </div>
                                                <small class="form-help">Use the toolbar above for rich text formatting. Your message will be sent as HTML.</small>
                                            </div>

                                            <div class="form-group">
                                                <label for="attachments" class="form-label">
                                                    <i class="icofont-attachment"></i> Attachments
                                                </label>
                                                <div class="file-upload-area">
                                                    <input type="file" id="attachments" name="attachments[]" 
                                                           multiple accept=".pdf,.doc,.docx,.txt,.jpg,.png,.gif,.zip"
                                                           class="file-input">
                                                    <div class="file-upload-display">
                                                        <div class="file-upload-icon">
                                                            <i class="icofont-cloud-upload"></i>
                                                        </div>
                                                        <div class="file-upload-text">
                                                            <strong>Click to upload files</strong> or drag and drop here
                                                            <br><small>Supported: PDF, DOC, DOCX, TXT, JPG, PNG, GIF, ZIP (Max: 3 files, 10MB each)</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="file-list" class="file-list"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <div class="compose-panel">
                                        <div class="panel-header">
                                            <h5><i class="icofont-users"></i> Recipients & Settings</h5>
                                            <p class="panel-subtitle">Configure your memo delivery options</p>
                                        </div>
                                        
                                        <div class="form-section">
                                            <div class="form-group">
                                                <label class="form-label">Recipient Selection</label>
                                                <div class="recipient-options">
                                                    <div class="option-card">
                                                        <input class="option-radio" type="radio" name="recipient_type" 
                                                               id="all_users" value="all" 
                                                               {{ old('recipient_type', 'all') === 'all' ? 'checked' : '' }}>
                                                        <label class="option-label" for="all_users">
                                                            <div class="option-icon">
                                                                <i class="icofont-users-alt-3"></i>
                                                            </div>
                                                            <div class="option-content">
                                                                <strong>All Registered Users</strong>
                                                                <span class="option-desc">Send to all approved users ({{ $users->count() }} users)</span>
                                                            </div>
                                                        </label>
                                                    </div>

                                                    <div class="option-card">
                                                        <input class="option-radio" type="radio" name="recipient_type" 
                                                               id="selected_users" value="selected"
                                                               {{ old('recipient_type') === 'selected' ? 'checked' : '' }}>
                                                        <label class="option-label" for="selected_users">
                                                            <div class="option-icon">
                                                                <i class="icofont-user-alt-4"></i>
                                                            </div>
                                                            <div class="option-content">
                                                                <strong>Selected Users</strong>
                                                                <span class="option-desc">Choose specific users to send to</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>

                                        <div id="user-selector" class="user-selector" style="display: none;">
                                            <div class="user-selector-header">
                                                <div class="search-container">
                                                    <div class="search-input-wrapper">
                                                        <i class="icofont-search search-icon"></i>
                                                        <input type="text" id="user-search" 
                                                               placeholder="Search by name or email..." 
                                                               class="search-input">
                                                    </div>
                                                </div>
                                                <div class="user-stats">
                                                    <div class="stats-info">
                                                        <span class="selected-count">0</span> of <span class="total-count">{{ $users->count() }}</span> users selected
                                                    </div>
                                                    <div class="progress-bar-container">
                                                        <div class="progress-bar" id="selection-progress"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="user-list-container">
                                                <div class="user-list" id="user-list">
                                                    @foreach($users as $user)
                                                        <div class="user-item" data-user-id="{{ $user->id }}" data-search="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                                                            <div class="user-avatar">
                                                                                                @if($user->profile_picture)
                                    <img src="{{ asset('profile_pictures/' . $user->profile_picture) }}" alt="{{ $user->first_name }}" class="avatar-img">
                                @else
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                                            </div>
                                                            <div class="user-info">
                                                                <div class="user-name">
                                                                    <span>{{ $user->first_name }} {{ $user->last_name }}</span>
                                                                    @if($user->position)
                                                                        <span class="name-separator">|</span>
                                                                        <span class="position-badge-small">
                                                                            <i class="fas fa-briefcase"></i>
                                                                            {{ $user->position->name }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <div class="user-email">{{ $user->email }}</div>
                                                                <div class="user-status">
                                                                    <span class="status-indicator online"></span>
                                                                    <small>Active User</small>
                                                                </div>
                                                            </div>
                                                            <div class="user-select-checkbox">
                                                                <input type="checkbox" name="selected_users[]" 
                                                                       value="{{ $user->id }}" 
                                                                       class="user-checkbox"
                                                                       {{ in_array($user->id, old('selected_users', [])) ? 'checked' : '' }}>
                                                                <span class="checkmark"></span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="user-actions">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-btn">
                                                    <i class="icofont-check-circled"></i> Select All
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-btn">
                                                    <i class="icofont-close-circled"></i> Clear All
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info" id="export-selected-btn">
                                                    <i class="icofont-download"></i> Export
                                                </button>
                                            </div>
                                        </div>

                                                <!-- Staff Category Cards -->
                                                <div class="staff-category-section">
                                                    <h6 class="section-title">Staff Categories</h6>
                                                    <div class="staff-category-grid">
                                                        <div class="option-card">
                                                            <input class="option-radio" type="radio" name="recipient_type" 
                                                                   id="junior_staff" value="junior_staff"
                                                                   {{ old('recipient_type') === 'junior_staff' ? 'checked' : '' }}>
                                                            <label class="option-label" for="junior_staff">
                                                                <div class="option-icon">
                                                                    <i class="icofont-user"></i>
                                                                </div>
                                                                <div class="option-content">
                                                                    <strong>Junior Staff</strong>
                                                                    <span class="option-desc">Send to all Junior Staff members ({{ $staffCategoryCounts['junior_staff'] }} users)</span>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <div class="option-card">
                                                            <input class="option-radio" type="radio" name="recipient_type" 
                                                                   id="senior_staff" value="senior_staff"
                                                                   {{ old('recipient_type') === 'senior_staff' ? 'checked' : '' }}>
                                                            <label class="option-label" for="senior_staff">
                                                                <div class="option-icon">
                                                                    <i class="icofont-user-alt-2"></i>
                                                                </div>
                                                                <div class="option-content">
                                                                    <strong>Senior Staff</strong>
                                                                    <span class="option-desc">Send to all Senior Staff members ({{ $staffCategoryCounts['senior_staff'] }} users)</span>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <div class="option-card">
                                                            <input class="option-radio" type="radio" name="recipient_type" 
                                                                   id="senior_member_non_teaching" value="senior_member_non_teaching"
                                                                   {{ old('recipient_type') === 'senior_member_non_teaching' ? 'checked' : '' }}>
                                                            <label class="option-label" for="senior_member_non_teaching">
                                                                <div class="option-icon">
                                                                    <i class="icofont-user-alt-3"></i>
                                                                </div>
                                                                <div class="option-content">
                                                                    <strong>Senior Member (Non-Teaching)</strong>
                                                                    <span class="option-desc">Send to all Senior Members (Non-Teaching) ({{ $staffCategoryCounts['senior_member_non_teaching'] }} users)</span>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <div class="option-card">
                                                            <input class="option-radio" type="radio" name="recipient_type" 
                                                                   id="senior_member_teaching" value="senior_member_teaching"
                                                                   {{ old('recipient_type') === 'senior_member_teaching' ? 'checked' : '' }}>
                                                            <label class="option-label" for="senior_member_teaching">
                                                                <div class="option-icon">
                                                                    <i class="icofont-user-alt-5"></i>
                                                                </div>
                                                                <div class="option-content">
                                                                    <strong>Senior Member (Teaching)</strong>
                                                                    <span class="option-desc">Send to all Senior Members (Teaching) ({{ $staffCategoryCounts['senior_member_teaching'] }} users)</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Committees & Boards Section -->
                                                <div class="committees-section mt-4">
                                                    <h6 class="section-title">Committees & Boards</h6>
                                                    <div class="committees-grid">
                                                        @forelse ($committees as $committee)
                                                        <div class="option-card">
                                                            <input class="option-radio" type="radio" name="recipient_type" 
                                                                   id="committee_{{ $committee->id }}" value="committee_{{ $committee->id }}"
                                                                   data-committee-name="{{ $committee->name }}"
                                                                   data-committee-members="{{ $committee->users_count }}"
                                                                   {{ old('recipient_type') === 'committee_' . $committee->id ? 'checked' : '' }}>
                                                            <label class="option-label" for="committee_{{ $committee->id }}">
                                                                <div class="option-icon">
                                                                    <i class="icofont-users-alt-5"></i>
                                                                </div>
                                                                <div class="option-content">
                                                                    <strong>{{ $committee->name }}</strong>
                                                                    <span class="option-desc">Send to all members of {{ $committee->name }} ({{ $committee->users_count }} members)</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                        @empty
                                                        <div class="alert alert-info">
                                                            <i class="icofont-info-circle"></i> No active committees/boards available. Create one in the Committees & Boards section.
                                                        </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>

                                        <div class="form-group">
                                            <label class="form-label">Sending Options</label>
                                            <div class="sending-options">
                                                <div class="option-card">
                                                    <input class="option-radio" type="radio" name="send_option" 
                                                           id="send_now" value="now" checked>
                                                    <label class="option-label" for="send_now">
                                                        <div class="option-icon">
                                                            <i class="icofont-send-mail"></i>
                                                        </div>
                                                        <div class="option-content">
                                                            <strong>Send Immediately</strong>
                                                            <span class="option-desc">Memo will be sent right away</span>
                                                        </div>
                                                    </label>
                                                </div>

                                                <div class="option-card">
                                                    <input class="option-radio" type="radio" name="send_option" 
                                                           id="schedule_send" value="schedule">
                                                    <label class="option-label" for="schedule_send">
                                                        <div class="option-icon">
                                                            <i class="icofont-clock-time"></i>
                                                        </div>
                                                        <div class="option-content">
                                                            <strong>Schedule for Later</strong>
                                                            <span class="option-desc">Choose when to send the memo</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="schedule-datetime" class="schedule-datetime mt-3" style="display: none;">
                                            <label for="scheduled_at">Schedule Date & Time:</label>
                                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" 
                                                   class="form-control" min="{{ date('Y-m-d\TH:i') }}">
                                        </div>

                                        <hr>

                                        <div class="form-group">
                                            <label class="form-label">Actions</label>
                                            <div class="form-actions">
                                                <button type="button" id="send-memo-btn" class="action-btn send-btn">
                                                    <i class="icofont-send-mail"></i> Send Memo
                                                </button>
                                                <button type="submit" name="action" value="draft" class="action-btn draft-btn">
                                                    <i class="icofont-save"></i> Save as Draft
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Memo Confirmation Modal -->
<div id="sendMemoModal" class="memo-confirm-modal">
    <div class="memo-confirm-modal-overlay"></div>
    <div class="memo-confirm-modal-content">
        <div class="memo-confirm-modal-header">
            <div class="memo-confirm-icon">
                <i class="icofont-send-mail"></i>
            </div>
            <h3>Confirm Send Memo</h3>
        </div>
        <div class="memo-confirm-modal-body">
            <p>Are you sure you want to send this memo?</p>
            <div class="memo-preview-info">
                <div class="info-item">
                    <i class="icofont-email"></i>
                    <span id="modal-subject-preview">Subject will appear here</span>
                </div>
                <div class="info-item">
                    <i class="icofont-users-alt-3"></i>
                    <span id="modal-recipients-preview">Recipients will appear here</span>
                </div>
            </div>
            <p class="memo-warning-text">
                <i class="icofont-warning"></i>
                This action cannot be undone. The memo will be sent immediately.
            </p>
        </div>
        <div class="memo-confirm-modal-footer">
            <button type="button" class="memo-confirm-btn memo-cancel-btn" id="cancelSendBtn">
                <i class="icofont-close"></i> Cancel
            </button>
            <button type="button" class="memo-confirm-btn memo-send-confirm-btn" id="confirmSendBtn">
                <i class="icofont-send-mail"></i> Yes, Send Memo
            </button>
        </div>
    </div>
</div>

<style>
    /* Modern Compose Memo Styles */
* {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.required { 
  color: #ef4444; 
  font-weight: 600;
}

/* Enhanced Dashboard Section Title */
.dashboard__section__title h4 {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
  font-size: 26px;
  font-weight: 800;
  color: #1e293b;
  letter-spacing: -0.03em;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  margin: 0;
}

/* Responsive Button Styling */
.dashboard__section__actions {
  display: flex;
  gap: 15px;
  align-items: center;
}

.responsive-btn {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 45px;
  height: 45px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition-duration: 0.3s;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.199);
  background-color: #1a4a9b;
  text-decoration: none;
  flex-shrink: 0;
}

.responsive-btn:hover {
  width: 160px;
  border-radius: 40px;
  transition-duration: 0.3s;
  text-decoration: none;
}

.responsive-btn .svgWrapper {
  width: 100%;
  transition-duration: 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.responsive-btn .svgIcon {
  width: 17px;
  flex-shrink: 0;
}

.responsive-btn .text {
  position: absolute;
  left: 50px;
  width: 100px;
  opacity: 0;
  color: white;
  font-size: 14px;
  font-weight: 600;
  transition-duration: 0.3s;
  white-space: nowrap;
  text-align: left;
  padding-right: 10px;
}

.responsive-btn:hover .svgWrapper {
  width: 45px;
  transition-duration: 0.3s;
  padding-left: 0;
}

.responsive-btn:hover .text {
  opacity: 1;
  transition-duration: 0.3s;
}

.responsive-btn:active {
  transform: translate(2px, 2px);
}

/* Button variants */
.compose-btn {
  background-color: #1a4a9b;
}

.compose-btn:hover {
  background-color: #1a4a9b;
}

.back-btn {
  background-color: #1a4a9b;
}

.back-btn:hover {
  background-color: #1a4a9b;
}

.edit-btn {
  background-color: #1a4a9b;
}

.edit-btn:hover {
  background-color: #1a4a9b;
}

/* Compose Panel */
.compose-panel {
  background: #ffffff;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
  height: 100%;
  margin-bottom: 24px;
}

.panel-header {
  margin-bottom: 32px;
  text-align: center;
}

.panel-header h5 {
  font-size: 20px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px 0;
  letter-spacing: -0.02em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.panel-subtitle {
  font-size: 14px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
  letter-spacing: 0.02em;
  opacity: 0.85;
}

/* Form Sections */
.form-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-label {
  font-size: 15px;
  font-weight: 600;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 8px;
  letter-spacing: 0.01em;
}

.form-label i {
  color: #3b82f6;
  font-size: 16px;
}

.form-help {
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
  margin: 0;
}

/* Form Inputs */
.form-input {
  padding: 16px 20px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 15px;
  color: #1e293b;
  background: #ffffff;
  transition: all 0.3s ease;
  font-weight: 500;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

.form-input::placeholder {
  color: #94a3b8;
  font-weight: 400;
}

/* Message Editor */
.message-editor {
  position: relative;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.message-editor:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

.form-textarea {
  width: 100%;
  border: none;
  padding: 20px;
  font-size: 15px;
  color: #1e293b;
  background: #ffffff;
  resize: vertical;
  min-height: 200px;
  font-family: inherit;
  line-height: 1.6;
}

.form-textarea:focus {
  outline: none;
}

.form-textarea::placeholder {
  color: #94a3b8;
  font-weight: 400;
}

/* Editor Toolbar */
.editor-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px 20px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  flex-wrap: wrap;
}

.toolbar-group {
  display: flex;
  gap: 4px;
  align-items: center;
}

.toolbar-divider {
  width: 1px;
  height: 24px;
  background: #e2e8f0;
  margin: 0 4px;
}

.toolbar-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  background: #ffffff;
  color: #64748b;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e2e8f0;
  position: relative;
}

.toolbar-btn:hover {
  background: #3b82f6;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  border-color: #3b82f6;
}

.toolbar-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.toolbar-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
}

.toolbar-select {
  padding: 6px 8px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #ffffff;
  color: #64748b;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 80px;
}

.toolbar-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.toolbar-color {
  width: 32px;
  height: 32px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #ffffff;
}

.toolbar-color:hover {
  border-color: #3b82f6;
  transform: scale(1.05);
}

.toolbar-color::-webkit-color-swatch-wrapper {
  padding: 0;
}

.toolbar-color::-webkit-color-swatch {
  border: none;
  border-radius: 4px;
}

/* Editor Content Area */
.editor-content {
  min-height: 200px;
  padding: 20px;
  border: none;
  outline: none;
  font-size: 15px;
  color: #1e293b;
  background: #ffffff;
  font-family: inherit;
  line-height: 1.6;
  overflow-y: auto;
  position: relative;
}

.editor-content:empty:before {
  content: attr(data-placeholder);
  color: #94a3b8;
  font-style: italic;
  pointer-events: none;
}

.editor-content:focus {
  outline: none;
}

.editor-content:focus:empty:before {
  display: none;
}

/* Rich Text Content Styles */
.editor-content h1,
.editor-content h2,
.editor-content h3,
.editor-content h4,
.editor-content h5,
.editor-content h6 {
  margin: 16px 0 8px 0;
  color: #1e293b;
  font-weight: 600;
  line-height: 1.3;
}

.editor-content h1 { font-size: 24px; }
.editor-content h2 { font-size: 20px; }
.editor-content h3 { font-size: 18px; }
.editor-content h4 { font-size: 16px; }
.editor-content h5 { font-size: 14px; }
.editor-content h6 { font-size: 13px; }

.editor-content p {
  margin: 0 0 12px 0;
  line-height: 1.6;
}

.editor-content ul,
.editor-content ol {
  margin: 12px 0;
  padding-left: 24px;
}

.editor-content li {
  margin: 4px 0;
  line-height: 1.5;
}

.editor-content blockquote {
  margin: 16px 0;
  padding: 12px 20px;
  border-left: 4px solid #3b82f6;
  background: #f8fafc;
  font-style: italic;
  color: #64748b;
}

.editor-content table {
  width: 100%;
  border-collapse: collapse;
  margin: 16px 0;
}

.editor-content table td,
.editor-content table th {
  border: 1px solid #e2e8f0;
  padding: 8px 12px;
  text-align: left;
}

.editor-content table th {
  background: #f8fafc;
  font-weight: 600;
  color: #374151;
}

.editor-content img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 8px 0;
}

.editor-content a {
  color: #3b82f6;
  text-decoration: underline;
  transition: color 0.3s ease;
}

.editor-content a:hover {
  color: #1d4ed8;
}

/* Recipient Options */
.recipient-options {
  display: flex;
  flex-direction: row;
  gap: 16px;
}

/* Staff Category Section */
.staff-category-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
}

.committees-section {
  margin-top: 24px;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
}

.committees-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.section-title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.section-title:before {
  content: '';
  width: 4px;
  height: 20px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  border-radius: 2px;
}

.staff-category-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.option-card {
  position: relative;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
  overflow: hidden;
  flex: 1 1 0;
}

.option-card:hover {
  border-color: #3b82f6;
  background: #f8fafc;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.option-radio {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.option-label {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.option-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  flex-shrink: 0;
}

.option-content {
  flex: 1;
  min-width: 0;
}

.option-content strong {
  display: block;
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 4px;
  letter-spacing: 0.01em;
}

.option-desc {
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
  line-height: 1.4;
}

.option-radio:checked + .option-label {
  border-color: #3b82f6;
  background: #f0f9ff;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.option-radio:checked + .option-label .option-icon {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

/* Selected card state */
.option-card.selected {
  border-color: #3b82f6 !important;
  background: #f0f9ff !important;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15) !important;
  transform: translateY(-2px);
}

.option-card.selected .option-icon {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
}

/* User Selector */
.user-selector {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin-top: 16px;
}

.user-selector-header {
  padding: 24px;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  color: white;
}

.search-container {
  margin-bottom: 20px;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 4px;
  transition: all 0.3s ease;
}

.search-input-wrapper:focus-within {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 12px 16px;
  font-size: 14px;
  color: white;
  outline: none;
  font-weight: 500;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.search-icon {
  color: rgba(255, 255, 255, 0.8);
  margin-right: 12px;
  font-size: 16px;
}

.user-stats {
  text-align: center;
  font-size: 14px;
  opacity: 0.9;
}

.stats-info {
  margin-bottom: 12px;
  font-weight: 500;
}

.selected-count {
  font-weight: 700;
  color: #10b981;
}

.progress-bar-container {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  height: 8px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #059669);
  width: 0%;
  transition: width 0.8s ease;
  border-radius: 10px;
}

.user-list-container {
    max-height: 300px;
    overflow-y: auto;
    background: #f8f9fa;
}

.user-list {
    padding: 0;
    margin: 0;
}

.user-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
    cursor: pointer;
}

.user-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.user-item:last-child {
    border-bottom: none;
}

.user-avatar {
    margin-right: 15px;
    flex-shrink: 0;
}

.avatar-img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.avatar-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 4px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.name-separator {
    color: #dee2e6;
    font-weight: 300;
    padding: 0 0.25rem;
}

.position-badge-small {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 600;
    background: rgba(196, 181, 253, 0.8);
    color: #5b21b6;
    border: 1px solid rgba(139, 92, 246, 0.3);
    white-space: nowrap;
}

.position-badge-small i {
    font-size: 0.65rem;
}

.user-email {
    color: #6c757d;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 4px;
}

.user-status {
    display: flex;
    align-items: center;
    gap: 6px;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #28a745;
    animation: pulse 2s infinite;
}

.status-indicator.online {
    background: #28a745;
}

.status-indicator.offline {
    background: #6c757d;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

.user-select-checkbox {
    position: relative;
    margin-left: 15px;
}

.user-checkbox {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 22px;
    width: 22px;
    z-index: 2;
}

.checkmark {
    height: 22px;
    width: 22px;
    background-color: #fff;
    border: 2px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    z-index: 1;
}

.user-checkbox:checked ~ .checkmark {
    background-color: #28a745;
    border-color: #28a745;
}

.checkmark:after {
    content: '';
    display: none;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.user-checkbox:checked ~ .checkmark:after {
    display: block;
}

/* Ensure the checkbox container is properly positioned */
.user-select-checkbox {
    position: relative;
    margin-left: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    min-height: 22px;
}

/* Make sure checkboxes are clickable */
.user-checkbox:focus + .checkmark {
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
}

/* Hover effect for better UX */
.user-item:hover .checkmark {
    border-color: #007bff;
}

/* Ensure the checkbox is properly layered */
.user-select-checkbox input[type="checkbox"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    opacity: 0;
    cursor: pointer;
}

.user-actions {
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 10px;
}

.user-actions .btn {
    flex: 1;
    font-size: 12px;
    padding: 8px 12px;
}

/* Sending options inline layout */
.sending-options {
  display: flex;
  gap: 12px;
  align-items: stretch;
  flex-wrap: nowrap;
}

.sending-options .option-card {
  flex: 1 1 0;
}

.sending-options .form-check {
    margin-bottom: 15px;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.sending-options .form-check:hover {
    border-color: #28a745;
    background-color: #f8f9fa;
}

.schedule-datetime {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

/* File Upload */
.file-upload-area {
  position: relative;
  border: 2px dashed #cbd5e1;
  border-radius: 16px;
  padding: 40px 30px;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
  background: #f8fafc;
}

.file-upload-area:hover {
  border-color: #3b82f6;
  background: #f0f9ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.file-upload-area.dragover {
  border-color: #3b82f6;
  background: #dbeafe;
  transform: scale(1.02);
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.file-upload-icon {
  font-size: 56px;
  color: #3b82f6;
  margin-bottom: 16px;
  opacity: 0.8;
}

.file-list {
    max-height: 200px;
    overflow-y: auto;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    margin-bottom: 5px;
    background: #f8f9fa;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-size {
    font-size: 12px;
    color: #6c757d;
}

.preview-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    font-size: 14px;
}

.preview-subject {
    font-weight: bold;
    margin-bottom: 10px;
    color: #495057;
}

.preview-message {
    margin-bottom: 10px;
    color: #6c757d;
    max-height: 100px;
    overflow-y: auto;
}

.preview-recipients {
    font-size: 12px;
    color: #28a745;
    font-weight: bold;
}

/* Form Actions */
.form-actions {
  display: flex;
  flex-direction: row;
  gap: 12px;
}

.action-btn {
  padding: 16px 24px;
  border: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  letter-spacing: 0.02em;
  text-decoration: none;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.send-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.draft-btn {
  background: linear-gradient(135deg, #64748b 0%, #475569 100%);
  color: white;
}

/* Send Memo Confirmation Modal */
.memo-confirm-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10000;
  animation: fadeIn 0.3s ease;
}

.memo-confirm-modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
}

.memo-confirm-modal-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  animation: fadeIn 0.3s ease;
}

.memo-confirm-modal-content {
  position: relative;
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  z-index: 10001;
}

.memo-confirm-modal-header {
  text-align: center;
  padding: 32px 32px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.memo-confirm-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 20px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
  animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.memo-confirm-icon i {
  font-size: 36px;
  color: white;
}

.memo-confirm-modal-header h3 {
  font-size: 24px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  letter-spacing: -0.02em;
}

.memo-confirm-modal-body {
  padding: 24px 32px;
}

.memo-confirm-modal-body > p:first-child {
  font-size: 16px;
  color: #475569;
  text-align: center;
  margin: 0 0 24px;
  font-weight: 500;
  line-height: 1.6;
}

.memo-preview-info {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  border: 1px solid #e2e8f0;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid #e2e8f0;
}

.info-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.info-item i {
  font-size: 18px;
  color: #3b82f6;
  width: 24px;
  flex-shrink: 0;
}

.info-item span {
  font-size: 14px;
  color: #1e293b;
  font-weight: 500;
  word-break: break-word;
}

.memo-warning-text {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px;
  background: #fef3c7;
  border: 1px solid #fcd34d;
  border-radius: 12px;
  color: #92400e;
  font-size: 14px;
  margin: 0;
  font-weight: 500;
}

.memo-warning-text i {
  font-size: 20px;
  color: #f59e0b;
  flex-shrink: 0;
}

.memo-confirm-modal-footer {
  display: flex;
  gap: 12px;
  padding: 24px 32px 32px;
  border-top: 1px solid #e2e8f0;
}

.memo-confirm-btn {
  flex: 1;
  padding: 14px 24px;
  border: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  letter-spacing: 0.02em;
}

.memo-cancel-btn {
  background: #f1f5f9;
  color: #64748b;
  border: 2px solid #e2e8f0;
}

.memo-cancel-btn:hover {
  background: #e2e8f0;
  color: #475569;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.memo-send-confirm-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.memo-send-confirm-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.memo-send-confirm-btn:active {
  transform: translateY(0);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.5);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Responsive Modal */
@media (max-width: 576px) {
  .memo-confirm-modal-content {
    width: 95%;
    max-height: 95vh;
  }
  
  .memo-confirm-modal-header {
    padding: 24px 24px 20px;
  }
  
  .memo-confirm-icon {
    width: 64px;
    height: 64px;
  }
  
  .memo-confirm-icon i {
    font-size: 28px;
  }
  
  .memo-confirm-modal-header h3 {
    font-size: 20px;
  }
  
  .memo-confirm-modal-body {
    padding: 20px 24px;
  }
  
  .memo-confirm-modal-footer {
    flex-direction: column;
    padding: 20px 24px 24px;
  }
  
  .memo-confirm-btn {
    width: 100%;
  }
}

/* Preview Card */
.preview-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  font-size: 14px;
}

.preview-header {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
}

.preview-subject {
  font-weight: 700;
  margin-bottom: 8px;
  color: #1e293b;
  font-size: 16px;
  letter-spacing: 0.01em;
}

.preview-recipients {
  font-size: 13px;
  color: #10b981;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.preview-message {
  color: #64748b;
  line-height: 1.6;
  max-height: 120px;
  overflow-y: auto;
  font-size: 13px;
}

/* Custom scrollbar for user list */
.user-list-container::-webkit-scrollbar {
  width: 6px;
}

.user-list-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.user-list-container::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.user-list-container::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Animation for user items */
.user-item {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard__section__actions {
    flex-direction: column;
    gap: 10px;
    align-items: stretch;
  }
  
  .responsive-btn {
    width: 100%;
    height: 50px;
    border-radius: 25px;
    justify-content: center;
  }
  
  .responsive-btn:hover {
    width: 100%;
  }
  
  .responsive-btn .text {
    position: static;
    opacity: 1;
    width: auto;
    margin-left: 15px;
  }
  
  .responsive-btn .svgWrapper {
    width: auto;
  }
  
  .compose-panel {
    padding: 24px;
    margin-bottom: 16px;
  }
  
  .panel-header h5 {
    font-size: 18px;
  }
  
  .form-input,
  .form-textarea {
    padding: 14px 16px;
    font-size: 14px;
  }
  
  .message-editor {
    min-height: 180px;
  }
  
  .editor-toolbar {
    padding: 10px 16px;
    gap: 6px;
  }
  
  .toolbar-group {
    gap: 2px;
  }
  
  .toolbar-btn {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
  
  .toolbar-divider {
    height: 20px;
    margin: 0 2px;
  }
  
  .toolbar-select {
    min-width: 70px;
    font-size: 11px;
    padding: 4px 6px;
  }
  
  .toolbar-color {
    width: 28px;
    height: 28px;
  }
  
  .option-label {
    padding: 16px;
    gap: 12px;
  }
  
  .option-icon {
    width: 40px;
    height: 40px;
    font-size: 18px;
  }
  
  .user-selector-header {
    padding: 20px;
  }
  
  .search-input-wrapper {
    padding: 2px;
  }
  
  .search-input {
    padding: 10px 14px;
    font-size: 13px;
  }
  
  .user-item {
    padding: 12px 16px;
  }
  
  .avatar-img, .avatar-placeholder {
    width: 40px;
    height: 40px;
    font-size: 14px;
  }
  
  .user-name {
    font-size: 14px;
  }
  
  .user-email {
    font-size: 12px;
  }
  
  .action-btn {
    padding: 14px 20px;
    font-size: 14px;
  }
  
  .file-upload-area {
    padding: 30px 20px;
  }
  
  .file-upload-icon {
    font-size: 48px;
  }
}

@media (max-width: 576px) {
  .compose-panel {
    padding: 20px;
  }
  
  .panel-header {
    margin-bottom: 24px;
  }
  
  .form-section {
    gap: 20px;
  }
  
  .option-label {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
  
  .user-selector-header {
    padding: 16px;
  }
  
  .user-actions {
    flex-direction: column;
    gap: 8px;
  }
  
  .user-actions .btn {
    width: 100%;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const recipientRadios = document.querySelectorAll('input[name="recipient_type"]');
    const userSelector = document.getElementById('user-selector');
    const userSearch = document.getElementById('user-search');
    const userList = document.getElementById('user-list');
    const userItems = document.querySelectorAll('.user-item');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const selectAllBtn = document.getElementById('select-all-btn');
    const clearAllBtn = document.getElementById('clear-all-btn');
    const exportSelectedBtn = document.getElementById('export-selected-btn');
    const selectedCountSpan = document.querySelector('.selected-count');
    
    const sendOptionRadios = document.querySelectorAll('input[name="send_option"]');
    const scheduleDateTime = document.getElementById('schedule-datetime');
    
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    const previewSubject = document.getElementById('preview-subject');
    const previewMessage = document.getElementById('preview-message');
    const previewRecipients = document.getElementById('preview-recipients');
    
    // Handle recipient type changes
    recipientRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Update visual state for all cards
            updateCardStates();
            
            if (this.value === 'selected') {
                userSelector.style.display = 'block';
                updateSelectedCount();
                updatePreview();
            } else {
                userSelector.style.display = 'none';
                updatePreview();
            }
        });
    });
    
    // Function to update card visual states
    function updateCardStates() {
        recipientRadios.forEach(radio => {
            const card = radio.closest('.option-card');
            if (radio.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }
    
    // Initialize card states on page load
    updateCardStates();
    
    // Handle send option changes
    sendOptionRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'schedule') {
                scheduleDateTime.style.display = 'block';
                document.getElementById('scheduled_at').required = true;
            } else {
                scheduleDateTime.style.display = 'none';
                document.getElementById('scheduled_at').required = false;
            }
        });
    });
    
    // Search functionality
    userSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        userItems.forEach(item => {
            const searchData = item.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                item.style.display = 'flex';
                item.style.animation = 'slideIn 0.3s ease-out';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // User selection handling
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            updatePreview();
        });
        
        // Also add click event to the user item for better UX
        const userItem = checkbox.closest('.user-item');
        if (userItem) {
            userItem.addEventListener('click', function(e) {
                // Don't trigger if clicking on the checkbox itself
                if (e.target !== checkbox && !checkbox.contains(e.target)) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        }
    });
    
    // Select all functionality
    selectAllBtn.addEventListener('click', function() {
        const visibleCheckboxes = Array.from(userCheckboxes).filter(checkbox => {
            const userItem = checkbox.closest('.user-item');
            return userItem.style.display !== 'none';
        });
        
        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        
        updateSelectedCount();
        updatePreview();
    });
    
    // Clear all functionality
    clearAllBtn.addEventListener('click', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        updateSelectedCount();
        updatePreview();
    });
    
    // Export selected users functionality
    exportSelectedBtn.addEventListener('click', function() {
        const selectedUsers = Array.from(userCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => {
                const userItem = checkbox.closest('.user-item');
                const name = userItem.querySelector('.user-name').textContent;
                const email = userItem.querySelector('.user-email').textContent;
                return { name, email };
            });
        
        if (selectedUsers.length === 0) {
            alert('No users selected to export');
            return;
        }
        
        // Create CSV content
        const csvContent = 'Name,Email\n' + 
            selectedUsers.map(user => `"${user.name}","${user.email}"`).join('\n');
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `selected_users_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        // Show success message
        this.innerHTML = '<i class="icofont-check-circled"></i> Exported!';
        setTimeout(() => {
            this.innerHTML = '<i class="icofont-download"></i> Export';
        }, 2000);
    });
    
    // Update selected count
    function updateSelectedCount() {
        const selectedCount = Array.from(userCheckboxes).filter(checkbox => checkbox.checked).length;
        const totalCount = userCheckboxes.length;
        const progressPercentage = totalCount > 0 ? (selectedCount / totalCount) * 100 : 0;
        
        selectedCountSpan.textContent = selectedCount;
        
        // Update progress bar
        const progressBar = document.getElementById('selection-progress');
        if (progressBar) {
            progressBar.style.width = progressPercentage + '%';
        }
        
        // Update button states
        if (selectedCount > 0) {
            clearAllBtn.disabled = false;
            exportSelectedBtn.disabled = false;
        } else {
            clearAllBtn.disabled = true;
            exportSelectedBtn.disabled = true;
        }
    }
    
    // Handle file uploads
    const fileInput = document.getElementById('attachments');
    const fileList = document.getElementById('file-list');
    const fileUploadArea = document.querySelector('.file-upload-area');
    
    // Store accumulated files globally
    window.accumulatedFiles = [];
    
    fileInput.addEventListener('change', function(e) {
        const newFiles = Array.from(e.target.files);
        
        // Add new files to accumulated files
        newFiles.forEach(file => {
            if (window.accumulatedFiles.length < 3) {
                window.accumulatedFiles.push(file);
            }
        });
        
        // Check limit
        if (window.accumulatedFiles.length > 3) {
            alert('Maximum 3 files allowed. Only the first 3 files will be kept.');
            window.accumulatedFiles = window.accumulatedFiles.slice(0, 3);
        }
        
        // Update the file input with accumulated files
        const dt = new DataTransfer();
        window.accumulatedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        
        handleFileSelect();
    });
    
    // Drag and drop functionality
    fileUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    fileUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    fileUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        fileInput.files = e.dataTransfer.files;
        handleFileSelect();
    });
    
    function handleFileSelect() {
        fileList.innerHTML = '';
        const files = Array.from(fileInput.files);
        
        // Limit to maximum 3 files
        if (files.length > 3) {
            alert('Maximum 3 files allowed. Only the first 3 files will be kept.');
            const dt = new DataTransfer();
            files.slice(0, 3).forEach(file => dt.items.add(file));
            fileInput.files = dt.files;
        }
        
        Array.from(fileInput.files).forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-info">
                    <i class="icofont-file-alt"></i>
                    <span>${file.name}</span>
                    <span class="file-size">(${formatFileSize(file.size)})</span>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                    <i class="icofont-close"></i>
                </button>
            `;
            fileList.appendChild(fileItem);
        });
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Rich Text Editor Functionality
    const editorContent = document.getElementById('editor-content');
    const messageTextarea = document.getElementById('message');
    const toolbarButtons = document.querySelectorAll('.toolbar-btn');
    
    // Initialize editor content
    if (editorContent.innerHTML.trim() === '') {
        editorContent.innerHTML = '';
    }
    
    // Handle toolbar button clicks
    toolbarButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const command = this.getAttribute('data-command');
            
            if (command) {
                executeCommand(command);
                updateTextareaContent();
                updatePreview();
            }
        });
    });
    
    // Execute formatting commands
    function executeCommand(command) {
        switch(command) {
            case 'createLink':
                const url = prompt('Enter URL:', 'https://');
                if (url) {
                    document.execCommand('createLink', false, url);
                }
                break;
            case 'insertImage':
                const imageUrl = prompt('Enter image URL:', 'https://');
                if (imageUrl) {
                    document.execCommand('insertImage', false, imageUrl);
                }
                break;
            case 'insertTable':
                const rows = prompt('Enter number of rows:', '3');
                const cols = prompt('Enter number of columns:', '3');
                if (rows && cols) {
                    insertTable(parseInt(rows), parseInt(cols));
                }
                break;
            case 'insertHorizontalRule':
                document.execCommand('insertHorizontalRule', false);
                break;
            default:
                document.execCommand(command, false, null);
                break;
        }
        
        // Update button states
        updateButtonStates();
    }
    
    // Insert table function
    function insertTable(rows, cols) {
        let tableHTML = '<table border="1" style="border-collapse: collapse; width: 100%;">';
        
        for (let i = 0; i < rows; i++) {
            tableHTML += '<tr>';
            for (let j = 0; j < cols; j++) {
                if (i === 0) {
                    tableHTML += '<th style="padding: 8px; border: 1px solid #ccc; background: #f8f9fa;">Header ' + (j + 1) + '</th>';
                } else {
                    tableHTML += '<td style="padding: 8px; border: 1px solid #ccc;">Cell ' + (i + 1) + '-' + (j + 1) + '</td>';
                }
            }
            tableHTML += '</tr>';
        }
        
        tableHTML += '</table>';
        document.execCommand('insertHTML', false, tableHTML);
    }
    
    // Update button states based on current selection
    function updateButtonStates() {
        toolbarButtons.forEach(button => {
            const command = button.getAttribute('data-command');
            if (['bold', 'italic', 'underline', 'strikeThrough'].includes(command)) {
                if (document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            }
        });
    }
    
    // Update textarea content for form submission
    function updateTextareaContent() {
        messageTextarea.value = editorContent.innerHTML;
    }
    
    // Handle editor content changes
    editorContent.addEventListener('input', function() {
        updateTextareaContent();
        updatePreview();
    });
    
    editorContent.addEventListener('keyup', function() {
        updateButtonStates();
    });
    
    // Handle keyboard shortcuts
    editorContent.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key.toLowerCase()) {
                case 'b':
                    e.preventDefault();
                    executeCommand('bold');
                    break;
                case 'i':
                    e.preventDefault();
                    executeCommand('italic');
                    break;
                case 'u':
                    e.preventDefault();
                    executeCommand('underline');
                    break;
                case 'z':
                    if (e.shiftKey) {
                        e.preventDefault();
                        executeCommand('redo');
                    } else {
                        e.preventDefault();
                        executeCommand('undo');
                    }
                    break;
            }
        }
    });
    
    // Font size and color controls
    const fontSizeSelect = document.getElementById('fontSize');
    const textColorInput = document.getElementById('textColor');
    
    fontSizeSelect.addEventListener('change', function() {
        document.execCommand('fontSize', false, this.value);
        updateTextareaContent();
        updatePreview();
    });
    
    textColorInput.addEventListener('change', function() {
        document.execCommand('foreColor', false, this.value);
        updateTextareaContent();
        updatePreview();
    });
    
    // Preview functionality
    function updatePreview() {
        if (!previewSubject || !previewMessage || !previewRecipients) {
            return;
        }
        const subject = subjectInput.value || 'Subject will appear here';
        const message = messageTextarea.value || 'Message preview will appear here';
        
        previewSubject.textContent = subject;
        previewMessage.innerHTML = message.substring(0, 200) + (message.length > 200 ? '...' : '');
        
        const recipientType = document.querySelector('input[name="recipient_type"]:checked').value;
        if (recipientType === 'all') {
            previewRecipients.textContent = `Recipients: All users ({{ $users->count() }} users)`;
        } else {
            const selectedCount = Array.from(userCheckboxes).filter(checkbox => checkbox.checked).length;
            previewRecipients.textContent = `Recipients: ${selectedCount} selected users`;
        }
    }
    
    subjectInput.addEventListener('input', updatePreview);
    editorContent.addEventListener('input', updatePreview);
    
    // Initialize
    if (document.getElementById('selected_users').checked) {
        userSelector.style.display = 'block';
    }
    
    if (document.getElementById('schedule_send').checked) {
        scheduleDateTime.style.display = 'block';
    }
    
    // Initialize button states
    exportSelectedBtn.disabled = true;
    
    updateSelectedCount();
    updatePreview();
    
    // Send Memo Button - Show Confirmation Modal
    const sendMemoBtn = document.getElementById('send-memo-btn');
    const sendMemoModal = document.getElementById('sendMemoModal');
    const cancelSendBtn = document.getElementById('cancelSendBtn');
    const confirmSendBtn = document.getElementById('confirmSendBtn');
    const modalSubjectPreview = document.getElementById('modal-subject-preview');
    const modalRecipientsPreview = document.getElementById('modal-recipients-preview');
    
    function updateModalPreview() {
        const subject = subjectInput.value || 'No subject';
        modalSubjectPreview.textContent = subject.length > 50 ? subject.substring(0, 50) + '...' : subject;
        
        const recipientType = document.querySelector('input[name="recipient_type"]:checked');
        if (recipientType) {
            if (recipientType.value === 'all') {
                modalRecipientsPreview.textContent = `All Registered Users ({{ $users->count() }} users)`;
            } else if (recipientType.value === 'selected') {
                const selectedCount = Array.from(userCheckboxes).filter(checkbox => checkbox.checked).length;
                modalRecipientsPreview.textContent = `${selectedCount} Selected User${selectedCount !== 1 ? 's' : ''}`;
            } else if (recipientType.value.startsWith('committee_')) {
                // Committee/Board
                const committeeName = recipientType.getAttribute('data-committee-name') || 'Committee';
                const memberCount = recipientType.getAttribute('data-committee-members') || '0';
                modalRecipientsPreview.textContent = `${committeeName} (${memberCount} member${memberCount !== '1' ? 's' : ''})`;
            } else {
                // Staff category
                const categoryLabels = {
                    'junior_staff': 'Junior Staff',
                    'senior_staff': 'Senior Staff',
                    'senior_member_non_teaching': 'Senior Member (Non-Teaching)',
                    'senior_member_teaching': 'Senior Member (Teaching)'
                };
                modalRecipientsPreview.textContent = categoryLabels[recipientType.value] || recipientType.value;
            }
        }
    }
    
    sendMemoBtn.addEventListener('click', function(e) {
        e.preventDefault();
        updateModalPreview();
        sendMemoModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    });
    
    function closeModal() {
        sendMemoModal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    cancelSendBtn.addEventListener('click', closeModal);
    
    sendMemoModal.addEventListener('click', function(e) {
        if (e.target === sendMemoModal || e.target.classList.contains('memo-confirm-modal-overlay')) {
            closeModal();
        }
    });
    
    confirmSendBtn.addEventListener('click', function() {
        // Ensure the textarea has the latest content
        updateTextareaContent();
        
        // Create hidden input for action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'send';
        document.getElementById('emailForm').appendChild(actionInput);
        
        // Set the hidden input for send_immediately
        const sendImmediatelyInput = document.createElement('input');
        sendImmediatelyInput.type = 'hidden';
        sendImmediatelyInput.name = 'send_immediately';
        sendImmediatelyInput.value = document.getElementById('send_now').checked ? '1' : '0';
        document.getElementById('emailForm').appendChild(sendImmediatelyInput);
        
        // Show loading state
        confirmSendBtn.disabled = true;
        confirmSendBtn.innerHTML = '<i class="icofont-spinner fa-spin"></i> Sending...';
        
        // Submit the form
        document.getElementById('emailForm').submit();
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sendMemoModal.classList.contains('show')) {
            closeModal();
        }
    });
    
    // Form submission (for draft button)
    document.getElementById('emailForm').addEventListener('submit', function(e) {
        // Only handle draft submissions here, send is handled by modal
        if (e.submitter && e.submitter.value === 'draft') {
            // Ensure the textarea has the latest content
            updateTextareaContent();
            
            // Show loading state
            e.submitter.disabled = true;
            e.submitter.innerHTML = '<i class="icofont-spinner fa-spin"></i> Processing...';
        }
    });
});

// Make functions globally accessible
window.removeFile = function(index) {
    const fileInput = document.getElementById('attachments');
    
    // Remove from accumulated files array
    if (window.accumulatedFiles) {
        window.accumulatedFiles.splice(index, 1);
    } else {
        // Fallback if accumulatedFiles not available
        window.accumulatedFiles = Array.from(fileInput.files);
        window.accumulatedFiles.splice(index, 1);
    }
    
    // Update the file input
    const dt = new DataTransfer();
    window.accumulatedFiles.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
    
    // Re-render file list with updated indices
    const fileList = document.getElementById('file-list');
    fileList.innerHTML = '';
    Array.from(fileInput.files).forEach((file, newIndex) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="file-info">
                <i class="icofont-file-alt"></i>
                <span>${file.name}</span>
                <span class="file-size">(${formatFileSize(file.size)})</span>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeFile(${newIndex})">
                <i class="icofont-close"></i>
            </button>
        `;
        fileList.appendChild(fileItem);
    });
}

window.formatFileSize = function(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endsection