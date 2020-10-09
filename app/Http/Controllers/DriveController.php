<?php

namespace App\Http\Controllers;

use App\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use File;

class DriveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function upload()
    {
        Storage::cloud()->put('test.txt', 'Hello World');
            return 'File was saved to Google Drive';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function putexisting(Request $request)
    {
        if ($request->file('file') == null) {
            $file = "";
        }else{
           $file = $request->file('file')->store('/');  
        }
  
        $filename = $file;
        $filePath = storage_path($filename);
        $fileData = File::get($filePath);
    
        Storage::cloud()->put($filename, $fileData);
        return 'File was saved to Google Drive';
    }

    public function listing()
    {
        $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    return $contents->where('type', '=', 'file'); // files
    
    }

    public function folder()
    {
        $dir = '/';
        $recursive = false; // Get subdirectories also?
        $contents = collect(Storage::cloud()->listContents($dir, $recursive));
        return $contents->where('type', '=', 'dir'); // directories
        // return $contents->where('type', '=', 'file'); // files
    }

    public function foldercontents()
    {// The human readable folder name to get the contents of...
    // For simplicity, this folder is assumed to exist in the root directory.
    $folder = 'Test Dir';

    // Get root directory contents...
    $contents = collect(Storage::cloud()->listContents('/', false));

    // Find the folder you are looking for...
    $dir = $contents->where('type', '=', 'dir')
        ->where('filename', '=', $folder)
        ->first(); // There could be duplicate directory names!

    if ( ! $dir) {
        return 'No such folder!';
    }

    // Get the files inside the folder...
    $files = collect(Storage::cloud()->listContents($dir['path'], false))
        ->where('type', '=', 'file');

    return $files->mapWithKeys(function($file) {
        $filename = $file['filename'].'.'.$file['extension'];
        $path = $file['path'];

        // Use the path to download each file via a generated link..
        // Storage::cloud()->get($file['path']);

        return [$filename => $path];
    });
    }

    public function get()
    {
        $filename = 'Screenshot from 2020-09-26 01-04-30.png';

        $dir = '/';
        $recursive = false; // Get subdirectories also?
        $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!
    
        //return $file; // array with file info
        $rawData = Storage::cloud()->get($file['path']);
        return response($rawData, 200)
            ->header('ContentType', $file['mimetype'])
            ->header('Content-Disposition', "attachment; filename='$filename'");
    }

    public function largerfile()
    {
       // Assume this is a large file...
    $filename = 'laravel.png';
    $filePath = public_path($filename);

    // Upload using a stream...
    Storage::cloud()->put($filename, fopen($filePath, 'r+'));

    // Get file listing...
    $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));

    // Get file details...
    $file = $contents
        ->where('type', '=', 'file')
        ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
        ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
        ->first(); // there can be duplicate file names!

    //return $file; // array with file info

    // Store the file locally...
    //$readStream = Storage::cloud()->getDriver()->readStream($file['path']);
    //$targetFile = storage_path("downloaded-{$filename}");
    //file_put_contents($targetFile, stream_get_contents($readStream), FILE_APPEND);

    // Stream the file to the browser...
    $readStream = Storage::cloud()->getDriver()->readStream($file['path']);

    return response()->stream(function () use ($readStream) {
        fpassthru($readStream);
    }, 200, [
        'Content-Type' => $file['mimetype'],
        //'Content-disposition' => 'attachment; filename="'.$filename.'"', // force download?
    ]);
    }
    
    public function createDirectory()
    {
        Storage::cloud()->makeDirectory('New sub irectory');
        return 'Directory was created in Google Drive';
    }

    public function CreateSubDirectory()
    {
        // Create parent dir
    Storage::cloud()->makeDirectory('testing sub directory');

    // Find parent dir for reference
    $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));

    $dir = $contents->where('type', '=', 'dir')
        ->where('filename', '=', 'Test Dir')
        ->first(); // There could be duplicate directory names!

    if ( ! $dir) {
        return 'Directory does not exist!';
    }

    // Create sub dir
    Storage::cloud()->makeDirectory($dir['path'].'/Sub Dir');

    return 'Sub Directory was created in Google Drive';
    }

    public function PutInDirectory()
    {
         $dir = '/';
        $recursive = false; // Get subdirectories also?
        $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    
        $dir = $contents->where('type', '=', 'dir')
            ->where('filename', '=', 'Test Dir')
            ->first(); // There could be duplicate directory names!
    
        if ( ! $dir) {
            return 'Directory does not exist!';
        }
    
        Storage::cloud()->put($dir['path'].'/test.txt', 'Hello World');
    
        return 'File was created in the sub directory in Google Drive';
    }

    public function newest()
    {
        $filename = 'test.txt';

        Storage::cloud()->put($filename, \Carbon\Carbon::now()->toDateTimeString());
    
        $dir = '/';
        $recursive = false; // Get subdirectories also?
    
        $file = collect(Storage::cloud()->listContents($dir, $recursive))
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->sortBy('timestamp')
            ->last();
    
        return Storage::cloud()->get($file['path']);
    }

    public function delete()
    {
        $filename = 'test.txt';

        // First we need to create a file to delete
        Storage::cloud()->makeDirectory('Test Dir');
    
        // Now find that file and use its ID (path) to delete it
        $dir = '/';
        $recursive = false; // Get subdirectories also?
        $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
            ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
            ->first(); // there can be duplicate file names!
    
        Storage::cloud()->delete($file['path']);
    
        return 'File was deleted from Google Drive';
    }
    public function RenameDirectory()
    {
        $directoryName = 'new-test';

    // First we need to create a directory to rename
    Storage::cloud()->makeDirectory($directoryName);

    // Now find that directory and use its ID (path) to rename it
    $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));

    $directory = $contents
        ->where('type', '=', 'dir')
        ->where('filename', '=', $directoryName)
        ->first(); // there can be duplicate file names!

    Storage::cloud()->move($directory['path'], 'hello');

    return 'Directory was renamed in Google Drive';
    }

    public function share()
    {
        $filename = 'test.txt';

    // Store a demo file
    Storage::cloud()->put($filename, 'Hello World');

    // Get the file to find the ID
    $dir = '/';
    $recursive = false; // Get subdirectories also?
    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
    $file = $contents
        ->where('type', '=', 'file')
        ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
        ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
        ->first(); // there can be duplicate file names!

    // Change permissions
    // - https://developers.google.com/drive/v3/web/about-permissions
    // - https://developers.google.com/drive/v3/reference/permissions
    $service = Storage::cloud()->getAdapter()->getService();
    $permission = new \Google_Service_Drive_Permission();
    $permission->setRole('reader');
    $permission->setType('anyone');
    $permission->setAllowFileDiscovery(false);
    $permissions = $service->permissions->create($file['basename'], $permission);

    return Storage::cloud()->url($file['path']);
    }

    public function export($basename)
    {
        $service = Storage::cloud()->getAdapter()->getService();
        $mimeType = 'application/pdf';
        $export = $service->files->export($basename, $mimeType);
    
        return response($export->getBody(), 200, $export->getHeaders());
    }

}
