<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'depositor_name' => 'required|string',
            'email' => 'required|string|email',
            'phone_number' => 'required|string',
            'file_title' => 'required|string',
            'file_format' => 'required|string',
            'year_created' => 'required|date',
            'year_deposit' => 'required|date',
            'document_file' => 'required|file',
            'unit' => 'required|string',
        ]);

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('exams/files'), $fileName);
            $validatedData['document_file'] = 'exams/files/' . $fileName;
        }
        $validatedData['user_id'] = Auth::user()->id;
        $validatedData['document_id'] = random_int(1000000000, 9999999999);
        $validatedData['is_approve'] = true; // Auto-approve all uploads
        File::create($validatedData);
        return redirect()->route('dashboard')->with('success', 'File has been deposited successfully.');
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
            'depositor_name' => 'required|string',
            'email' => 'required|string|email',
            'phone_number' => 'required|string',
            'file_title' => 'required|string',
            'file_format' => 'required|string',
            'year_created' => 'required|date',
            'year_deposit' => 'required|date',
            'document_file' => 'nullable|file', // Changed to nullable
            'unit' => 'required|string',
        ]);

        $file = File::findOrFail($id);

        // Handle file update if new file is uploaded
        if ($request->hasFile('document_file')) {
            // Delete old file if it exists
            if ($file->document_file && file_exists(public_path($file->document_file))) {
                unlink(public_path($file->document_file));
            }
            
            // Store new file
            $newFile = $request->file('document_file');
            $fileName = time() . '_' . $newFile->getClientOriginalName();
            $newFile->move(public_path('exams/files'), $fileName);
            $validatedData['document_file'] = 'exams/files/' . $fileName;
        } else {
            // Keep existing file if no new file uploaded
            $validatedData['document_file'] = $file->document_file;
        }

        // Update the file record
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
        return view('admin.all_files',[
            'files' => File::where('user_id', Auth::id())->get(),
        ]);
    }

    // Unified Files view (no more pending/approved separation)
    public function myFiles(){
        $files = File::where('user_id', Auth::user()->id)->get();
        return view('admin.my_files',compact('files'));
    }

    public function allFiles(){
        // Only show user's own files (no approval system means users manage their own content)
        $files = File::where('user_id', Auth::user()->id)->get();
        return view('admin.all_files_list',compact('files'));
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
        // 1. Admins can download any file
        // 2. Users can download their own files
        // 3. Approvers can only VIEW files (not download) unless it's their own file
        if (!$user->is_admin && $file->user_id !== $user->id) {
            abort(403, 'You can only download your own files. Use the view button to review files for approval.');
        }

        // Check if file exists in public storage
        $filePath = public_path($file->document_file);
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        // Get file info
        $extension = pathinfo($file->document_file, PATHINFO_EXTENSION);
        
        // Create a proper filename for download
        $downloadName = $file->file_title . '.' . $extension;
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
