<?php

namespace App\Http\Controllers;

use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Import');
    }

    public function preview(Request $request, CsvImportService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,ofx,qfx|max:5120',
            'has_header' => 'nullable|boolean',
        ]);

        $preview = $service->preview($request->file('file'), [
            'date' => '',
            'amount' => '',
            'description' => '',
            'category' => '',
        ]);

        // Store uploaded file to a permanent location for the import step
        $storedPath = $request->file('file')->store('imports', 'local');
        $request->session()->put('import_stored_path', $storedPath);

        return response()->json($preview);
    }

    public function import(Request $request, CsvImportService $service)
    {
        $request->validate([
            'mapping' => 'required|array',
            'mapping.date' => 'nullable|string',
            'mapping.amount' => 'nullable|string',
            'mapping.description' => 'nullable|string',
            'mapping.category' => 'nullable|string',
            'has_header' => 'nullable|boolean',
        ]);

        $storedPath = $request->session()->pull('import_stored_path');

        if (!$storedPath || !Storage::disk('local')->exists($storedPath)) {
            return back()->withErrors(['file' => 'Uploaded file not found. Please try again.']);
        }

        $fullPath = Storage::disk('local')->path($storedPath);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $fullPath,
            basename($storedPath),
            mime_content_type($fullPath),
            null,
            true
        );

        $result = $service->import(
            auth()->id(),
            $uploadedFile,
            $request->input('mapping'),
            $request->boolean('has_header', true)
        );

        // Clean up stored file
        Storage::disk('local')->delete($storedPath);

        return response()->json($result);
    }
}
