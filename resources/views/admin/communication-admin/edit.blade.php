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
                            <h4>Edit Memo</h4>
                            <div class="dashboard__section__actions">
                                <a href="{{route('admin.communication-admin.show', $campaign)}}" class="responsive-btn back-btn">
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
                                        <div class="text">Back</div>
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

                        <form method="POST" action="{{ route('admin.communication-admin.update', $campaign) }}" enctype="multipart/form-data" id="emailForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-xl-8">
                                    <div class="dashboard__form__wraper">
                                        

                                        <div class="dashboard__form__input">
                                            <label for="subject">Memo Subject <span class="required">*</span></label>
                                            <input type="text" id="subject" name="subject" 
                                                   placeholder="Enter memo subject" 
                                                   value="{{ old('subject', $campaign->subject) }}" required>
                                            <small class="form-text text-muted">This will be the memo subject line users see</small>
                                        </div>

                                        <div class="dashboard__form__input">
                                            <label for="message">Memo Message <span class="required">*</span></label>
                                            <textarea id="message" name="message" rows="12" 
                                                      placeholder="Enter your memo message..." required>{{ old('message', $campaign->message) }}</textarea>
                                            <small class="form-text text-muted">HTML formatting is supported</small>
                                        </div>

                                        <!-- Existing Attachments -->
                                        @if($campaign->attachments && count($campaign->attachments) > 0)
                                            <div class="dashboard__form__input">
                                                <label>Current Attachments</label>
                                                <div class="existing-attachments">
                                                    @foreach($campaign->attachments as $index => $attachment)
                                                        <div class="attachment-item" id="attachment-{{ $index }}">
                                                            <div class="attachment-info">
                                                                <i class="icofont-file-alt"></i>
                                                                <span class="attachment-name">{{ $attachment['name'] }}</span>
                                                                <span class="attachment-size">({{ number_format($attachment['size'] / 1024, 1) }} KB)</span>
                                                            </div>
                                                            <div class="attachment-actions">
                                                                <a href="{{ route('admin.communication-admin.download-attachment', [$campaign, $index]) }}" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="icofont-download"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-danger" 
                                                                        onclick="removeExistingAttachment({{ $index }})">
                                                                    <i class="icofont-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- New Attachments -->
                                        <div class="dashboard__form__input">
                                            <label for="attachments">Add New Attachments</label>
                                            <div class="file-upload-area">
                                                <input type="file" id="attachments" name="attachments[]" 
                                                       multiple accept=".pdf,.doc,.docx,.txt,.jpg,.png,.gif,.zip"
                                                       class="file-input">
                                                <div class="file-upload-display">
                                                    <div class="file-upload-icon">
                                                        <i class="icofont-attachment"></i>
                                                    </div>
                                                    <div class="file-upload-text">
                                                        <strong>Click to upload files</strong> or drag and drop
                                                        <br><small>Supported: PDF, DOC, DOCX, TXT, JPG, PNG, GIF, ZIP (Max: 10MB each)</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="file-list" class="file-list mt-3"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4">
                                    <div class="dashboard__form__wraper">
                                        <h5><i class="icofont-users"></i> Recipients</h5>
                                        
                                        <div class="recipient-options">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="all_users" value="all" 
                                                       {{ old('recipient_type', $campaign->recipient_type) === 'all' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="all_users">
                                                    <strong>All Registered Users</strong>
                                                    <br><small class="text-muted">Send to all approved users ({{ $users->count() }} users)</small>
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="selected_users" value="selected"
                                                       {{ old('recipient_type', $campaign->recipient_type) === 'selected' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="selected_users">
                                                    <strong>Selected Users</strong>
                                                    <br><small class="text-muted">Choose specific users to send to</small>
                                                </label>
                                            </div>
                                        </div>

                                        <div id="user-selector" class="user-selector mt-3" style="display: {{ old('recipient_type', $campaign->recipient_type) === 'selected' ? 'block' : 'none' }};">
                                            <div class="user-selector-header">
                                                <label for="user-search">Search & Select Users:</label>
                                                <div class="search-container">
                                                    <input type="text" id="user-search" 
                                                           placeholder="Search by name or email..." 
                                                           class="form-control search-input">
                                                    <i class="icofont-search search-icon"></i>
                                                </div>
                                                <div class="user-stats">
                                                    <span class="selected-count">0</span> of <span class="total-count">{{ $users->count() }}</span> users selected
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
                                                                <div class="user-name">{{ $user->first_name }} {{ $user->last_name }}</div>
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
                                                                       {{ in_array($user->id, old('selected_users', $campaign->selected_users ?? [])) ? 'checked' : '' }}>
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

                                        <hr>

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="icofont-save"></i> Update Memo
                                            </button>
                                            <a href="{{ route('admin.communication-admin.show', $campaign) }}" 
                                               class="btn btn-secondary btn-block">
                                                <i class="icofont-close"></i> Cancel
                                            </a>
                                        </div>

                                        <div class="email-preview mt-3">
                                            <h6><i class="icofont-eye"></i> Memo Preview</h6>
                                            <div class="preview-card">
                                                <div class="preview-subject" id="preview-subject">{{ $campaign->subject }}</div>
                                                <div class="preview-message" id="preview-message">{{ Str::limit($campaign->message, 200) }}</div>
                                                <div class="preview-recipients" id="preview-recipients">
                                                    Recipients: {{ $campaign->recipient_type === 'all' ? 'All users' : count($campaign->selected_users ?? []) . ' selected users' }}
                                                </div>
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

<style>
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

.required { color: #e74c3c; }

.existing-attachments {
    margin-bottom: 20px;
}

.attachment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin-bottom: 10px;
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.attachment-name {
    font-weight: 600;
}

.attachment-size {
    color: #6c757d;
    font-size: 12px;
}

.attachment-actions {
    display: flex;
    gap: 5px;
}

.recipient-options .form-check {
    margin-bottom: 15px;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.recipient-options .form-check:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.recipient-options .form-check-input:checked + .form-check-label {
    color: #007bff;
}

.user-selector {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.user-selector-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.user-selector-header label {
    color: white;
    font-weight: 600;
    margin-bottom: 15px;
    display: block;
}

.search-container {
    position: relative;
    margin-bottom: 15px;
}

.search-input {
    padding: 12px 40px 12px 15px;
    border: none;
    border-radius: 25px;
    background: rgba(255,255,255,0.9);
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-input:focus {
    background: white;
    box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
    outline: none;
}

.search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 16px;
}

.user-stats {
    text-align: center;
    font-size: 14px;
    opacity: 0.9;
}

.selected-count {
    font-weight: bold;
    color: #28a745;
}

.progress-bar-container {
    margin-top: 10px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    height: 6px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    width: 0%;
    transition: width 0.3s ease;
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

.file-upload-area {
    position: relative;
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.file-upload-area.dragover {
    border-color: #007bff;
    background-color: #e3f2fd;
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
    font-size: 48px;
    color: #6c757d;
    margin-bottom: 10px;
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

.form-actions button {
    margin-bottom: 10px;
}

/* Custom scrollbar for user list */
.user-list-container::-webkit-scrollbar {
    width: 6px;
}

.user-list-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.user-list-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.user-list-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
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

/* Responsive design */
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
    
    .user-selector-header {
        padding: 15px;
    }
    
    .user-item {
        padding: 12px 15px;
    }
    
    .avatar-img, .avatar-placeholder {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
    
    .user-name {
        font-size: 13px;
    }
    
    .user-email {
        font-size: 11px;
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
    
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    const previewSubject = document.getElementById('preview-subject');
    const previewMessage = document.getElementById('preview-message');
    const previewRecipients = document.getElementById('preview-recipients');
    
    // Handle recipient type changes
    recipientRadios.forEach(radio => {
        radio.addEventListener('change', function() {
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
    
    fileInput.addEventListener('change', handleFileSelect);
    
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
    
    // Preview functionality
    function updatePreview() {
        const subject = subjectInput.value || 'Subject will appear here';
        const message = messageInput.value || 'Message preview will appear here';
        
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
    messageInput.addEventListener('input', updatePreview);
    
    // Initialize
    if (document.getElementById('selected_users').checked) {
        userSelector.style.display = 'block';
    }
    
    // Initialize button states
    exportSelectedBtn.disabled = true;
    
    updateSelectedCount();
    updatePreview();
    
    // Form submission
    document.getElementById('emailForm').addEventListener('submit', function(e) {
        // Show loading state
        e.target.querySelector('button[type="submit"]').disabled = true;
        e.target.querySelector('button[type="submit"]').innerHTML = '<i class="icofont-spinner fa-spin"></i> Updating...';
    });
});

function removeFile(index) {
    const fileInput = document.getElementById('attachments');
    const dt = new DataTransfer();
    const files = Array.from(fileInput.files);
    
    files.forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    fileInput.files = dt.files;
    handleFileSelect();
}

function removeExistingAttachment(index) {
    if (confirm('Are you sure you want to remove this attachment?')) {
        fetch(`{{ route('admin.communication-admin.remove-attachment', [$campaign, '__INDEX__']) }}`.replace('__INDEX__', index), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`attachment-${index}`).remove();
            } else {
                alert('Failed to remove attachment: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove attachment');
        });
    }
}
</script>
@endsection