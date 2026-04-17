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
                            <h4>Memo Details</h4>
                            <div class="dashboard__section__actions">
                                <a href="{{route('admin.communication-admin.index')}}" class="responsive-btn back-btn">
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
                                @if($campaign->status === 'draft')
                                    <a href="{{ route('admin.communication-admin.edit', $campaign) }}" class="responsive-btn edit-btn">
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
                                                    d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"
                                                ></path>
                                                <path
                                                    stroke="#fff"
                                                    stroke-width="2"
                                                    d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z"
                                                ></path>
                                            </svg>
                                            <div class="text">Edit Memo</div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>



                        <div class="row">
                            <!-- Memo Information -->
                            <div class="col-xl-8">
                                <div class="dashboard__form__wraper">
                                    <div class="email-header">
                                        <div class="email-status-badge">
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
                                        </div>
                                        
                                        <div class="email-meta">
                                            <div class="meta-item">
                                                <i class="icofont-calendar"></i>
                                                <strong>Created:</strong> {{ $campaign->created_at->format('F j, Y \a\t g:i A') }}
                                            </div>
                                            <div class="meta-item">
                                                <i class="icofont-user"></i>
                                                <strong>Created by:</strong> {{ $campaign->creator->first_name }} {{ $campaign->creator->last_name }}
                                            </div>
                                            @if($campaign->scheduled_at)
                                                <div class="meta-item">
                                                    <i class="icofont-clock-time"></i>
                                                    <strong>Scheduled:</strong> {{ $campaign->scheduled_at->format('F j, Y \a\t g:i A') }}
                                                </div>
                                            @endif
                                            @if($campaign->sent_at)
                                                <div class="meta-item">
                                                    <i class="icofont-send-mail"></i>
                                                    <strong>Sent:</strong> {{ $campaign->sent_at->format('F j, Y \a\t g:i A') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="email-details">
                                        <div class="detail-section">
                                            <h5><i class="icofont-info-circle"></i> Memo Information</h5>
                                            <div class="detail-item">
                                                <strong>Subject:</strong>
                                                <p>{{ $campaign->subject }}</p>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Message:</strong>
                                                <div class="message-content">
                                                    {!! $campaign->message !!}
                                                </div>
                                            </div>
                                        </div>

                                        @if($campaign->attachments && count($campaign->attachments) > 0)
                                            <div class="detail-section">
                                                <h5><i class="icofont-attachment"></i> Attachments</h5>
                                                <div class="attachments-list">
                                                    @foreach($campaign->attachments as $index => $attachment)
                                                        <div class="attachment-item">
                                                            <div class="attachment-info">
                                                                <i class="icofont-file-alt"></i>
                                                                <span class="attachment-name">{{ $attachment['name'] }}</span>
                                                                <span class="attachment-size">({{ number_format($attachment['size'] / 1024, 1) }} KB)</span>
                                                            </div>
                                                            <div class="attachment-actions">
                                                                <a href="{{ route('admin.communication-admin.download-attachment', [$campaign, $index]) }}" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="icofont-download"></i> Download
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Recipients Details -->
                                @if($campaign->recipients->isNotEmpty())
                                    <div class="dashboard__form__wraper">
                                        <div class="detail-section">
                                            <h5><i class="icofont-users"></i> Recipients Details</h5>
                                            
                                            <div class="recipients-filters mb-3">
                                                <button class="btn btn-sm btn-outline-primary active" onclick="filterRecipients('all')">
                                                    All ({{ $campaign->recipients->count() }})
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="filterRecipients('sent')">
                                                    Sent ({{ $campaign->recipients->where('status', 'sent')->count() }})
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning" onclick="filterRecipients('pending')">
                                                    Pending ({{ $campaign->recipients->where('status', 'pending')->count() }})
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="filterRecipients('failed')">
                                                    Failed ({{ $campaign->recipients->where('status', 'failed')->count() }})
                                                </button>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-striped recipients-table">
                                                    <thead>
                                                        <tr>
                                                            <th>User</th>
                                                            <th>Email</th>
                                                            <th>Status</th>
                                                            <th>Sent At</th>
                                                            <th>Error Message</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($campaign->recipients as $recipient)
                                                            <tr class="recipient-row" data-status="{{ $recipient->status }}">
                                                                <td>
                                                                    <strong>{{ $recipient->user->first_name }} {{ $recipient->user->last_name }}</strong>
                                                                </td>
                                                                <td>{{ $recipient->user->email }}</td>
                                                                <td>
                                                                    <span class="badge badge-{{ $recipient->status === 'sent' ? 'success' : ($recipient->status === 'failed' ? 'danger' : 'warning') }}">
                                                                        {{ ucfirst($recipient->status) }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    {{ $recipient->sent_at ? $recipient->sent_at->format('M j, Y g:i A') : '-' }}
                                                                </td>
                                                                <td>
                                                                    @if($recipient->error_message)
                                                                        <span class="text-danger" title="{{ $recipient->error_message }}">
                                                                            {{ Str::limit($recipient->error_message, 50) }}
                                                                        </span>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Email Statistics -->
                            <div class="col-xl-4">
                                <div class="dashboard__form__wraper">
                                                                                <h5><i class="icofont-chart-bar-graph"></i> Memo Statistics</h5>
                                    
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <div class="stat-icon">
                                                <i class="icofont-users-alt-3"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-number">{{ $campaign->total_recipients }}</div>
                                                <div class="stat-label">Total Recipients</div>
                                            </div>
                                        </div>

                                        <div class="stat-item">
                                            <div class="stat-icon text-success">
                                                <i class="icofont-check-circled"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-number">{{ $campaign->sent_count }}</div>
                                                <div class="stat-label">Successfully Sent</div>
                                            </div>
                                        </div>

                                        <div class="stat-item">
                                            <div class="stat-icon text-danger">
                                                <i class="icofont-close-circled"></i>
                                            </div>
                                            <div class="stat-content">
                                                <div class="stat-number">{{ $campaign->failed_count }}</div>
                                                <div class="stat-label">Failed</div>
                                            </div>
                                        </div>

                                        @if($campaign->status === 'sent')
                                            <div class="stat-item">
                                                <div class="stat-icon text-info">
                                                    <i class="icofont-chart-line"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <div class="stat-number">{{ $campaign->success_rate }}%</div>
                                                    <div class="stat-label">Success Rate</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($campaign->status === 'sending' || $campaign->status === 'sent')
                                        <div class="progress-section">
                                            <h6>Delivery Progress</h6>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $campaign->progress_percentage }}%"
                                                     aria-valuenow="{{ $campaign->progress_percentage }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ $campaign->sent_count }} of {{ $campaign->total_recipients }} sent 
                                                ({{ $campaign->progress_percentage }}%)
                                            </small>
                                        </div>
                                    @endif

                                    @if(in_array($campaign->status, ['draft', 'scheduled']))
                                        <div class="email-actions">
                                            <form method="POST" action="{{ route('admin.communication-admin.send', $campaign) }}" 
                                                  onsubmit="return confirm('Are you sure you want to send this memo now?')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-block">
                                                    <i class="icofont-send-mail"></i> Send Memo Now
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                    @if($campaign->status !== 'sending')
                                        <div class="danger-zone mt-4">
                                            <h6 class="text-danger">Danger Zone</h6>
                                            <form method="POST" action="{{ route('admin.communication-admin.destroy', $campaign) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this memo? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="icofont-trash"></i> Delete Memo
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
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

.email-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    margin-bottom: 30px;
}

