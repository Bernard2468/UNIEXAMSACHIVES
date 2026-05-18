@extends('layout.app')

@section('content')
@include('frontend.header')

@php
    // Render via shared explorer with no items list — just folders.
    $items = collect();
@endphp

@include('components.explorer', [
    'pageTitle' => 'My Folders',
    'pageSubtitle' => 'Organize your files and exam documents into folders. Open any folder to manage its contents, or drag-drop items into folders from the archive pages.',
    'folders' => $folders,
    'items' => $items,
    'itemKind' => 'file',
    'allowNewFolder' => true,
    'showItemsSection' => false,
])

@endsection
