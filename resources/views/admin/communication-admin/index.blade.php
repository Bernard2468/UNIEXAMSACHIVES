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
                            <h4>Advanced Communication System</h4>
                            <div class="dashboard__section__actions">
                                <a href="{{route('admin.communication-admin.create')}}" class="responsive-btn compose-btn">
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
                                                d="M12 5v14m-7-7h14"
                                            ></path>
                                        </svg>
                                        <div class="text">Compose Memo</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Memo Delivery Success Popup -->
                        @if(session('memo_delivered'))
                        <div id="memoSuccessPopup" class="memo-success-popup">
                            <div class="popup-container">
                                <div class="popup-content">
                                    <div class="popup-icon">
                                        <div class="pulse-ring"></div>
                                        <div class="pulse-ring pulse-ring-delay-1"></div>
                                        <div class="pulse-ring pulse-ring-delay-2"></div>
                                        <i class="icofont-check-circled success-icon"></i>
                                    </div>
                                    <h3 class="popup-title">Memo Delivered Successfully! 📧</h3>
                                    <p class="popup-message">Your memo has been sent to all recipients.</p>
                                    <div class="popup-details">
                                        <div class="detail-item">
                                            <i class="icofont-ui-check"></i>
                                            <span>{{ session('success') }}</span>
                                        </div>
                                    </div>
                                    <div class="security-details">
                                        <div class="detail-item security-message">
                                            <i class="icofont-shield"></i>
                                            <span>🔒 Security Verified: Steganography and crypto features are active. All activities are safely guided by the Meta IronDom Security System</span>
                                        </div>
                                    </div>
                                    <button type="button" class="popup-close-btn" onclick="closeMemoPopup()">
                                        <i class="icofont-close"></i> Close
                                    </button>
                                </div>
                                <div class="popup-confetti"></div>
                            </div>
                        </div>
                        @endif

                        <!-- Statistics Cards -->
                        <div class="row mb-5">
                            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="icofont-email"></i>
                                    </div>
                                    <div class="metric-content">
                                        <h3 class="metric-number">{{ $stats['total'] }}</h3>
                                        <p class="metric-label">Total Memos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="icofont-check-circled"></i>
                                    </div>
                                    <div class="metric-content">
                                        <h3 class="metric-number">{{ $stats['sent'] }}</h3>
                                        <p class="metric-label">Sent Memos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="icofont-edit"></i>
                                    </div>
                                    <div class="metric-content">
                                        <h3 class="metric-number">{{ $stats['draft'] }}</h3>
                                        <p class="metric-label">Draft Memos</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="icofont-users-alt-3"></i>
                                    </div>
                                    <div class="metric-content">
                                        <h3 class="metric-number">{{ $stats['failed'] }}</h3>
                                        <p class="metric-label">Failed Memos</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Filter Buttons -->
                        <div class="filter-section mb-4">
                            <div class="filter-buttons">
                                <a href="{{ route('admin.communication-admin.index') }}" 
                                   class="filter-btn {{ $statusFilter === 'all' ? 'active' : '' }}">
                                    <i class="icofont-list"></i> All ({{ $stats['total'] }})
                                </a>
                                <a href="{{ route('admin.communication-admin.index', ['status' => 'draft']) }}" 
                                   class="filter-btn {{ $statusFilter === 'draft' ? 'active' : '' }}">
                                    <i class="icofont-edit"></i> Drafts ({{ $stats['draft'] }})
                                </a>
                                <a href="{{ route('admin.communication-admin.index', ['status' => 'scheduled']) }}" 
                                   class="filter-btn {{ $statusFilter === 'scheduled' ? 'active' : '' }}">
                                    <i class="icofont-clock-time"></i> Scheduled ({{ $stats['scheduled'] }})
                                </a>
                                <a href="{{ route('admin.communication-admin.index', ['status' => 'sending']) }}" 
                                   class="filter-btn {{ $statusFilter === 'sending' ? 'active' : '' }}">
                                    <i class="icofont-spinner"></i> Sending ({{ $stats['sending'] }})
                                </a>
                                <a href="{{ route('admin.communication-admin.index', ['status' => 'sent']) }}" 
                                   class="filter-btn {{ $statusFilter === 'sent' ? 'active' : '' }}">
                                    <i class="icofont-check-circled"></i> Sent ({{ $stats['sent'] }})
                                </a>
                                <a href="{{ route('admin.communication-admin.index', ['status' => 'failed']) }}" 
                                   class="filter-btn {{ $statusFilter === 'failed' ? 'active' : '' }}">
                                    <i class="icofont-close-circled"></i> Failed ({{ $stats['failed'] }})
                                </a>
                            </div>
                        </div>

                        <!-- Memos Table -->
                        <div class="stats-panel">
                            <div class="panel-header">
                                <h5>Memo Campaigns
                                    @if($statusFilter !== 'all')
                                        <span class="filter-indicator">- {{ ucfirst($statusFilter) }} Only</span>
                                    @endif
                                </h5>
                                <p class="panel-subtitle">Manage and monitor your memo communications</p>
                            </div>

                            <div class="table-controls">
                                <form method="GET" class="search-form">
                                    <div class="search-input-wrapper">
                                        <i class="icofont-search-1 search-icon"></i>
                                        <input type="text" name="search" placeholder="Search memos..." 
                                               value="{{ request('search') }}" class="search-input">
                                        <button type="submit" class="search-button">Search</button>
                                    </div>
                                </form>
                            </div>

                            <div class="table-container">
                                <table class="email-table">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Subject</th>
                                            <th>Recipients</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($campaigns as $campaign)
                                        <tr class="email-row">
                                            <td>
                                                <span class="recipient-badge" title="Memo Reference">{{ $campaign->reference ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <div class="email-title">
                                                    <strong>{{ Str::limit($campaign->subject, 40) }}</strong>
                                                    @if($campaign->attachments && count($campaign->attachments) > 0)
                                                        <i class="icofont-attachment attachment-icon" title="Has attachments"></i>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="recipient-badge">{{ $campaign->total_recipients }} users</span>
                                            </td>
                                            <td>
                                                <span class="email-status status-{{ $campaign->status }}">
                                                    @switch($campaign->status)
                                                        @case('draft')
                                                            <i class="icofont-edit"></i> Draft
                                                            @break
                                                        @case('scheduled')
                                                            <i class="icofont-clock-time"></i> Scheduled
                                                            @break
                                                        @case('sending')
                                                            <i class="icofont-spinner"></i> Sending
                                                            @break
                                                        @case('sent')
                                                            <i class="icofont-check-circled"></i> Sent
                                                            @break
                                                        @case('failed')
                                                            <i class="icofont-close-circled"></i> Failed
                                                            @break
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td class="created-date">{{ $campaign->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="{{ route('admin.communication-admin.show', $campaign) }}" 
                                                       class="action-btn view-btn" title="View Details">
                                                        <i class="icofont-eye"></i>
                                                    </a>
                                                    
                                                    @if($campaign->status === 'sent')
                                                        <a href="{{ route('admin.communication-admin.replies', $campaign) }}" 
                                                           class="action-btn replies-btn" title="View Replies ({{ $campaign->replies_count }})">
                                                            <i class="icofont-chat"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($campaign->status === 'draft')
                                                        <a href="{{ route('admin.communication-admin.edit', $campaign) }}" 
                                                           class="action-btn edit-btn" title="Edit">
                                                            <i class="icofont-edit"></i>
                                                        </a>
                                                    @endif

                                                    @if(in_array($campaign->status, ['draft', 'scheduled']))
                                                        <form method="POST" action="{{ route('admin.communication-admin.send', $campaign) }}" 
                                                              style="display: inline-block;" 
                                                              onsubmit="return confirm('Are you sure you want to send this memo?')">
                                                            @csrf
                                                            <button type="submit" class="action-btn send-btn" title="Send Now">
                                                                <i class="icofont-send-mail"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form method="POST" action="{{ route('admin.communication-admin.destroy', $campaign) }}" 
                                                          style="display: inline-block;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this memo?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-btn delete-btn" title="Delete">
                                                            <i class="icofont-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="icofont-inbox"></i>
                                                    <h5>No Memos Found</h5>
                                                    <p>Start by composing your first memo to communicate with users.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($campaigns->hasPages())
                                <div class="pagination-wrapper">
                                    {{ $campaigns->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Statistics Page Styles */
* {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.metric-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  align-items: center;
  gap: 20px;
}

.metric-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.metric-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  flex-shrink: 0;
}

.metric-content {
  flex: 1;
}

.metric-number {
  font-size: 36px;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 8px 0;
  line-height: 1;
  letter-spacing: -0.025em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.metric-label {
  font-size: 15px;
  color: #64748b;
  margin: 0;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  opacity: 0.9;
}

/* Filter Section */
.filter-section {
  background: #ffffff;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
}

.filter-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
}

.filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  color: #64748b;
  text-decoration: none;
  font-weight: 500;
  font-size: 14px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.filter-btn:hover {
  background: #e2e8f0;
  border-color: #cbd5e1;
  color: #475569;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  text-decoration: none;
}

.filter-btn.active {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  border-color: #3b82f6;
  color: white;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.filter-btn i {
  font-size: 16px;
}

.filter-indicator {
  color: #64748b;
  font-weight: 400;
  font-size: 14px;
}

.stats-panel {
  background: #ffffff;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f1f5f9;
  height: 100%;
}

.panel-header {
  margin-bottom: 32px;
  text-align: center;
}

.panel-header h5 {
  font-size: 22px;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px 0;
  letter-spacing: -0.02em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.panel-subtitle {
  font-size: 15px;
  color: #64748b;
  margin: 0;
  font-weight: 500;
  letter-spacing: 0.02em;
  opacity: 0.85;
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
  background-color: #f59e0b;
}

.edit-btn:hover {
  background-color: #d97706;
}

/* Table Controls */
.table-controls {
  margin-bottom: 24px;
}

.search-form {
  max-width: 400px;
  margin: 0 auto;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 4px;
  transition: all 0.3s ease;
}

.search-input-wrapper:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
  color: #64748b;
  margin-left: 12px;
  font-size: 16px;
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 12px 16px;
  font-size: 14px;
  color: #1e293b;
  outline: none;
}

.search-input::placeholder {
  color: #94a3b8;
}

.search-button {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.search-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Table Styling */
.table-container {
  overflow-x: auto;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.email-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.email-table th {
  background: #f8fafc;
  padding: 16px 20px;
  text-align: left;
  font-weight: 700;
  color: #1e293b;
  font-size: 14px;
  letter-spacing: 0.02em;
  border-bottom: 2px solid #e2e8f0;
  white-space: nowrap;
}

.email-table td {
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

.email-table tbody tr {
  transition: all 0.3s ease;
}

.email-table tbody tr:hover {
  background: #f8fafc;
  transform: translateX(4px);
}

/* Email Title */
.email-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
}

.attachment-icon {
  color: #64748b;
  font-size: 16px;
  opacity: 0.7;
}

/* Recipient Badge */
.recipient-badge {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: white;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.02em;
  white-space: nowrap;
}

/* Email Status */
.email-status {
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: capitalize;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.status-draft { 
  background: linear-gradient(135deg, #64748b 0%, #475569 100%); 
  color: white; 
}

.status-scheduled { 
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); 
  color: white; 
}

.status-sending { 
  background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); 
  color: white; 
}

.status-sent { 
  background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
  color: white; 
}

.status-failed { 
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); 
  color: white; 
}

/* Progress Bar */
.progress-wrapper {
  min-width: 140px;
}

.progress-bar {
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 6px;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.8s ease;
}

.progress-fill.sent-fill { 
  background: linear-gradient(90deg, #10b981 0%, #059669 100%); 
}

.progress-fill.sending-fill { 
  background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 100%); 
}

.progress-text {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
}

.no-progress {
  color: #94a3b8;
  font-style: italic;
}

/* Created Date */
.created-date {
  color: #64748b;
  font-weight: 500;
  font-size: 14px;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.view-btn { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
.edit-btn { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.send-btn { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.delete-btn { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.replies-btn { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

/* Memo Success Popup Styles */
.memo-success-popup {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2147483647; /* ensure on top across all screen sizes */
  opacity: 1;
  animation: popupBounceIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

.popup-container {
  position: relative;
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
  border-radius: 24px;
  padding: 40px;
  box-shadow: 
    0 25px 50px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.05),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  max-width: 450px;
  width: 90%;
  text-align: center;
  transform: scale(0.5) translateY(100px);
  animation: popupBounceIn 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
  border: 3px solid #10b981;
  overflow: hidden;
}

.popup-content {
  position: relative;
  z-index: 2;
}

.popup-icon {
  position: relative;
  display: inline-block;
  margin-bottom: 24px;
  animation: popupIconBlink 2s infinite;
}

.success-icon {
  font-size: 64px;
  color: #10b981;
  position: relative;
  z-index: 3;
  display: block;
  filter: drop-shadow(0 0 20px rgba(16, 185, 129, 0.6));
}

.pulse-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  border: 3px solid #10b981;
  border-radius: 50%;
  width: 80px;
  height: 80px;
  opacity: 0;
  animation: pulseRing 2s infinite ease-out;
}

.pulse-ring-delay-1 {
  animation-delay: 0.33s;
}

.pulse-ring-delay-2 {
  animation-delay: 0.66s;
}

.popup-title {
  font-size: 28px;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 16px 0;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  animation: titleBlink 3s infinite;
}

.popup-message {
  font-size: 16px;
  color: #64748b;
  margin: 0 0 24px 0;
  font-weight: 500;
  line-height: 1.5;
}

.popup-details {
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 24px;
}

.security-details {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 32px;
}

.security-details .detail-item {
  color: #dc2626;
}

.security-details .detail-item i {
  color: #ef4444;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  color: #059669;
  font-weight: 600;
}

.detail-item i {
  font-size: 16px;
  color: #10b981;
}

.popup-close-btn {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.popup-close-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}

.popup-confetti {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  overflow: hidden;
  border-radius: 24px;
}

.popup-confetti::before,
.popup-confetti::after {
  content: '';
  position: absolute;
  width: 10px;
  height: 10px;
  background: #10b981;
  animation: confettiFall 3s infinite ease-in;
}

.popup-confetti::before {
  left: 20%;
  animation-delay: 0s;
  background: #3b82f6;
}

.popup-confetti::after {
  left: 80%;
  animation-delay: 1s;
  background: #f59e0b;
}

/* Keyframe Animations */
@keyframes popupFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes popupBounceIn {
  0% {
    transform: scale(0.5) translateY(100px);
    opacity: 0;
  }
  50% {
    transform: scale(1.1) translateY(-20px);
  }
  100% {
    transform: scale(1) translateY(0);
    opacity: 1;
  }
}

@keyframes popupIconBlink {
  0%, 100% {
    transform: scale(1);
    filter: drop-shadow(0 0 20px rgba(16, 185, 129, 0.6));
  }
  25% {
    transform: scale(1.1);
    filter: drop-shadow(0 0 30px rgba(16, 185, 129, 0.8));
  }
  50% {
    transform: scale(1.2);
    filter: drop-shadow(0 0 40px rgba(16, 185, 129, 1));
  }
  75% {
    transform: scale(1.1);
    filter: drop-shadow(0 0 30px rgba(16, 185, 129, 0.8));
  }
}

@keyframes titleBlink {
  0%, 100% {
    color: #1e293b;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }
  50% {
    color: #10b981;
    text-shadow: 0 0 20px rgba(16, 185, 129, 0.6);
  }
}

@keyframes pulseRing {
  0% {
    transform: translate(-50%, -50%) scale(0.8);
    opacity: 1;
  }
  100% {
    transform: translate(-50%, -50%) scale(2);
    opacity: 0;
  }
}

@keyframes confettiFall {
  0% {
    transform: translateY(-100px) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: translateY(400px) rotate(720deg);
    opacity: 0;
  }
}

/* Popup responsive design */
@media (max-width: 768px) {
  .popup-container {
    padding: 30px 20px;
    max-width: 95%;
  }
  
  .popup-title {
    font-size: 24px;
  }
  
  .success-icon {
    font-size: 48px;
  }
  
  .pulse-ring {
    width: 60px;
    height: 60px;
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #94a3b8;
}

.empty-state i {
  font-size: 56px;
  margin-bottom: 20px;
  opacity: 0.4;
  color: #94a3b8;
}

.empty-state h5 {
  font-size: 18px;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 12px;
}

.empty-state p {
  font-size: 16px;
  margin-bottom: 20px;
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.01em;
}

.empty-state .btn {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
  font-weight: 600;
  letter-spacing: 0.02em;
  border-radius: 12px;
  padding: 12px 28px;
  font-size: 15px;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.empty-state .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

/* Pagination */
.pagination-wrapper {
  margin-top: 32px;
  display: flex;
  justify-content: center;
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
  
  .metric-card {
    padding: 20px;
    gap: 16px;
  }
  
  .metric-icon {
    width: 50px;
    height: 50px;
    font-size: 20px;
  }
  
  .metric-number {
    font-size: 28px;
  }
  
  .stats-panel {
    padding: 24px;
  }
  
  .email-table th,
  .email-table td {
    padding: 12px 16px;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 4px;
  }
  
  .action-btn {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
  
  .search-form {
    max-width: 100%;
  }
  
  .filter-buttons {
    flex-direction: column;
    gap: 8px;
  }
  
  .filter-btn {
    padding: 10px 16px;
    font-size: 13px;
    justify-content: center;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Memo Success Popup with Sound
    const memoPopup = document.getElementById('memoSuccessPopup');
    if (memoPopup) {
        // Create and play success sound
        playMemoSuccessSound();
        
        // Auto hide popup after 10 seconds unless user interaction
        let autoHideTimer = setTimeout(() => {
            closeMemoPopup();
        }, 10000);
        
        // Clear auto-hide timer if user hovers over popup
        memoPopup.addEventListener('mouseenter', () => {
            clearTimeout(autoHideTimer);
        });
        
        // Restart auto-hide timer when user stops hovering
        memoPopup.addEventListener('mouseleave', () => {
            autoHideTimer = setTimeout(() => {
                closeMemoPopup();
            }, 5000);
        });
        
        // Close popup when clicking outside
        memoPopup.addEventListener('click', (e) => {
            if (e.target === memoPopup) {
                closeMemoPopup();
            }
        });
        
        // Close popup with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && memoPopup) {
                closeMemoPopup();
            }
        });
    }

    // Animate metric cards
    const metricCards = document.querySelectorAll('.metric-card');
    metricCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });

    // Animate table rows
    const tableRows = document.querySelectorAll('.email-row');
    tableRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            row.style.transition = 'all 0.4s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 200 + (index * 50));
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

            // Refresh page every 30 seconds for sending memos
    const hasSendingEmails = document.querySelector('.status-sending');
    if (hasSendingEmails) {
        setTimeout(function() {
            location.reload();
        }, 30000);
    }
});

// Memo Success Popup Functions
function closeMemoPopup() {
    const popup = document.getElementById('memoSuccessPopup');
    if (popup) {
        popup.style.animation = 'popupFadeOut 0.5s ease-in forwards';
        setTimeout(() => {
            popup.remove();
        }, 500);
    }
}

function playMemoSuccessSound() {
    // Create success sound using Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        
        // Success notification sound sequence
        const successTune = [
            { freq: 523.25, duration: 0.2 }, // C5
            { freq: 659.25, duration: 0.2 }, // E5
            { freq: 783.99, duration: 0.2 }, // G5
            { freq: 1046.50, duration: 0.4 } // C6
        ];
        
        let startTime = audioContext.currentTime;
        
        successTune.forEach((note, index) => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = note.freq;
            oscillator.type = 'sine';
            
            // Envelope for smooth sound
            gainNode.gain.setValueAtTime(0, startTime);
            gainNode.gain.linearRampToValueAtTime(0.3, startTime + 0.05);
            gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + note.duration);
            
            oscillator.start(startTime);
            oscillator.stop(startTime + note.duration);
            
            startTime += note.duration;
        });
        
        // Add celebratory bell sound
        setTimeout(() => {
            const bellOscillator = audioContext.createOscillator();
            const bellGain = audioContext.createGain();
            
            bellOscillator.connect(bellGain);
            bellGain.connect(audioContext.destination);
            
            bellOscillator.frequency.value = 2093; // C7
            bellOscillator.type = 'triangle';
            
            bellGain.gain.setValueAtTime(0, audioContext.currentTime);
            bellGain.gain.linearRampToValueAtTime(0.2, audioContext.currentTime + 0.02);
            bellGain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.8);
            
            bellOscillator.start();
            bellOscillator.stop(audioContext.currentTime + 0.8);
        }, 800);
        
    } catch (error) {
        console.log('Audio not supported or blocked by browser');
        // Fallback: use notification sound if available
        if ('speechSynthesis' in window) {
            // Create a subtle beep using speech synthesis as fallback
            const utterance = new SpeechSynthesisUtterance('');
            utterance.volume = 0.1;
            utterance.rate = 10;
            utterance.pitch = 2;
            speechSynthesis.speak(utterance);
        }
    }
}

// Additional CSS keyframe for popup fade out
const style = document.createElement('style');
style.textContent = `
@keyframes popupFadeOut {
  from {
    opacity: 1;
    transform: scale(1);
  }
  to {
    opacity: 0;
    transform: scale(0.8);
  }
}
`;
document.head.appendChild(style);
</script>
@endsection
