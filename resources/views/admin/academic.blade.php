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
                        <div class="dashboard__section__title">
                            <h4>Manage Users Account</h4>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="dashboard__table table-responsive">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>First Name</th>
                                                <th>Last name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (count($users) > 0)
                                                @foreach ($users as $user )
                                                    <tr>
                                                        <th>{{$user->id}}</th>
                                                        <td>{{$user->first_name}}</td>
                                                        <td>{{$user->last_name}}</td>
                                                        <td>{{$user->email}}</td>
                                                        <td>
                                                            @if ($user->is_approve)
                                                            <span class="dashboard__td">Approved</span>
                                                            @else
                                                                <span class="dashboard__td dashboard__td__2">Pending</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="d-flex">
                                                                @if (!$user->is_approve)
                                                                    <form action="{{ route('users.approve', $user->id) }}" method="post">
                                                                        @csrf
                                                                        <button type="submit" class="btn btn-success btn-sm me-1">Approve</button>
                                                                    </form>
                                                                @else
                                                                <form action="{{ route('users.disapprove', $user->id) }}" method="post">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success btn-sm me-1">Disapprove</button>
                                                                </form>
                                                                @endif

                                                                <form action="{{ route('users.destroy', $user->id) }}" method="post">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                @endforeach
                                            @else
                                            <tr>
                                                <td colspan="6">No User Yet</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
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
