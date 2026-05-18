@extends('layout.app')

@section('content')
@include('frontend.header')

@php
    // Combine files + exams into a single Explorer items list.
    $fileItems = collect($files)->map(function($file) {
        $ext = strtolower(pathinfo($file->document_file ?? '', PATHINFO_EXTENSION) ?: 'pdf');
        $date = $file->year_deposit
            ? \Carbon\Carbon::parse($file->year_deposit)->format('Y-m-d')
            : ($file->created_at ? $file->created_at->format('Y-m-d') : now()->format('Y-m-d'));
        return [
            'id' => $file->id,
            'kind' => 'file',
            'title' => $file->file_title,
            'meta' => ($file->unit ?? '') . ' • ' . ($file->file_format ?? ''),
            'date' => $date,
            'extension' => $ext,
            'view_url' => asset($file->document_file),
            'download_url' => route('download.file', $file->id),
            'edit_url' => route('files.edit', $file->id),
            'destroy_url' => route('file.destroy', $file->id),
        ];
    });

    $examItems = collect($exams)->map(function($exam) {
        $ext = strtolower(pathinfo($exam->exam_document ?? '', PATHINFO_EXTENSION) ?: 'pdf');
        $date = $exam->created_at ? $exam->created_at->format('Y-m-d') : now()->format('Y-m-d');
        return [
            'id' => $exam->id,
            'kind' => 'exam',
            'title' => $exam->course_title . ' (' . $exam->course_code . ')',
            'meta' => ($exam->faculty ?? '') . ' • ' . ($exam->semester ?? '') . ' • ' . ($exam->academic_year ?? ''),
            'date' => $date,
            'extension' => $ext,
            'view_url' => asset($exam->exam_document),
            'download_url' => route('download.exam', $exam->id),
            'edit_url' => route('exams.edit', $exam->id),
            'destroy_url' => route('exams.destroy', $exam->id),
        ];
    });

    $items = $fileItems->merge($examItems)->sortByDesc('date')->values();
@endphp

@include('components.explorer', [
    'pageTitle' => 'All Documents',
    'pageSubtitle' => 'Everything you have uploaded — files and exam documents in one place. Drag any item onto a folder to organize. Right-click for options.',
    'folders' => $folders,
    'sharedFolders' => $sharedFolders ?? collect(),
    'items' => $items,
    'itemKind' => 'file',
    'itemsSectionLabel' => 'Documents',
    'emptyStateText' => 'Upload a file or an exam document to see it here.',
])

@endsection
