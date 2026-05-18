<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'file_title' => 'required|string',
            'year_created' => 'required|date|before_or_equal:today',
            'document_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx',
        ]);

        $user = Auth::user();
        $validatedData['depositor_name'] = trim($user->first_name . ' ' . $user->last_name);
        $validatedData['email'] = $user->email;
        $validatedData['unit'] = optional($user->department)->name ?? 'Unassigned';

        if ($request->hasFile('document_file')) {
            $uploadedFile = $request->file('document_file');
            $fileName = $this->buildStoredFilename($uploadedFile);
            $uploadedFile->move(public_path('exams/files'), $fileName);
            $validatedData['document_file'] = 'exams/files/' . $fileName;
            $validatedData['file_format'] = $this->detectFileFormat($uploadedFile->getClientOriginalExtension());
        }
        $validatedData['year_deposit'] = now()->toDateString();
        $validatedData['user_id'] = $user->id;
        $validatedData['document_id'] = random_int(1000000000, 9999999999);
        $validatedData['is_approve'] = true;
        File::create($validatedData);
        return redirect()->route('dashboard')->with('success', 'File has been deposited successfully.');
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

    private function detectFileFormat(string $extension): string
    {
        $map = [
            'pdf'  => 'Pdf',
            'doc'  => 'Word', 'docx' => 'Word',
            'xls'  => 'Excel', 'xlsx' => 'Excel',
            'csv'  => 'Csv',
            'ppt'  => 'PowerPoint', 'pptx' => 'PowerPoint',
        ];
        return $map[strtolower($extension)] ?? strtoupper($extension);
    }


    public function edit(File $file)
    {
        return view('admin.file_form', [
            'file' => $file,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'file_title' => 'required|string',
            'year_created' => 'required|date|before_or_equal:today',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx',
        ]);

        $file = File::findOrFail($id);

        $user = Auth::user();
        $validatedData['depositor_name'] = trim($user->first_name . ' ' . $user->last_name);
        $validatedData['email'] = $user->email;
        $validatedData['unit'] = optional($user->department)->name ?? $file->unit;

        if ($request->hasFile('document_file')) {
            if ($file->document_file && file_exists(public_path($file->document_file))) {
                unlink(public_path($file->document_file));
            }
            $newFile = $request->file('document_file');
            $fileName = $this->buildStoredFilename($newFile);
            $newFile->move(public_path('exams/files'), $fileName);
            $validatedData['document_file'] = 'exams/files/' . $fileName;
            $validatedData['file_format'] = $this->detectFileFormat($newFile->getClientOriginalExtension());
        } else {
            $validatedData['document_file'] = $file->document_file;
        }

        $file->update($validatedData);

        return redirect()->route('dashboard')
            ->with('success', 'File has been updated successfully.');
    }

    public function uploadedFile(){
        return view('admin.files',[
            'files' => File::where('user_id', Auth::id())->get(),
        ]);
    }
    public function allUploadedFile(){
        // Only show user's own files (no approval system means users manage their own content)
        $files = File::where('user_id', Auth::id())
            ->whereDoesntHave('folders')
            ->orderBy('created_at', 'desc')
            ->get();
        $folders = Folder::where('user_id', Auth::id())
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();
        $sharedFolders = Auth::user()->sharedFolders()
            ->withCount(['files', 'exams'])
            ->with('user:id,first_name,last_name,email,profile_picture')
            ->orderBy('folder_shares.created_at', 'desc')
            ->get();
        return view('admin.all_files', compact('files', 'folders', 'sharedFolders'));
    }

    // Unified Files view (no more pending/approved separation)
    public function myFiles(){
        $files = File::where('user_id', Auth::user()->id)->get();
        return view('admin.my_files',compact('files'));
    }

    public function allFiles(){
        // Only show user's own files (no approval system means users manage their own content)
        $files = File::where('user_id', Auth::id())
            ->whereDoesntHave('folders')
            ->orderBy('created_at', 'desc')
            ->get();
        $folders = Folder::where('user_id', Auth::id())
            ->withCount(['files', 'exams'])
            ->orderBy('created_at', 'desc')
            ->get();
        $sharedFolders = Auth::user()->sharedFolders()
            ->withCount(['files', 'exams'])
            ->with('user:id,first_name,last_name,email,profile_picture')
            ->orderBy('folder_shares.created_at', 'desc')
            ->get();
        return view('admin.all_files_list', compact('files', 'folders', 'sharedFolders'));
    }

    public function destroy(File $file)
    {
        $file->delete();

        return redirect()->back()->with('success', 'File deleted successfully');
    }

    public function downloadFile(File $file)
    {
        $user = auth()->user();

        // Permission logic:
        // 1. The owner can always download
        // 2. Anyone with access to a folder containing this file can download
        //    (covers shared folders — viewer or editor)
        // 3. Admins keep their existing access
        $isOwner = $file->user_id === $user->id;
        $viaSharedFolder = $file->folders()
            ->whereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->exists();

        if (!$isOwner && !$user->is_admin && !$viaSharedFolder) {
            abort(403, 'You do not have access to this file.');
        }

        // Check if file exists in public storage
        $filePath = public_path($file->document_file);
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        // Get file info
        $extension = pathinfo($file->document_file, PATHINFO_EXTENSION);
        
        // Create a proper filename for download (include upload date)
        $dateSlug = $file->year_deposit ? \Carbon\Carbon::parse($file->year_deposit)->format('Y-m-d') : now()->format('Y-m-d');
        $downloadName = $file->file_title . '_' . $dateSlug . '.' . $extension;
        $downloadName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $downloadName); // Sanitize filename

        // Return the file as a download response
        return response()->download($filePath, $downloadName);
    }

    /**
     * Check if current user can download a specific file
     */
    public static function canDownloadFile($file)
    {
        $user = auth()->user();
        
        // Admins can download any file
        if ($user->is_admin) {
            return true;
        }
        
        // Users can download their own files
        if ($file->user_id === $user->id) {
            return true;
        }
        
        // Approvers cannot download files they didn't upload
        return false;
    }

}
