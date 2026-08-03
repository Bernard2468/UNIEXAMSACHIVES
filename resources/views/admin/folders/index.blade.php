@extends('layout.app')

@section('content')
@include('frontend.header')

@php
    // Render via shared explorer with no items list — just folders.
    $items = collect();
@endphp

<div class="dashboardarea sp_bottom_100">
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    @include('components.explorer', [
    'pageTitle' => 'My Folders',
    'pageSubtitle' => 'Organize your files and exam documents into folders. Open any folder to manage its contents, or drag-drop items into folders from the archive pages.',
    'folders' => $folders,
    'sharedFolders' => $sharedFolders ?? collect(),
    'items' => $items,
    'itemKind' => 'file',
    'allowNewFolder' => true,
    'showItemsSection' => false,
])
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
