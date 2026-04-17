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
                        
                        <div class="dashboard__section__title">
                            <h4>Memo</h4>
                            <div style="margin-top:8px; display: flex; gap: 8px; align-items: center;">
                                <a href="{{ route('dashboard.message') }}" class="btn btn-sm btn-primary">
                                    <i class="icofont-arrow-left"></i> Back to Memos
                                </a>
                                <form method="POST" action="{{ route('dashboard.memo.markAsRead', $message->id) }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="icofont-check-circled"></i> Mark as Read
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12">
                                <div class="dashboard__form"><h3>{{$message->title}}</h3></div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <div class="dashboard__form">{!!$message->body!!}</div>
                                @if(isset($message->attachments) && is_array($message->attachments) && count($message->attachments) > 0)
                                <div class="dashboard__form" style="margin-top: 16px;">
                                    <h5><i class="icofont-attachment"></i> Attachments</h5>
                                    <div class="attachments-list">
                                        @foreach($message->attachments as $index => $file)
                                            <div class="attachment-item" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; margin: 8px 0; border: 1px solid #e0e0e0; border-radius: 8px; background: #f9f9f9; transition: all 0.2s ease;">
                                                <div class="attachment-info" style="display: flex; align-items: center; flex: 1;">
                                                    <i class="icofont-file-alt" style="margin-right: 12px; color: #666; font-size: 1.2em;"></i>
                                                    <div>
                                                        <span class="attachment-name" style="font-weight: 500; color: #333; display: block;">{{ $file['name'] }}</span>
                                                        <span class="attachment-size" style="color: #666; font-size: 0.85em;">
                                                            {{ number_format($file['size'] / 1024, 1) }} KB
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="attachment-actions">
                                                    <a href="{{ route('dashboard.memo.download-attachment', ['recipient' => $message->id, 'index' => $index]) }}" 
                                                       class="btn btn-sm btn-primary" style="padding: 6px 12px; font-size: 0.85em; border-radius: 4px;">
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
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
