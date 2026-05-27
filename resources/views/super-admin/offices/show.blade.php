@extends('super-admin.layout')

@section('title', $office->name)

@section('content')
<div class="page-container" style="max-width: 1100px; margin: 0 auto; padding: 1.5rem;">
    <a href="{{ route('super-admin.offices.index') }}" style="color:#3b82f6; text-decoration:none; font-size:14px;">&larr; Back to offices</a>

    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin: 14px 0 26px;">
        <div>
            <h1 style="font-size: 1.625rem; font-weight: 700; margin: 0;">{{ $office->name }}</h1>
            <code style="font-size:12px; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:4px; display:inline-block; margin-top: 6px;">{{ $office->slug }}</code>
            @if($office->description)<p style="color:#6b7280; margin: 10px 0 0;">{{ $office->description }}</p>@endif
        </div>
    </div>

    @if(session('success'))
        <div style="padding:12px 16px; background:#ecfdf5; color:#065f46; border-radius:8px; margin-bottom:16px;">{{ session('success') }}</div>
    @endif

    <div style="background:#fff; padding:20px; border:1px solid #e5e7eb; border-radius:10px; margin-bottom: 24px;">
        <h3 style="margin-top:0;">Office Settings</h3>
        <form method="POST" action="{{ route('super-admin.offices.update', $office->id) }}">
            @csrf @method('PUT')
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                <label>Name <input type="text" name="name" class="form-control" value="{{ $office->name }}" required style="width:100%;"></label>
                <label>Email <input type="email" name="email" class="form-control" value="{{ $office->email }}" style="width:100%;"></label>
            </div>
            <label style="display:block; margin-top:14px;">
                Description
                <textarea name="description" class="form-control" rows="2" style="width:100%;">{{ $office->description }}</textarea>
            </label>
            <label style="margin-top:14px; display:inline-flex; gap: 8px; align-items: center;">
                <input type="checkbox" name="is_active" value="1" @checked($office->is_active)>
                Active
            </label>
            <div style="margin-top:16px;">
                <button type="submit" style="background:#3b82f6;color:#fff;padding:8px 18px;border:none;border-radius:6px;">Save changes</button>
            </div>
        </form>
    </div>

    <div style="background:#fff; padding:20px; border:1px solid #e5e7eb; border-radius:10px; margin-bottom: 24px;">
        <h3 style="margin-top:0;">Members</h3>

        @php $members = $office->users; @endphp

        @if($members->isEmpty())
            <p style="color:#6b7280;">No members yet. Forms cannot be routed here until at least one active member is added.</p>
        @else
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align:left;">
                        <th style="padding:10px 6px; border-bottom:2px solid #e5e7eb;">Name</th>
                        <th style="padding:10px 6px; border-bottom:2px solid #e5e7eb;">Email</th>
                        <th style="padding:10px 6px; border-bottom:2px solid #e5e7eb;">Head</th>
                        <th style="padding:10px 6px; border-bottom:2px solid #e5e7eb;">Active</th>
                        <th style="padding:10px 6px; border-bottom:2px solid #e5e7eb;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($members as $member)
                    <tr>
                        <td style="padding:10px 6px; border-bottom:1px solid #f3f4f6;">
                            {{ trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) }}
                        </td>
                        <td style="padding:10px 6px; border-bottom:1px solid #f3f4f6; color:#6b7280; font-size:13px;">{{ $member->email }}</td>
                        <td style="padding:10px 6px; border-bottom:1px solid #f3f4f6;">
                            <form method="POST" action="{{ route('super-admin.offices.members.update', [$office->id, $member->id]) }}" style="display:inline;">
                                @csrf @method('PUT')
                                <input type="hidden" name="is_active" value="{{ $member->pivot->is_active ? '1' : '0' }}">
                                <input type="hidden" name="is_head" value="{{ $member->pivot->is_head ? '0' : '1' }}">
                                <button type="submit" style="background:none;border:none;cursor:pointer;color:{{ $member->pivot->is_head ? '#1d4ed8' : '#9ca3af' }};font-weight:600;">
                                    {{ $member->pivot->is_head ? '★ Head' : 'Make head' }}
                                </button>
                            </form>
                        </td>
                        <td style="padding:10px 6px; border-bottom:1px solid #f3f4f6;">
                            <form method="POST" action="{{ route('super-admin.offices.members.update', [$office->id, $member->id]) }}" style="display:inline;">
                                @csrf @method('PUT')
                                <input type="hidden" name="is_head" value="{{ $member->pivot->is_head ? '1' : '0' }}">
                                <input type="hidden" name="is_active" value="{{ $member->pivot->is_active ? '0' : '1' }}">
                                <button type="submit" style="background:none;border:none;cursor:pointer;color:{{ $member->pivot->is_active ? '#10b981' : '#9ca3af' }};font-weight:600;">
                                    {{ $member->pivot->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td style="padding:10px 6px; border-bottom:1px solid #f3f4f6; text-align:right;">
                            <form method="POST" action="{{ route('super-admin.offices.members.remove', [$office->id, $member->id]) }}"
                                  onsubmit="return confirm('Remove this member from the office?');" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#dc2626;cursor:pointer;">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <hr style="border:none;border-top:1px solid #f3f4f6;margin:18px 0;">

        <h4>Add a member</h4>
        <form method="POST" action="{{ route('super-admin.offices.members.add', $office->id) }}"
              style="display:flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            @csrf
            <label style="flex: 2 1 280px;">
                User
                <select name="user_id" class="form-control" required style="width:100%;">
                    <option value="">— Pick a user —</option>
                    @foreach($candidates as $u)
                        <option value="{{ $u->id }}">
                            {{ trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) }} — {{ $u->email }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label style="display:inline-flex; align-items:center; gap:6px; padding-bottom:8px;">
                <input type="checkbox" name="is_head" value="1"> Make head
            </label>
            <button type="submit" style="background:#3b82f6;color:#fff;padding:9px 18px;border:none;border-radius:6px;">Add</button>
        </form>
    </div>
</div>
@endsection
