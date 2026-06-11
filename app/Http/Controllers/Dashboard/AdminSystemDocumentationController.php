<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SystemDocumentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminSystemDocumentationController extends Controller
{
    /**
     * Check if user is authorized (admins only - those with is_admin = 0)
     */
    private function checkAuthorization()
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('dashboard.system-documentation')
                ->with('error', 'Access denied. Only administrators can manage documents.');
        }
        return null;
    }

    /**
     * Display a listing of system documentation (with management for admins)
     */
    public function index(Request $request)
    {
        if ($redirect = $this->checkAuthorization()) {
            return $redirect;
        }

        $perPage = $request->get('per_page', 10);
        $documents = SystemDocumentation::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        
        return view('admin.system-documentation.manage', compact('documents'));
    }

    /**
     * Store a newly created documentation
     */
    public function store(Request $request)
    {
        if ($redirect = $this->checkAuthorization()) {
            return $redirect;
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'document_file' => 'required|file|mimes:pdf,doc,docx,zip|max:10240', // 10MB max
        ]);

        // Handle file upload
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            
            // Get file info BEFORE moving (important!)
            $fileType = strtolower($file->getClientOriginalExtension());
            $fileSize = $this->formatBytes($file->getSize());
            
            // Store under storage/app/public so the file survives code deploys.
            // (public/ is git-ignored and gets replaced on deploy, which is why
            // previously-uploaded files went missing while the DB rows remained.)
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('system-documentation', $fileName, 'public');

            SystemDocumentation::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('dashboard.system-documentation.manage')
                ->with('success', 'Document uploaded successfully.');
        }

        return redirect()->route('dashboard.system-documentation.manage')
            ->with('error', 'Failed to upload document.');
    }

    /**
     * Update the specified documentation
     */
    public function update(Request $request, $id)
    {
        if ($redirect = $this->checkAuthorization()) {
            return $redirect;
        }

        $document = SystemDocumentation::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'document_file' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
        ]);

        $document->title = $validated['title'];
        $document->description = $validated['description'];

        // Handle file replacement if new file is uploaded
        if ($request->hasFile('document_file')) {
            // Delete the old file wherever it currently lives.
            $oldFilePath = $this->resolveDocumentPath($document->file_path);
            if ($oldFilePath) {
                @unlink($oldFilePath);
            }

            // Upload new file
            $file = $request->file('document_file');

            // Get file info BEFORE moving (important!)
            $fileType = strtolower($file->getClientOriginalExtension());
            $fileSize = $this->formatBytes($file->getSize());

            // Store under storage/app/public so the file survives code deploys.
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $file->getClientOriginalName());
            $document->file_path = $file->storeAs('system-documentation', $fileName, 'public');
            $document->file_type = $fileType;
            $document->file_size = $fileSize;
        }

        $document->save();

        return redirect()->route('dashboard.system-documentation.manage')
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified documentation
     */
    public function destroy($id)
    {
        if ($redirect = $this->checkAuthorization()) {
            return $redirect;
        }

        $document = SystemDocumentation::findOrFail($id);
        
        // Delete file from storage (wherever it lives)
        $filePath = $this->resolveDocumentPath($document->file_path);
        if ($filePath) {
            @unlink($filePath);
        }

        $documentTitle = $document->title;
        $document->delete();

        return redirect()->route('dashboard.system-documentation.manage')
            ->with('success', "Document '{$documentTitle}' deleted successfully.");
    }

    /**
     * Preview document (for PDFs)
     */
    public function preview($id)
    {
        if ($redirect = $this->checkAuthorization()) {
            return $redirect;
        }

        $document = SystemDocumentation::findOrFail($id);
        
        if (!$document->isPdf() || $document->isZip()) {
            return redirect()->route('dashboard.system-documentation.manage')
                ->with('error', 'Only PDF files can be previewed. ZIP files must be downloaded.');
        }

        $filePath = $this->resolveDocumentPath($document->file_path);

        if (!$filePath) {
            return redirect()->route('dashboard.system-documentation.manage')
                ->with('error', 'File not found.');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($document->file_path) . '"'
        ]);
    }

    /**
     * Download document
     */
    public function download($id)
    {
        if ($redirect = $this->checkAuthorization()) {
            return $redirect;
        }

        $document = SystemDocumentation::findOrFail($id);
        
        $filePath = $this->resolveDocumentPath($document->file_path);

        if (!$filePath) {
            return redirect()->route('dashboard.system-documentation.manage')
                ->with('error', 'File not found.');
        }

        return response()->download($filePath, $document->title . '.' . $document->file_type);
    }

    /**
     * Resolve a stored document's absolute path on disk. Checks the persistent
     * storage location first (where new uploads go) and falls back to the legacy
     * public/ location for files uploaded before the storage change. Returns null
     * if the physical file is missing in both.
     */
    private function resolveDocumentPath(?string $relativePath): ?string
    {
        if (!$relativePath) {
            return null;
        }
        foreach ([
            storage_path('app/public/' . $relativePath),
            public_path($relativePath),
        ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Format bytes to human readable size
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
