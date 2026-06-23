<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Academic;
use App\Models\Department;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class ExamsController extends Controller
{
    public function upload()
    {
        $exams = Exam::all();
        return view('admin.uploaded_documents', compact('exams'));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required',
            'course_code' => 'required|string',
            'course_title' => 'required|string',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'exams_type' => 'required|string',
            'exam_date' => 'required|date|before_or_equal:today',
            'exam_format' => 'required',
            'duration' => 'required|string',
            'exam_document' => 'required|file|mimes:pdf,docx',
            'answer_key' => 'file|mimes:pdf,docx',
            'special_instruction' => 'string|nullable',
        ]);

        $user = Auth::user();
        $validatedData['instructor_name'] = trim($user->first_name . ' ' . $user->last_name);
        $validatedData['email'] = $user->email;
        $validatedData['faculty'] = optional($user->department)->name ?? 'Unassigned';

        // Store exam document in new public storage
        $examFile = $request->file('exam_document');
        $examFileName = $this->buildStoredFilename($examFile);
        $examFile->move(public_path('exams/documents'), $examFileName);
        $validatedData['exam_document'] = 'exams/documents/' . $examFileName;
        $validatedData['document_id'] = random_int(1000000000, 9999999999);

        if ($request->hasFile('answer_key')) {
            $answerFile = $request->file('answer_key');
            $answerFileName = $this->buildStoredFilename($answerFile);
            $answerFile->move(public_path('exams/answer_keys'), $answerFileName);
            $validatedData['answer_key'] = 'exams/answer_keys/' . $answerFileName;
        }

        $validatedData['user_id'] = Auth::user()->id;
        $validatedData['is_approve'] = true; // Auto-approve all uploads

        Exam::create($validatedData);
        return redirect()->route('dashboard.all.exams')->with('success', 'Exam Document deposited successfully.');
    }

    public function edit(Exam $exam)
    {
        $this->authorizeManage($exam);

        return view('admin.deposition_form', [
            'exam' => $exam,
            'departments' => Department::all(),
            'years' => Academic::all(),
        ]);
    }

    public function update(Request $request, Exam $exam)
    {
        $this->authorizeManage($exam);

        $validatedData = $request->validate([
            'student_id' => 'required',
            'course_code' => 'required|string',
            'course_title' => 'required|string',
            'semester' => 'required|string',
            'academic_year' => 'required|string',
            'exams_type' => 'required|string',
            'exam_date' => 'required|date|before_or_equal:today',
            'exam_format' => 'required',
            'duration' => 'required|string',
            'exam_document' => 'nullable|file',
            'answer_key' => 'nullable|file',
        ]);

        $user = Auth::user();
        $validatedData['instructor_name'] = trim($user->first_name . ' ' . $user->last_name);
        $validatedData['email'] = $user->email;
        $validatedData['faculty'] = optional($user->department)->name ?? $exam->faculty;

        // Handle exam document update
        if ($request->hasFile('exam_document')) {
            // Delete old exam document if it exists
            if ($exam->exam_document && file_exists(public_path($exam->exam_document))) {
                unlink(public_path($exam->exam_document));
            }
            
            // Store new exam document
            $examFile = $request->file('exam_document');
            $examFileName = $this->buildStoredFilename($examFile);
            $examFile->move(public_path('exams/documents'), $examFileName);
            $validatedData['exam_document'] = 'exams/documents/' . $examFileName;
        } else {
            // Keep existing exam document if no new file is uploaded
            $validatedData['exam_document'] = $exam->exam_document;
        }

        // Handle answer key update
        if ($request->hasFile('answer_key')) {
            // Delete old answer key if it exists
            if ($exam->answer_key && file_exists(public_path($exam->answer_key))) {
                unlink(public_path($exam->answer_key));
            }
            
            // Store new answer key
            $answerFile = $request->file('answer_key');
            $answerFileName = $this->buildStoredFilename($answerFile);
            $answerFile->move(public_path('exams/answer_keys'), $answerFileName);
            $validatedData['answer_key'] = 'exams/answer_keys/' . $answerFileName;
        } else {
            // Keep existing answer key if no new file is uploaded
            $validatedData['answer_key'] = $exam->answer_key;
        }

        $exam->update($validatedData);

        return redirect()->route('dashboard.all.exams')->with('success', 'Exam Document updated successfully.');
    }

    private function buildStoredFilename($uploadedFile): string
    {
        $original = $uploadedFile->getClientOriginalName();
        $ext = $uploadedFile->getClientOriginalExtension();
        $base = pathinfo($original, PATHINFO_FILENAME);
        $cleanBase = preg_replace('/[^A-Za-z0-9._-]+/', '_', $base);
        $cleanBase = trim($cleanBase, '_') ?: 'file';
        $dateSlug = now()->format('Y-m-d');
        return time() . '_' . $cleanBase . '_' . $dateSlug . '.' . $ext;
    }

    public function downloadExam(Exam $exam)
    {
        $user = auth()->user();
        $isOwner = $exam->user_id === $user->id;
        $viaSharedFolder = $exam->folders()
            ->whereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->exists();
        if (!$isOwner && !$user->is_admin && !$viaSharedFolder) {
            abort(403, 'You do not have access to this exam.');
        }

        // Check if file exists in new storage
        $filePath = public_path($exam->exam_document);
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        // Get file info
        $extension = pathinfo($exam->exam_document, PATHINFO_EXTENSION);
        
        // Create a proper filename for download (include upload date)
        $dateSlug = $exam->created_at ? $exam->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $downloadName = $exam->course_title . '_' . $exam->course_code . '_' . $dateSlug . '.' . $extension;
        $downloadName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $downloadName); // Sanitize filename

        // Return the file as a download response
        return response()->download($filePath, $downloadName);
    }

    public function downloadAnswerKey(Exam $exam)
    {
        $user = auth()->user();
        $isOwner = $exam->user_id === $user->id;
        $viaSharedFolder = $exam->folders()
            ->whereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->exists();
        if (!$isOwner && !$user->is_admin && !$viaSharedFolder) {
            abort(403, 'You do not have access to this answer key.');
        }

        // Check if answer key exists in new storage
        if (!$exam->answer_key) {
            abort(404, 'Answer key not found');
        }
        
        $filePath = public_path($exam->answer_key);
        if (!file_exists($filePath)) {
            abort(404, 'Answer key file not found');
        }

        // Get file info
        $extension = pathinfo($exam->answer_key, PATHINFO_EXTENSION);
        
        // Create a proper filename for download (include upload date)
        $dateSlug = $exam->created_at ? $exam->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $downloadName = $exam->course_title . '_' . $exam->course_code . '_AnswerKey_' . $dateSlug . '.' . $extension;
        $downloadName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $downloadName); // Sanitize filename

        // Return the file as a download response
        return response()->download($filePath, $downloadName);
    }

    public function destroy(Exam $exam)
    {
        $this->authorizeManage($exam);

        $exam->delete();

        return redirect()->back()->with('success', 'Document deleted successfully');
    }

    public function delete(Exam $exam)
    {
        $this->authorizeManage($exam);

        $exam->delete();

        return redirect()->route('dashboard.upload.document')->with('success', 'Document deleted successfully');
    }

    /**
     * Write guard for an Exam record. Only the exam's owner (or a super admin)
     * may edit or delete it. Folder sharing — viewer OR editor — grants
     * view/download access only, never write access to the underlying exam.
     */
    private function authorizeManage(Exam $exam): void
    {
        $user = Auth::user();
        if (!$user || ($exam->user_id !== $user->id && !$user->isSuperAdmin())) {
            abort(403, 'You do not have permission to modify this document.');
        }
    }

    public function filter(Request $request)
    {
        $facultyId = $request->input('faculty_id');
        $tags = $request->input('tags');
        $semesters = $request->input('semesters');
        $years = $request->input('years');

        $examsQuery = Exam::query();

        if ($facultyId) {
            $examsQuery->where('faculty', $facultyId);
        }

        if (!empty($tags)) {
            $examsQuery->whereIn('tags', $tags);
        }

        if (!empty($semesters)) {
            $examsQuery->whereIn('semester', $semesters);
        }

        if (!empty($years)) {
            $examsQuery->whereIn(DB::raw('YEAR(exam_date)'), $years);
        }

        $exams = $examsQuery->get();

        return response()->json($exams);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        // $exams = Exam::where('is_approve', 1)
        //             ->where(function($q) use ($query) {
        //                 $q->where('student_id', 'LIKE', "%$query%")
        //                     ->orWhere('document_id', 'LIKE', "%$query%")
        //                     ->orWhere('faculty', 'LIKE', "%$query%")
        //                     ->orWhere('course_code', 'LIKE', "%$query%")
        //                     ->orWhere('course_title', 'LIKE', "%$query%")
        //                     ->orWhere('semester', 'LIKE', "%$query%")
        //                     ->orWhere('academic_year', 'LIKE', "%$query%")
        //                     ->orWhere('exams_type', 'LIKE', "%$query%")
        //                     ->orWhere('instructor_name', 'LIKE', "%$query%")
        //                     ->orWhere('exam_date', 'LIKE', "%$query%")
        //                     ->orWhere('exam_format', 'LIKE', "%$query%")
        //                     ->orWhere('duration', 'LIKE', "%$query%")
        //                     ->orWhere('tags', 'LIKE', "%$query%");

        //             })
        //             ->paginate(20);

        $exams = Exam::where('is_approve', 1)
        ->where(function($q) use ($query) {
            $q->where('student_id', 'LIKE', "%$query%")
                ->orWhere('document_id', 'LIKE', "%$query%")
                ->orWhere('faculty', 'LIKE', "%$query%")
                ->orWhere('course_code', 'LIKE', "%$query%")
                ->orWhere('course_title', 'LIKE', "%$query%")
                ->orWhere('semester', 'LIKE', "%$query%")
                ->orWhere('academic_year', 'LIKE', "%$query%")
                ->orWhere('exams_type', 'LIKE', "%$query%")
                ->orWhere('instructor_name', 'LIKE', "%$query%")
                ->orWhere('exam_date', 'LIKE', "%$query%")
                ->orWhere('exam_format', 'LIKE', "%$query%")
                ->orWhere('duration', 'LIKE', "%$query%")
                ->orWhere('tags', 'LIKE', "%$query%");
        })->get();

        $files = File::where('is_approve', 1)
                ->where(function ($q) use ($query) {
                    $q->where('depositor_name', 'LIKE', "%$query%")
                        ->orWhere('document_id', 'LIKE', "%$query%")
                        ->orWhere('file_title', 'LIKE', "%$query%")
                        ->orWhere('file_format', 'LIKE', "%$query%")
                        ->orWhere('year_created', 'LIKE', "%$query%")
                        ->orWhere('year_deposit', 'LIKE', "%$query%");
                })->get();

        $results = [
            'exams' => $exams,
            'files' => $files,
        ];
        $uniqueFaculties = $exams->pluck('faculty')->unique()->values()->all();
        $uniqueTags = $exams->pluck('tags')->unique()->values()->all();
        $uniqueSemesters = $exams->pluck('semester')->unique()->values()->all();

        return view('admin.documents',[
            'exams' => $results,
            'faculties' => $uniqueFaculties,
            'tags' => $uniqueTags,
            'semesters' => $uniqueSemesters,
            'years' => Exam::select(DB::raw('YEAR(created_at) as year'))->distinct()->pluck('year'),
        ]);
    }
}
