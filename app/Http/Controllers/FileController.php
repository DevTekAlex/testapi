<?php

namespace App\Http\Controllers;

use App\Models\FileData;
use App\Notifications\FileUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request) {
        $messages = [
            'file.required' => 'File needed to download.',
            'file.max' => 'Maximum file size for upload is 5MB.',
            'description.required' => 'File description required.',
            'description.max' => 'The file description must not exceed 500 characters.',
        ];

        $request->validate([
            'file' => 'required|file|max:5120',
            'description' => 'required|string|max:500',
        ], $messages);

        if (!in_array($request->file->extension(), ['txt', 'xls', 'xlsx', 'doc', 'docx', 'pdf', 'png', 'jpg', 'jpeg', 'gif'])) {
            return response()->json(['error' => 'Invalid file format.'], 422);
        }

        $path = $request->file('file')->store('public/files');

        $secretKey = Str::random(40);
        $fileData = FileData::create([
            'file_name' => $request->file->getClientOriginalName(),
            'description' => $request->description,
            'secret_key' => $secretKey,
            'file_path' => $path,
        ]);

        Notification::route('mail', 'robot@mail.com')->notify(new FileUploaded($fileData));

        return response()->json([
            'message' => 'File uploaded successfully.',
            'download_url' => url('/download/'.$fileData->secret_key)
        ]);
    }



    public function getFiles() {
        $files = FileData::orderBy('created_at', 'desc')->get();
        return response()->json(['files' => $files]);
    }


    public function updateDescription(Request $request, $id) {
        $request->validate(['description' => 'required|string|max:500']);

        $file = FileData::findOrFail($id);
        $file->update(['description' => $request->description]);

        return response()->json(['message' => 'File description updated.']);
    }

    public function deleteFile($id) {
        $file = FileData::findOrFail($id);
        Storage::delete($file->file_path);
        $file->delete();

        return response()->json(['message' => 'File deleted.']);
    }

    public function deleteAllFiles() {
        $files = FileData::all();
        foreach ($files as $file) {
            Storage::delete($file->file_path);
            $file->delete();
        }

        return response()->json(['message' => 'All files deleted.']);
    }

    public function download($secretKey) {
        $fileData = FileData::where('secret_key', $secretKey)->firstOrFail();

        $filePath = storage_path('app/' . $fileData->file_path);
        if (!File::exists($filePath)) {
            abort(404);
        }

        $headers = ['Content-Type' => 'application/octet-stream'];
        $fileName = $fileData->file_name;

        return response()->download($filePath, $fileName, $headers);
    }

}
