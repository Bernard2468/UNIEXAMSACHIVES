@extends('layout.app')

@section('content')
@include('frontend.header')

@php
    $items = $files->map(function($file) {
        $ext = strtolower(pathinfo($file->document_file ?? '', PATHINFO_EXTENSION) ?: 'pdf');
        $date = $file->year_deposit
            ? \Carbon\Carbon::parse($file->year_deposit)->format('Y-m-d')
            : ($file->created_at ? $file->created_at->format('Y-m-d') : now()->format('Y-m-d'));
        return [
            'id' => $file->id,
            'kind' => 'file',
            'title' => $file->file_title,
            'meta' => $file->unit . ' • ' . $file->file_format,
            'date' => $date,
            'extension' => $ext,
            'view_url' => asset($file->document_file),
            'download_url' => route('download.file', $file->id),
            'edit_url' => route('files.edit', $file->id),
            'destroy_url' => route('file.destroy', $file->id),
        ];
    });
@endphp

@include('components.explorer', [
    'pageTitle' => 'My Uploaded Files',
    'pageSubtitle' => 'Every file document you have deposited. Right-click any item for options, or drag it onto a folder to organize.',
    'folders' => $folders,
    'sharedFolders' => $sharedFolders ?? collect(),
    'items' => $items,
    'itemKind' => 'file',
])

@endsection
