@extends('super-admin.layout')

@section('title', 'Offices')

@section('content')
<div class="page-container" style="max-width: 1400px; margin: 0 auto; padding: 1.5rem;">
    <div class="page-header-modern" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.875rem; font-weight: 700; margin: 0;">Offices</h1>
            <p style="color: #6b7280; margin-top: 4px;">Forms route through these offices. Assign at least one active member to each one your institution uses.</p>
        </div>
        <button type="button" onclick="document.getElementById('createOfficeForm').style.display='block';"
                style="background:#3b82f6; color:#fff; padding:10px 18px; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
            + New Office
        </button>
    </div>

    @if(session('success'))
        <div style="padding: 12px 16px; background: #ecfdf5; color: #065f46; border-radius: 8px; margin-bottom: 16px;">{{ session('success') }}</div>
    @endif

    <form id="createOfficeForm" method="POST" action="{{ route('super-admin.offices.store') }}"
          style="display:none; background:#fff; padding:20px; border:1px solid #e5e7eb; border-radius:10px; margin-bottom:20px;">
        @csrf
        <h3 style="margin-top:0;">New Office</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <label>Name <input type="text" name="name" class="form-control" required style="width:100%;"></label>
            <label>Slug (optional) <input type="text" name="slug" class="form-control" placeholder="auto-generated from name" style="width:100%;"></label>
        </div>
        <label style="display:block; margin-top: 14px;">Email (optional) <input type="email" name="email" class="form-control" style="width:100%;"></label>
        <label style="display:block; margin-top: 14px;">Description <textarea name="description" class="form-control" rows="2" style="width:100%;"></textarea></label>
        <div style="margin-top: 16px;">
            <button type="submit" style="background:#3b82f6;color:#fff;padding:8px 18px;border:none;border-radius:6px;">Create</button>
            <button type="button" onclick="document.getElementById('createOfficeForm').style.display='none';"
                    style="background:#fff;color:#6b7280;border:1px solid #d1d5db;padding:8px 18px;border-radius:6px;margin-left:6px;">Cancel</button>
        </div>
    </form>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px;">
        @foreach($offices as $office)
            @php
                $head = $office->users->where('pivot.is_head', true)->where('pivot.is_active', true)->first();
                $activeCount = $office->users->where('pivot.is_active', true)->count();
            @endphp
            <a href="{{ route('super-admin.offices.show', $office->id) }}"
               style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:20px; text-decoration:none; color:inherit; display:block; transition:all .15s;"
               onmouseover="this.style.borderColor='#3b82f6';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 16px rgba(59,130,246,0.1)';"
               onmouseout="this.style.borderColor='#e5e7eb';this.style.transform='';this.style.boxShadow='';">
                <div style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                    <h4 style="margin:0; color:#111827; font-size:16px;">{{ $office->name }}</h4>
                    @if($office->is_active)
                        <span style="background:#ecfdf5; color:#065f46; padding:2px 10px; border-radius:99px; font-size:10px; font-weight:600;">ACTIVE</span>
                    @else
                        <span style="background:#fef3c7; color:#92400e; padding:2px 10px; border-radius:99px; font-size:10px; font-weight:600;">INACTIVE</span>
                    @endif
                </div>
                <code style="font-size:11px; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:4px;">{{ $office->slug }}</code>
                @if($office->description)
                    <p style="color:#6b7280; font-size:13px; margin: 10px 0 0; line-height: 1.5;">{{ Str::limit($office->description, 120) }}</p>
                @endif
                <hr style="border:none; border-top: 1px solid #f3f4f6; margin: 14px 0;">
                <div style="display:flex; justify-content: space-between; font-size: 12.5px;">
                    <span style="color:#6b7280;">Members</span>
                    <strong>{{ $activeCount }} active</strong>
                </div>
                @if($head)
                    <div style="display:flex; justify-content: space-between; font-size: 12.5px; margin-top: 4px;">
                        <span style="color:#6b7280;">Head</span>
                        <strong>{{ trim(($head->first_name ?? '') . ' ' . ($head->last_name ?? '')) }}</strong>
                    </div>
                @else
                    <div style="color:#dc2626; font-size: 12px; margin-top: 6px;">⚠ No head assigned</div>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endsection
