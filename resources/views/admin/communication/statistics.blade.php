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
                            <h4>Communication Statistics</h4>
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
                                <a href="{{route('admin.communication.create')}}" class="responsive-btn compose-btn">
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

                        <!-- Key Metrics Cards -->
                        <div class="row mb-5">
                            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="icofont-email"></i>
                                    </div>
                                    <div class="metric-content">
                                        <h3 class="metric-number">{{ $stats['total_campaigns'] }}</h3>
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
                                        <h3 class="metric-number">{{ $stats['sent_campaigns'] }}</h3>
                                        <p class="metric-label">Sent Memos</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="icofont-send-mail"></i>
                                    </div>
                                    <div class="metric-content">
                                        <h3 class="metric-number">{{ $stats['total_emails_sent'] }}</h3>
                                        <p class="metric-label">Memos Delivered</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
                                <div class="metric-card">
                                    <div class="metric-icon">
                                        <i class="icofont-users-alt-3"></i>
                                    </div>
                                    <div class="metric-content">
                                        <h3 class="metric-number">{{ $stats['total_users'] }}</h3>
                                        <p class="metric-label">Active Users</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Memo Status Overview -->
                            <div class="col-xl-8 col-lg-12 mb-4">
                                <div class="stats-panel">
                                    <div class="panel-header">
                                        <h5>Memo Status Overview</h5>
                                        <p class="panel-subtitle">Current campaign distribution and performance metrics</p>
                                    </div>
                                    
                                    <div class="status-grid">
                                        <div class="status-card">
                                            <div class="status-header">
                                                <div class="status-icon draft">
                                                    <i class="icofont-edit"></i>
                                                </div>
                                                <div class="status-info">
                                                    <span class="status-name">Draft</span>
                                                    <span class="status-count">{{ $stats['draft_campaigns'] }}</span>
                                                </div>
                                            </div>
                                            <div class="status-progress">
                                                <div class="progress-bar">
                                                    <div class="progress-fill draft-fill" 
                                                         style="width: {{ $stats['total_campaigns'] ? ($stats['draft_campaigns'] / $stats['total_campaigns']) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="status-card">
                                            <div class="status-header">
                                                <div class="status-icon scheduled">
                                                    <i class="icofont-clock-time"></i>
                                                </div>
                                                <div class="status-info">
                                                    <span class="status-name">Scheduled</span>
                                                    <span class="status-count">{{ $stats['scheduled_campaigns'] }}</span>
                                                </div>
                                            </div>
                                            <div class="status-progress">
                                                <div class="progress-bar">
                                                    <div class="progress-fill scheduled-fill" 
                                                         style="width: {{ $stats['total_campaigns'] ? ($stats['scheduled_campaigns'] / $stats['total_campaigns']) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="status-card">
                                            <div class="status-header">
                                                <div class="status-icon sent">
                                                    <i class="icofont-check-circled"></i>
                                                </div>
                                                <div class="status-info">
                                                    <span class="status-name">Sent</span>
                                                    <span class="status-count">{{ $stats['sent_campaigns'] }}</span>
                                                </div>
                                            </div>
                                            <div class="status-progress">
                                                <div class="progress-bar">
                                                    <div class="progress-fill sent-fill" 
                                                         style="width: {{ $stats['total_campaigns'] ? ($stats['sent_campaigns'] / $stats['total_campaigns']) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($stats['total_campaigns'] > 0)
                                        <div class="performance-metrics">
                                            <div class="metric-row">
                                                <div class="metric-item">
                                                    <span class="metric-label">Success Rate</span>
                                                    <span class="metric-value success">{{ round(($stats['sent_campaigns'] / $stats['total_campaigns']) * 100, 1) }}%</span>
                                                </div>
                                                <div class="metric-item">
                                                    <span class="metric-label">Avg Recipients</span>
                                                    <span class="metric-value">{{ $stats['total_campaigns'] ? round($stats['total_emails_sent'] / $stats['total_campaigns']) : 0 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Monthly Activity -->
                            <div class="col-xl-4 col-lg-12 mb-4">
                                <div class="stats-panel">
                                    <div class="panel-header">
                                        <h5>Monthly Activity</h5>
                                        <p class="panel-subtitle">Memo activity trends over time</p>
                                    </div>
                                    
                                    <div class="activity-chart">
                                        @if($monthlyStats->isEmpty())
                                            <div class="empty-state">
                                                <i class="icofont-chart-line"></i>
                                                <p>No data available</p>
                                            </div>
                                        @else
                                            <div class="chart-container">
                                                @foreach($monthlyStats as $month)
                                                    @php
                                                        $maxCount = $monthlyStats->max('count');
                                                        $height = $maxCount > 0 ? ($month->count / $maxCount) * 100 : 0;
                                                    @endphp
                                                    <div class="chart-column">
                                                        <div class="column-bar" style="height: {{ $height }}%">
                                                            <span class="bar-tooltip">{{ $month->count }}</span>
                                                        </div>
                                                        <div class="column-label">{{ \Carbon\Carbon::parse($month->month . '-01')->format('M Y') }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="stats-panel">
                                    <div class="panel-header">
                                        <h5>Recent Memo Activity</h5>
                                        <p class="panel-subtitle">Latest campaign activities and updates</p>
                                    </div>
                                    
                                    @if($recentActivity->isEmpty())
                                        <div class="empty-state">
                                            <i class="icofont-history"></i>
                                            <p>No recent activity</p>
                                            <a href="{{ route('admin.communication.create') }}" class="btn btn-primary">
                                                Create Your First Memo
                                            </a>
                                        </div>
                                    @else
                                        <div class="activity-list">
                                            @foreach($recentActivity as $campaign)
                                                <div class="activity-item">
                                                    <div class="activity-status status-{{ $campaign->status }}">
                                                        @switch($campaign->status)
                                                            @case('draft')
                                                                <i class="icofont-edit"></i>
                                                                @break
                                                            @case('scheduled')
                                                                <i class="icofont-clock-time"></i>
                                                                @break
                                                            @case('sending')
                                                                <i class="icofont-spinner"></i>
                                                                @break
                                                            @case('sent')
                                                                <i class="icofont-check-circled"></i>
                                                                @break
                                                            @case('failed')
                                                                <i class="icofont-close-circled"></i>
                                                                @break
                                                        @endswitch
                                                    </div>
                                                    <div class="activity-content">
                                                        <div class="activity-main">
                                                            <h6><a href="{{ route('admin.communication.show', $campaign) }}">{{ Str::limit($campaign->subject, 40) }}</a></h6>
                                                            <p>
                                                                <strong>Ref:</strong> {{ $campaign->reference ?? '-' }}
                                                                &nbsp;—&nbsp;{{ Str::limit($campaign->subject, 80) }}
                                                            </p>
                                                        </div>
                                                        <div class="activity-meta">
                                                            <div class="meta-group">
                                                                <span class="meta-item">
                                                                    <i class="icofont-users"></i> {{ $campaign->total_recipients }}
                                                                </span>
                                                                <span class="meta-item">
                                                                    <i class="icofont-user"></i> {{ $campaign->creator->first_name }} {{ $campaign->creator->last_name }}
                                                                </span>
                                                                @if($campaign->attachments && count($campaign->attachments) > 0)
                                                                    <span class="meta-item">
                                                                        <i class="icofont-attachment"></i> {{ count($campaign->attachments) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <span class="activity-time">{{ $campaign->created_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
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

.status-grid {
    display: grid;
    gap: 24px;
    margin-bottom: 32px;
}

.status-card {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
}

.status-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.status-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.status-icon.draft {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.status-icon.scheduled {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.status-icon.sent {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.status-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-name {
  font-weight: 700;
  color: #334155;
  font-size: 16px;
  letter-spacing: 0.01em;
  text-transform: capitalize;
}

.status-count {
  font-weight: 800;
  font-size: 26px;
  color: #1e293b;
  letter-spacing: -0.02em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.status-progress {
    margin-top: 16px;
}

.progress-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.8s ease;
}

.progress-fill.draft-fill { background: linear-gradient(90deg, #64748b 0%, #475569 100%); }
.progress-fill.scheduled-fill { background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); }
.progress-fill.sent-fill { background: linear-gradient(90deg, #10b981 0%, #059669 100%); }

.performance-metrics {
    background: #f8fafc;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #e2e8f0;
}

.metric-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.metric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.metric-label {
  font-weight: 600;
  color: #475569;
  font-size: 14px;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  opacity: 0.9;
}

.metric-value {
  font-weight: 800;
  font-size: 22px;
  color: #1e293b;
  letter-spacing: -0.015em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.metric-value.success {
    color: #059669;
}

.activity-chart {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-container {
    display: flex;
    align-items: end;
    justify-content: space-around;
    gap: 16px;
    height: 160px;
    width: 100%;
}

.chart-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    max-width: 50px;
}

.column-bar {
    width: 100%;
    background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 6px 6px 0 0;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 8px;
    position: relative;
}

.column-bar:hover {
    opacity: 0.8;
    transform: scale(1.05);
}

.bar-tooltip {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  background: #1e293b;
  color: white;
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  opacity: 0;
  transition: opacity 0.3s ease;
  white-space: nowrap;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
  letter-spacing: 0.02em;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.column-bar:hover .bar-tooltip {
    opacity: 1;
}

.column-label {
  font-size: 13px;
  color: #64748b;
  margin-top: 12px;
  font-weight: 600;
  text-align: center;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

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

.empty-state p {
  font-size: 16px;
  margin-bottom: 20px;
  color: #64748b;
  font-weight: 500;
  letter-spacing: 0.01em;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.activity-status {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
}

.activity-status.status-draft { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
.activity-status.status-scheduled { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.activity-status.status-sending { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
.activity-status.status-sent { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.activity-status.status-failed { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-main h6 {
  margin: 0 0 8px 0;
  font-size: 17px;
  font-weight: 700;
  letter-spacing: -0.01em;
  color: #1e293b;
}

.activity-main h6 a {
    color: #1e293b;
    text-decoration: none;
}

.activity-main h6 a:hover {
    color: #3b82f6;
}

.activity-main p {
  margin: 0 0 16px 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.6;
  font-weight: 500;
  letter-spacing: 0.01em;
}

.activity-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.meta-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.meta-item {
  font-size: 13px;
  color: #64748b;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-transform: capitalize;
}

.activity-time {
  font-size: 12px;
  color: #94a3b8;
  font-weight: 600;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  opacity: 0.8;
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
    
    .metric-row {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .chart-container {
        gap: 12px;
    }
    
    .activity-item {
        flex-direction: column;
        text-align: center;
        gap: 16px;
    }
    
    .activity-status {
        margin: 0 auto;
    }
    
    .activity-meta {
        flex-direction: column;
        align-items: center;
    }
    
    .meta-group {
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 300);
    });

    // Animate chart bars
    const chartBars = document.querySelectorAll('.column-bar');
    chartBars.forEach((bar, index) => {
        const height = bar.style.height;
        bar.style.height = '0%';
        setTimeout(() => {
            bar.style.height = height;
        }, 100 * (index + 1));
    });

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
});
</script>
@endsection