.email-status-badge {
    margin-bottom: 15px;
}

.email-status {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.email-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 10px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section h5 {
    color: #495057;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.detail-item {
    margin-bottom: 15px;
}

.detail-item strong {
    display: block;
    margin-bottom: 5px;
    color: #495057;
}

.message-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}
.message-content p { margin: 0 0 12px 0; }
.message-content ul { margin: 12px 0; padding-left: 20px; }
.message-content ol { margin: 12px 0; padding-left: 20px; }
.message-content h1, .message-content h2, .message-content h3 { margin: 16px 0 8px 0; }

.attachments-list {
    display: grid;
    gap: 10px;
}

.attachment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #dee2e6;
}

.stat-icon {
    font-size: 24px;
    margin-bottom: 10px;
    color: #6c757d;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #495057;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.progress-section {
    margin-bottom: 30px;
}

.progress-section h6 {
    margin-bottom: 10px;
    color: #495057;
}

.recipients-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.recipients-filters .btn.active {
    background-color: #007bff;
    color: white;
}

.recipients-table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #495057;
}

.danger-zone {
    padding-top: 20px;
    border-top: 1px solid #dee2e6;
}

.danger-zone h6 {
    margin-bottom: 10px;
}

.email-actions {
    margin-top: 20px;
}

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
    
    .email-meta {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .recipients-filters {
        flex-direction: column;
    }
    
    .recipients-filters .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh for sending memos
    @if($campaign->status === 'sending')
        setInterval(function() {
            location.reload();
        }, 10000); // Refresh every 10 seconds
    @endif
    
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function filterRecipients(status) {
    const rows = document.querySelectorAll('.recipient-row');
    const buttons = document.querySelectorAll('.recipients-filters .btn');
    
    // Update button states
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter rows
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection