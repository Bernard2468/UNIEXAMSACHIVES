<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SystemDocumentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemDocumentationController extends Controller
{
    /**
     * Display a listing of system documentation (view-only for regular users)
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $documents = SystemDocumentation::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
        
        return view('admin.system-documentation.index', compact('documents'));
    }

    /**
     * Preview document (for PDFs)
     */
    public function preview($id)
    {
        $document = SystemDocumentation::findOrFail($id);
        
        if (!$document->isPdf() || $document->isZip()) {
            return redirect()->route('dashboard.system-documentation')
                ->with('error', 'Only PDF files can be previewed. ZIP files must be downloaded.');
        }

        $filePath = $this->resolveDocumentPath($document->file_path);

        if (!$filePath) {
            return redirect()->route('dashboard.system-documentation')
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
        $document = SystemDocumentation::findOrFail($id);
        
        $filePath = $this->resolveDocumentPath($document->file_path);

        if (!$filePath) {
            return redirect()->route('dashboard.system-documentation')
                ->with('error', 'File not found.');
        }

        return response()->download($filePath, $document->title . '.' . $document->file_type);
    }

    /**
     * Resolve a stored document's absolute path on disk. Checks the persistent
     * storage location first (where new uploads go) and falls back to the legacy
     * public/ location for older files. Returns null if missing in both.
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
}
