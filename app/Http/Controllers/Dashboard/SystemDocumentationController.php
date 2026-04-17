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

        $filePath = public_path($document->file_path);
        
        if (!file_exists($filePath)) {
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
        
        $filePath = public_path($document->file_path);
        
        if (!file_exists($filePath)) {
            return redirect()->route('dashboard.system-documentation')
                ->with('error', 'File not found.');
        }

        return response()->download($filePath, $document->title . '.' . $document->file_type);
    }
}
