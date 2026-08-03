@extends('layout.app')

@section('content')
@include('frontend.header')

@php
    // Read-only listing: every folder here is shared IN by an institutional
    // office account, so there are no "owned" folders and no New Folder action.
    $items = collect();
@endphp

<div class="dashboardarea sp_bottom_100">
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    @include('components.explorer', [
    'pageTitle' => 'Departmental Folders',
    'pageSubtitle' => 'Folders shared with you by institutional offices and departments. Open any folder to view or work with its contents.',
    'folders' => collect(),
    'sharedFolders' => $departmentalFolders ?? collect(),
    'sharedSectionLabel' => 'Departmental Folders',
    'items' => $items,
    'itemKind' => 'file',
    'allowNewFolder' => false,
    'showItemsSection' => false,
    'noFoldersTitle' => 'No departmental folders yet',
    'noFoldersText' => 'Folders shared with you by institutional offices will appear here.',
])
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
