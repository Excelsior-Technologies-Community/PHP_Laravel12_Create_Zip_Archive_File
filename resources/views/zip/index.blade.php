<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Zip Archive - Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles for better UX */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            height: 100%;
            width: 100%;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 0.75rem;
            transition: all 0.3s;
            cursor: pointer;
            min-height: 200px;
        }

        .file-input-label:hover {
            border-color: #10b981;
            background-color: #f0fdf4;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .file-input-label.dragover {
            border-color: #10b981;
            background-color: #dcfce7;
            border-style: solid;
            transform: scale(1.02);
        }

        .file-item {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .upload-progress {
            display: none;
        }

        .uploading .upload-progress {
            display: block;
        }

        .uploading .upload-button {
            display: none;
        }
        
        .file-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            margin-right: 12px;
        }
        
        .pdf { background-color: #fee; color: #f00; }
        .image { background-color: #eef; color: #00f; }
        .document { background-color: #efe; color: #090; }
        .archive { background-color: #ffe; color: #f90; }
        .other { background-color: #eee; color: #666; }
    </style>
</head>
<<<<<<< HEAD
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg mb-4">
                    <i class="fas fa-file-archive text-3xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-2">File Upload & Zip Creator</h1>
                <p class="text-gray-600 text-lg">Upload files and instantly create a compressed zip archive</p>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                    <div>
                        <p class="font-semibold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
=======

<body class="bg-gray-100 min-h-screen">

    <div class="container mx-auto px-4 py-8">

        <div class="max-w-7xl mx-auto">

            <!-- Header -->

            <div class="text-center mb-10">

                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg mb-4">

                    <i class="fas fa-file-archive text-3xl text-white"></i>

>>>>>>> development
                </div>

                <h1 class="text-4xl font-bold text-gray-800">
                    ZIP Archive Dashboard
                </h1>

                <p class="text-gray-600 mt-2">
                    Upload files, create ZIP archives and manage download history.
                </p>

            </div>

            <!-- Flash Messages -->

            @if(session('success'))
<<<<<<< HEAD
                <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3 text-xl"></i>
                    <div>
                        <p class="font-semibold">Success</p>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Main Upload Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-cloud-upload-alt mr-3 text-green-500"></i>
                        Upload Files & Create Zip Archive
                    </h2>
                    <p class="text-gray-600">Select multiple files, upload them, and download as a single zip file</p>
                </div>
                
                <form id="uploadForm" action="{{ route('zip.create.from.upload') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                    @csrf
                    
                    <!-- Drag & Drop Area -->
                    <div class="mb-8">
                        <div class="file-input-wrapper">
                            <div class="file-input-label" id="dropArea">
                                <div class="text-center p-4">
                                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-green-600"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Drop your files here</h3>
                                    <p class="text-gray-500 mb-4">or click to browse files on your computer</p>
                                    <div class="inline-flex items-center space-x-4 text-sm text-gray-400">
                                        <span class="flex items-center">
                                            <i class="fas fa-file-pdf mr-2 text-red-500"></i>PDF
                                        </span>
                                        <span class="flex items-center">
                                            <i class="fas fa-file-image mr-2 text-blue-500"></i>Images
                                        </span>
                                        <span class="flex items-center">
                                            <i class="fas fa-file-word mr-2 text-blue-600"></i>Documents
                                        </span>
                                        <span class="flex items-center">
                                            <i class="fas fa-file-archive mr-2 text-purple-500"></i>Archives
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <input type="file" name="uploaded_files[]" id="uploaded_files" multiple>
                        </div>
                    </div>
                    
                    <!-- Selected Files Section -->
                    <div id="selectedFilesContainer" class="hidden mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Selected Files</h3>
                                <p id="fileSummary" class="text-sm text-gray-500">Ready to compress</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span id="fileCount" class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                    0 files
                                </span>
                                <button type="button" onclick="clearAllFiles()" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                    <i class="fas fa-times mr-1"></i>Clear All
                                </button>
                            </div>
                        </div>
                        
                        <div id="selectedFilesList" class="space-y-3 max-h-64 overflow-y-auto p-4 border border-gray-200 rounded-xl bg-gray-50">
                            <!-- Files will be listed here -->
                            <div class="text-center py-8 text-gray-400" id="emptyState">
                                <i class="fas fa-folder-open text-4xl mb-3"></i>
                                <p>No files selected yet</p>
                            </div>
                        </div>
                        
                        <!-- Total Size -->
                        <div id="totalSize" class="mt-4 text-right text-sm text-gray-500 hidden">
                            Total size: <span id="totalSizeValue">0 MB</span>
                        </div>
                    </div>
                    
                    <!-- Progress Indicator -->
                    <div id="uploadProgress" class="upload-progress mb-6">
                        <div class="text-center py-6">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                                <div class="animate-spin rounded-full h-8 w-8 border-4 border-green-500 border-t-transparent"></div>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Creating Your Zip Archive</h4>
                            <p class="text-gray-600">Please wait while we compress your files...</p>
                            <div class="mt-4 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full animate-pulse w-3/4"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload Button -->
                    <div class="upload-button">
                        <button type="submit" id="uploadButton" 
                                class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:hover:from-green-500 disabled:hover:to-emerald-600"
                                disabled>
                            <div class="flex items-center justify-center">
                                <i class="fas fa-file-archive text-xl mr-3"></i>
                                <div class="text-left">
                                    <div class="text-lg">Create & Download Zip File</div>
                                    <div id="buttonSubtext" class="text-sm font-normal opacity-90">
                                        Select files to begin
                                    </div>
                                </div>
                                <div id="buttonCount" class="ml-auto bg-white/20 px-3 py-1 rounded-lg text-sm">
                                    0 files
                                </div>
                            </div>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Instructions -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-3 text-blue-500"></i>How It Works
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-5 rounded-xl border border-blue-100">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-bold">1</span>
                            </div>
                            <h4 class="font-semibold text-gray-800">Select Files</h4>
                        </div>
                        <p class="text-gray-600 text-sm">Click the upload area or drag & drop files from your computer</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-blue-100">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-bold">2</span>
                            </div>
                            <h4 class="font-semibold text-gray-800">Review & Upload</h4>
                        </div>
                        <p class="text-gray-600 text-sm">Check your selected files, remove any if needed, then upload</p>
                    </div>
                    <div class="bg-white p-5 rounded-xl border border-blue-100">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-bold">3</span>
                            </div>
                            <h4 class="font-semibold text-gray-800">Download Zip</h4>
                        </div>
                        <p class="text-gray-600 text-sm">Your files will be compressed and downloaded automatically as a zip file</p>
                    </div>
                </div>
                
                <!-- File Requirements -->
                <div class="mt-6 pt-6 border-t border-blue-100">
                    <h4 class="font-semibold text-gray-700 mb-2">File Requirements:</h4>
                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                            <i class="fas fa-check-circle mr-1"></i> Max 10MB per file
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                            <i class="fas fa-check-circle mr-1"></i> All file types supported
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                            <i class="fas fa-check-circle mr-1"></i> Unlimited files per zip
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                            <i class="fas fa-check-circle mr-1"></i> Secure file handling
                        </span>
                    </div>
                </div>
=======

            <div class="mb-6 rounded-xl bg-green-100 border border-green-300 text-green-800 p-4">

                {{ session('success') }}

            </div>

            @endif

            @if(session('error'))

            <div class="mb-6 rounded-xl bg-red-100 border border-red-300 text-red-800 p-4">

                {{ session('error') }}

            </div>

            @endif

            <!-- Dashboard Cards -->

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-white rounded-xl shadow p-6">

                    <div class="flex justify-between">

                        <div>

                            <p class="text-gray-500">

                                Total ZIP Files

                            </p>

                            <h2 class="text-4xl font-bold mt-2 text-green-600">

                                {{ $totalZipFiles }}

                            </h2>

                        </div>

                        <div>

                            <i class="fas fa-file-archive text-4xl text-green-500"></i>

                        </div>

                    </div>

                </div>

                <div class="bg-white rounded-xl shadow p-6">

                    <div class="flex justify-between">

                        <div>

                            <p class="text-gray-500">

                                Today's ZIP

                            </p>

                            <h2 class="text-4xl font-bold mt-2 text-blue-600">

                                {{ $todayZipFiles }}

                            </h2>

                        </div>

                        <div>

                            <i class="fas fa-calendar-day text-4xl text-blue-500"></i>

                        </div>

                    </div>

                </div>

                <div class="bg-white rounded-xl shadow p-6">

                    <div class="flex justify-between">

                        <div>

                            <p class="text-gray-500">

                                Successful

                            </p>

                            <h2 class="text-4xl font-bold mt-2 text-emerald-600">

                                {{ $successfulZipFiles }}

                            </h2>

                        </div>

                        <div>

                            <i class="fas fa-check-circle text-4xl text-emerald-500"></i>

                        </div>

                    </div>

                </div>

                <div class="bg-white rounded-xl shadow p-6">

                    <div class="flex justify-between">

                        <div>

                            <p class="text-gray-500">

                                Failed

                            </p>

                            <h2 class="text-4xl font-bold mt-2 text-red-600">

                                {{ $failedZipFiles }}

                            </h2>

                        </div>

                        <div>

                            <i class="fas fa-times-circle text-4xl text-red-500"></i>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Search -->

            <div class="bg-white rounded-xl shadow p-6 mb-8">

                <form method="GET" action="{{ route('zip.index') }}">

                    <div class="flex gap-3">

                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search ZIP name, files, size, source, status, remarks or date..."
                            class="flex-1 border rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500">

                        <button
                            class="bg-green-600 hover:bg-green-700 text-white px-6 rounded-lg">

                            <i class="fas fa-search mr-2"></i>

                            Search

                        </button>

                        <a
                            href="{{ route('zip.index') }}"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg">

                            Clear

                        </a>

                    </div>

                </form>

>>>>>>> development
            </div>

            <!-- ZIP Actions -->

            <div class="grid lg:grid-cols-3 gap-6 mb-10">

                <!-- Create ZIP From Storage -->

                <div class="bg-white rounded-xl shadow-lg p-6">

                    <h2 class="text-xl font-bold mb-4">
                        <i class="fas fa-folder-open text-blue-600"></i>
                        Create ZIP From Storage
                    </h2>

                    <p class="text-gray-500 mb-5">
                        Download all files from storage/files.
                    </p>

                    <a href="{{ route('zip.create.from.directory') }}"
                        class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-lg">

                        <i class="fas fa-download mr-2"></i>

                        Create ZIP

                    </a>

                </div>



                <!-- Upload Files -->

                <div class="bg-white rounded-xl shadow-lg p-6">

                    <h2 class="text-xl font-bold mb-4">

                        <i class="fas fa-upload text-green-600"></i>

                        Upload Files

                    </h2>

                    <form action="{{ route('zip.create.from.upload') }}"
                        method="POST"
                        enctype="multipart/form-data">

                        @csrf

                        <input
                            type="file"
                            name="uploaded_files[]"
                            multiple
                            class="border rounded-lg p-3 w-full mb-4">

                        <button
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg">

                            <i class="fas fa-file-archive mr-2"></i>

                            Upload & ZIP

                        </button>

                    </form>

                </div>



                <!-- Storage Files -->

<div class="bg-white rounded-xl shadow-lg p-6">

    <h2 class="text-xl font-bold mb-4">

        <i class="fas fa-file text-orange-600"></i>

        Storage Files

    </h2>


    <!-- Selected Preview -->

    <div id="selected-preview"
        class="hidden mb-5 bg-orange-50 border border-orange-200 rounded-xl p-4">


        <h3 class="font-bold text-orange-700 mb-3">

            <i class="fas fa-check-circle"></i>

            Selected Files Preview

        </h3>


        <div class="grid grid-cols-2 gap-3 text-sm">


            <div>

                <span class="text-gray-600">
                    Files:
                </span>

                <span id="selected-count"
                    class="font-bold text-gray-800">
                    0
                </span>

            </div>



            <div>

                <span class="text-gray-600">
                    Size:
                </span>

                <span id="selected-size"
                    class="font-bold text-gray-800">
                    0 KB
                </span>

            </div>


        </div>


    </div>



    <div id="storage-files">

        Loading...

    </div>


</div>

            </div>

            <!-- ==========================================
     ZIP HISTORY
========================================== -->

            <div class="bg-white rounded-2xl shadow-lg mt-10">

                <div class="border-b px-6 py-4 flex justify-between items-center">

                    <div>

                        <h2 class="text-2xl font-bold text-gray-800">

                            <i class="fas fa-history text-green-600 mr-2"></i>

                            ZIP History

                        </h2>

                        <p class="text-gray-500 text-sm mt-1">

                            All generated ZIP archives

                        </p>

                    </div>

                    <span
                        class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-semibold">

                        Total :
                        {{ $histories->total() }}

                    </span>

                </div>

                @if($histories->count())

                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-100">

                            <tr>

                                <th class="px-6 py-4 text-left">

                                    #

                                </th>

                                <th class="px-6 py-4 text-left">

                                    ZIP Name

                                </th>

                                <th class="px-6 py-4 text-center">

                                    Files

                                </th>

                                <th class="px-6 py-4 text-center">

                                    Size

                                </th>

                                <th class="px-6 py-4 text-center">

                                    Source

                                </th>

                                <th class="px-6 py-4 text-center">

                                    Status

                                </th>

                                <th class="px-6 py-4 text-center">

                                    Date

                                </th>

                                <th class="px-6 py-4 text-center">

                                    Action

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($histories as $history)

                            <tr class="border-b hover:bg-gray-50">

                                <td class="px-6 py-4">

                                    {{ $histories->firstItem() + $loop->index }}

                                </td>

                                <td class="px-6 py-4 font-semibold">

                                    {{ $history->zip_name }}

                                </td>

                                <td class="px-6 py-4 text-center">

                                    {{ $history->total_files }}

                                </td>

                                <td class="px-6 py-4 text-center">

                                    {{ $history->zip_size }}

                                </td>

                                <td class="px-6 py-4 text-center">

                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700">

                                        {{ ucfirst(str_replace('_',' ',$history->source)) }}

                                    </span>

                                </td>

                                <td class="px-6 py-4 text-center">

                                    @if($history->status=='success')

                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full">

                                        Success

                                    </span>

                                    @else

                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full">

                                        Failed

                                    </span>

                                    @endif

                                </td>

                                <td class="px-6 py-4 text-center">

                                    {{ $history->created_at->format('d M Y') }}

                                    <br>

                                    <small class="text-gray-500">

                                        {{ $history->created_at->format('h:i A') }}

                                    </small>

                                </td>

                                <td class="px-6 py-4 text-center">

                                    <div class="flex justify-center gap-2">


                                        <!-- Download ZIP -->

                                        <a href="{{ route('zip.history.download',$history->id) }}"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">

                                            <i class="fas fa-download"></i>

                                        </a>



                                        <!-- Delete History -->

                                        <form
                                            action="{{ route('zip.history.destroy',$history->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete this ZIP history?')">


                                            @csrf

                                            @method('DELETE')


                                            <button
                                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">


                                                <i class="fas fa-trash"></i>


                                            </button>


                                        </form>


                                    </div>

                                </td>
                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="px-6 py-4">

                    {{ $histories->links() }}

                </div>

                @else

                <div class="p-10 text-center">

                    <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>

                    <h3 class="text-2xl font-bold text-gray-700">

                        No ZIP History Found

                    </h3>

                    <p class="text-gray-500 mt-2">

                        Generate your first ZIP archive to see history here.

                    </p>

                </div>

                @endif

            </div>

        </div>
    </div>

<<<<<<< HEAD
<<<<<<< HEAD
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get DOM elements
            const fileInput = document.getElementById('uploaded_files');
            const dropArea = document.getElementById('dropArea');
            const selectedFilesContainer = document.getElementById('selectedFilesContainer');
            const selectedFilesList = document.getElementById('selectedFilesList');
            const emptyState = document.getElementById('emptyState');
            const fileCount = document.getElementById('fileCount');
            const totalSizeDiv = document.getElementById('totalSize');
            const totalSizeValue = document.getElementById('totalSizeValue');
            const fileSummary = document.getElementById('fileSummary');
            const uploadButton = document.getElementById('uploadButton');
            const buttonSubtext = document.getElementById('buttonSubtext');
            const buttonCount = document.getElementById('buttonCount');
            const uploadForm = document.getElementById('uploadForm');
            const uploadProgress = document.getElementById('uploadProgress');
            
            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });
            
            // Highlight drop area when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });
            
            // Handle dropped files
            dropArea.addEventListener('drop', handleDrop, false);
            
            // Handle file input change
            fileInput.addEventListener('change', handleFiles);
            
            // Handle click on drop area
            dropArea.addEventListener('click', () => {
                fileInput.click();
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            function highlight(e) {
                dropArea.classList.add('dragover');
            }
            
            function unhighlight(e) {
                dropArea.classList.remove('dragover');
            }
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                handleFiles();
            }
            
            function handleFiles() {
                const files = fileInput.files;
                
                if (files.length === 0) {
                    // No files selected
                    selectedFilesContainer.classList.add('hidden');
                    emptyState.classList.remove('hidden');
                    uploadButton.disabled = true;
                    buttonSubtext.textContent = 'Select files to begin';
                    buttonCount.textContent = '0 files';
                    return;
                }
                
                // Hide empty state
                emptyState.classList.add('hidden');
                
                // Update file count
                const fileCountText = `${files.length} file${files.length > 1 ? 's' : ''}`;
                fileCount.textContent = fileCountText;
                buttonCount.textContent = fileCountText;
                
                // Clear previous list (keep empty state if present)
                if (!emptyState.classList.contains('hidden')) {
                    selectedFilesList.innerHTML = '';
                    selectedFilesList.appendChild(emptyState);
                } else {
                    selectedFilesList.innerHTML = '';
                }
                
                // Variables for validation
                let totalSize = 0;
                let hasInvalidFile = false;
                let fileTypes = new Set();
                
                // Process each file
                Array.from(files).forEach((file, index) => {
                    totalSize += file.size;
                    
                    // Get file type for icon
                    const fileType = getFileType(file);
                    fileTypes.add(fileType.name);
                    
                    // Check file size (10MB limit)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    const isValidSize = file.size <= maxSize;
                    
                    if (!isValidSize) {
                        hasInvalidFile = true;
                    }
                    
                    // Format file size
                    const fileSizeFormatted = formatFileSize(file.size);
                    
                    // Create file item
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item flex items-center justify-between p-4 bg-white rounded-lg border border-gray-100 shadow-sm';
                    
                    fileItem.innerHTML = `
                        <div class="flex items-center flex-1 min-w-0">
                            <div class="file-icon ${fileType.class}">
                                <i class="${fileType.icon}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 truncate" title="${file.name}">${file.name}</p>
                                <div class="flex items-center mt-1">
                                    <span class="text-xs text-gray-500 mr-3">${fileSizeFormatted}</span>
                                    <span class="text-xs px-2 py-1 rounded ${isValidSize ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                        ${isValidSize ? '✓ Ready' : '✗ Too large'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="removeFile(${index})" 
                                class="text-gray-400 hover:text-red-500 p-2 ml-3 rounded-full hover:bg-red-50 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    
                    selectedFilesList.appendChild(fileItem);
                });
                
                // Show selected files container
                selectedFilesContainer.classList.remove('hidden');
                
                // Update total size
                const totalSizeFormatted = formatFileSize(totalSize);
                totalSizeValue.textContent = totalSizeFormatted;
                totalSizeDiv.classList.remove('hidden');
                
                // Update summary
                const typeList = Array.from(fileTypes).join(', ');
                fileSummary.textContent = `${files.length} files • ${totalSizeFormatted} • ${typeList}`;
                
                // Update button state and text
                uploadButton.disabled = files.length === 0 || hasInvalidFile;
                buttonSubtext.textContent = hasInvalidFile ? 
                    'Some files exceed size limit' : 
                    `Ready to compress (${totalSizeFormatted})`;
                
                // Update button style
                if (hasInvalidFile) {
                    uploadButton.classList.remove('from-green-500', 'to-emerald-600');
                    uploadButton.classList.add('from-red-500', 'to-red-600');
                } else {
                    uploadButton.classList.remove('from-red-500', 'to-red-600');
                    uploadButton.classList.add('from-green-500', 'to-emerald-600');
                }
            }
            
            // Helper function to get file type
            function getFileType(file) {
                const name = file.name.toLowerCase();
                const type = file.type;
                
                if (type.includes('pdf')) {
                    return { name: 'PDF', icon: 'fas fa-file-pdf', class: 'pdf' };
                } else if (type.includes('image')) {
                    return { name: 'Image', icon: 'fas fa-file-image', class: 'image' };
                } else if (type.includes('word') || name.endsWith('.doc') || name.endsWith('.docx')) {
                    return { name: 'Document', icon: 'fas fa-file-word', class: 'document' };
                } else if (type.includes('excel') || name.endsWith('.xls') || name.endsWith('.xlsx')) {
                    return { name: 'Spreadsheet', icon: 'fas fa-file-excel', class: 'document' };
                } else if (type.includes('zip') || type.includes('rar') || name.endsWith('.zip') || name.endsWith('.rar') || name.endsWith('.7z')) {
                    return { name: 'Archive', icon: 'fas fa-file-archive', class: 'archive' };
                } else if (type.includes('text') || name.endsWith('.txt') || name.endsWith('.md')) {
                    return { name: 'Text', icon: 'fas fa-file-alt', class: 'document' };
                } else {
                    return { name: 'File', icon: 'fas fa-file', class: 'other' };
                }
            }
            
            // Helper function to format file size
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            // Handle form submission
            uploadForm.addEventListener('submit', function(e) {
                const files = fileInput.files;
                
                if (files.length === 0) {
                    e.preventDefault();
                    showAlert('Please select at least one file to upload.', 'error');
                    return;
                }
                
                // Validate file sizes
                const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                let isValid = true;
                let largeFiles = [];
                
                Array.from(files).forEach(file => {
                    if (file.size > maxSize) {
                        isValid = false;
                        largeFiles.push({
                            name: file.name,
                            size: formatFileSize(file.size)
                        });
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    let errorMessage = '<div class="text-left"><p class="font-semibold mb-2">Some files exceed the 10MB limit:</p><ul class="list-disc pl-5 space-y-1">';
                    largeFiles.forEach(file => {
                        errorMessage += `<li>${file.name} (${file.size})</li>`;
                    });
                    errorMessage += '</ul><p class="mt-2">Please remove or reduce the size of these files.</p></div>';
                    
                    showAlert(errorMessage, 'error', true);
                    return;
                }
                
                // Show progress indicator
                uploadForm.classList.add('uploading');
            });
            
            // Alert function
            function showAlert(message, type = 'info', isHtml = false) {
                // Remove existing alert
                const existingAlert = document.querySelector('.custom-alert');
                if (existingAlert) {
                    existingAlert.remove();
                }
                
                // Create alert
                const alert = document.createElement('div');
                alert.className = `custom-alert fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg border max-w-md ${
                    type === 'error' ? 'bg-red-50 border-red-200 text-red-800' : 
                    'bg-blue-50 border-blue-200 text-blue-800'
                }`;
                
                alert.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'info-circle'} mr-3 mt-0.5 text-lg"></i>
                        <div class="flex-1">
                            ${isHtml ? message : `<p>${message}</p>`}
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                document.body.appendChild(alert);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.remove();
                    }
                }, 5000);
            }
            
            // Global functions
            window.removeFile = function(index) {
                const dt = new DataTransfer();
                const files = fileInput.files;
                
                // Add all files except the one to remove
                Array.from(files).forEach((file, i) => {
                    if (i !== index) {
                        dt.items.add(file);
                    }
                });
                
                // Update file input
                fileInput.files = dt.files;
                
                // Update display
                handleFiles();
                
                // Show feedback
                showAlert('File removed from selection', 'info');
            };
            
            window.clearAllFiles = function() {
                fileInput.value = '';
                handleFiles();
                showAlert('All files cleared', 'info');
            };
        });
    </script>
=======
>>>>>>> development
=======
<script>

fetch('/zip/list-files')

.then(response => response.json())

.then(data => {


    let html = '';



    if(data.files.length == 0){


        html = `

            <p class="text-gray-500">

                No files found.

            </p>

        `;


    }else{


        html += `

        <form action="/zip/create-from-files"
              method="POST"
              id="zip-form">


        @csrf


        <div class="max-h-72 overflow-y-auto">


        `;



        data.files.forEach(function(file){


            html += `


            <label class="flex items-center justify-between mb-3 
                    bg-gray-50 p-3 rounded-lg hover:bg-orange-50">


                <div class="flex items-center">


                    <input type="checkbox"

                        name="selected_files[]"

                        value="${file.path}"

                        data-size="${file.size}"

                        class="file-checkbox mr-3">


                    <span>

                        ${file.name}

                    </span>


                </div>



                <span class="text-xs text-gray-500">

                    ${file.size}

                </span>



            </label>


            `;


        });



        html += `


        </div>



        <button

            class="mt-4 w-full bg-orange-600 
            hover:bg-orange-700 text-white 
            px-4 py-3 rounded-lg">


            <i class="fas fa-file-archive mr-2"></i>

            Create ZIP


        </button>


        </form>


        `;


    }



    document.getElementById('storage-files').innerHTML = html;




    /*
    |--------------------------------------------------------------------------
    | Selected Files Preview
    |--------------------------------------------------------------------------
    */


    const checkboxes = document.querySelectorAll('.file-checkbox');


    const preview = document.getElementById('selected-preview');

    const countBox = document.getElementById('selected-count');

    const sizeBox = document.getElementById('selected-size');



    checkboxes.forEach(function(checkbox){


        checkbox.addEventListener('change', function(){



            let selectedCount = 0;

            let totalSize = 0;



            checkboxes.forEach(function(item){


                if(item.checked){


                    selectedCount++;


                    let sizeText = item.dataset.size;


                    totalSize += convertSizeToKB(sizeText);


                }


            });



            if(selectedCount > 0){


                preview.classList.remove('hidden');


            }else{


                preview.classList.add('hidden');


            }



            countBox.innerHTML = selectedCount;


            sizeBox.innerHTML = formatSize(totalSize);



        });



    });



});





/*
|--------------------------------------------------------------------------
| Convert File Size To KB
|--------------------------------------------------------------------------
*/

function convertSizeToKB(size){


    let value = parseFloat(size);



    if(size.includes('MB')){

        return value * 1024;

    }


    return value;


}





/*
|--------------------------------------------------------------------------
| Format Size
|--------------------------------------------------------------------------
*/

function formatSize(kb){


    if(kb >= 1024){


        return (kb / 1024).toFixed(2) + ' MB';


    }


    return kb.toFixed(2) + ' KB';


}


</script>
>>>>>>> development
</body>

</html>