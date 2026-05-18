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
    'pageTitle' => 'All Exams Archive',
    'pageSubtitle' => 'Browse and organize your exam documents. Drag any item onto a folder, or right-click for more options.',
    'folders' => $folders,
    'items' => $items,
    'itemKind' => 'exam',
])

@endsection
