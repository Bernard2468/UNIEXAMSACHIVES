@extends('layout.app')

@section('content')
@include('frontend.header')

@php
    $items = $exams->map(function($exam) {
        $ext = strtolower(pathinfo($exam->exam_document ?? '', PATHINFO_EXTENSION) ?: 'pdf');
        $date = $exam->created_at ? $exam->created_at->format('Y-m-d') : now()->format('Y-m-d');
        return [
            'id' => $exam->id,
            'kind' => 'exam',
            'title' => $exam->course_title . ' (' . $exam->course_code . ')',
            'meta' => $exam->faculty . ' • ' . $exam->semester . ' • ' . $exam->academic_year,
            'date' => $date,
            'extension' => $ext,
            'view_url' => asset($exam->exam_document),
            'download_url' => route('download.exam', $exam->id),
            'edit_url' => route('exams.edit', $exam->id),
            'destroy_url' => route('exams.destroy', $exam->id),
        ];
    });
@endphp

@include('components.explorer', [
    'pageTitle' => 'My Uploaded Exams',
    'pageSubtitle' => 'All exam documents you have deposited. Right-click or use the menu to manage each one. Drag any item onto a folder to organize.',
    'folders' => $folders,
    'sharedFolders' => $sharedFolders ?? collect(),
    'items' => $items,
    'itemKind' => 'exam',
])

@endsection
