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
        ], 200);
    }



    public function getFiles() {
        try {
            $files = FileData::all();
            return response()->json(['files' => $files], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while receiving files.'], 500);
        }
    }


    public function updateDescription(Request $request, $id) {
        try {
            $request->validate(['description' => 'required|string|max:500']);

            $file = FileData::findOrFail($id);
            $file->update(['description' => $request->description]);

            return response()->json(['message' => 'File description updated.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'File not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the file description.'], 500);
        }
    }

    public function deleteFile($id) {
        try {
            $file = FileData::findOrFail($id);
            Storage::delete($file->file_path);
            $file->delete();

            return response()->json(['message' => 'File deleted.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'File not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the file.'], 500);
        }
    }

    public function deleteAllFiles() {
        try {
            $files = FileData::all(['file_path']);
            foreach ($files as $file) {
                Storage::delete($file->file_path);
            }
            FileData::truncate();

            return response()->json(['message' => 'All files deleted.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting all files.'], 500);
        }
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
