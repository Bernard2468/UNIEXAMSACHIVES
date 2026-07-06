@extends('layout.app')

@section('content')
@include('frontend.header')

@php
    // Read-only listing: every folder here is shared IN by an institutional
    // office account, so there are no "owned" folders and no New Folder action.
    $items = collect();
@endphp

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

@endsection
