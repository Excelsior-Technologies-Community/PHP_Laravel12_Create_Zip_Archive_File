<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced ZIP Archive Manager &amp; Analytics - Laravel 12</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; width: 100%; }
        .file-input-wrapper input[type=file] { position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; height: 100%; width: 100%; }
        .file-input-label { display: flex; align-items: center; justify-content: center; padding: 1.5rem; background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 0.75rem; transition: all 0.3s; cursor: pointer; min-height: 130px; }
        .file-input-label:hover { border-color: #0284c7; background-color: #f0f9ff; }
        .file-input-label.dragover { border-color: #0284c7; background-color: #e0f2fe; border-style: solid; }
        .modal-backdrop { background-color: rgba(15, 23, 42, 0.6); }
    </style>
</head>

<body class="bg-slate-100 text-slate-800 min-h-screen">

    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <!-- Top Header -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-8 pb-6 border-b border-slate-200 gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-tr from-sky-600 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-file-zipper text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">ZIP Archive Manager</h1>
                    <p class="text-sm text-slate-500">AES-256 Encryption &bull; Online Extractor &bull; Smart Compression &bull; Storage Insights</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    Engine: ZipArchive + AES-256
                </span>
            </div>
        </div>

        <!-- Global Flash Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-xl text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-xl text-rose-600"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Quick Stats Cards Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold text-slate-400">Total Archives</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalZipFiles }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Today: <strong class="text-sky-600">{{ $todayZipFiles }} created</strong></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold text-slate-400">Disk Space Saved</p>
                    <h3 class="text-3xl font-black text-emerald-600 mt-1">{{ $totalSavedFormatted }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Average Savings: <strong class="text-emerald-600">{{ $analytics['savings']['savings_percent'] }}%</strong></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-hard-drive"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold text-slate-400">Encrypted Archives</p>
                    <h3 class="text-3xl font-black text-indigo-600 mt-1">{{ $encryptedZipFiles }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Secured via <strong class="text-indigo-600">AES-256</strong></p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-lock"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold text-slate-400">Success Rate</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1">
                        {{ $totalZipFiles > 0 ? round(($successfulZipFiles / $totalZipFiles) * 100, 1) : 100 }}%
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Success: {{ $successfulZipFiles }} | Failed: {{ $failedZipFiles }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>
        </div>

        <!-- Analytics Charts Row (Chart.js) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- 30-Day Activity & Size Growth Chart -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg">
                            <i class="fa-solid fa-chart-line text-sky-600 mr-2"></i> 30-Day Activity &amp; Compressed Size Trend
                        </h4>
                        <p class="text-xs text-slate-400">Daily volume of archive archives and storage footprint (KB)</p>
                    </div>
                    <span class="text-xs bg-slate-100 text-slate-600 px-3 py-1 rounded-full font-medium">Daily Metrics</span>
                </div>
                <div class="h-64">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>

            <!-- Source Distribution Donut Chart & Savings Summary -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <h4 class="font-bold text-slate-800 text-lg mb-1">
                        <i class="fa-solid fa-chart-pie text-emerald-600 mr-2"></i> Archive Sources
                    </h4>
                    <p class="text-xs text-slate-400 mb-4">Distribution by source input channel</p>
                    <div class="h-44">
                        <canvas id="sourceChart"></canvas>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Original Input Data:</span>
                        <strong class="text-slate-800">{{ $analytics['savings']['total_original_formatted'] }}</strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Compressed Archive Size:</span>
                        <strong class="text-sky-600">{{ $analytics['savings']['total_compressed_formatted'] }}</strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Net Disk Space Saved:</span>
                        <strong class="text-emerald-600">{{ $analytics['savings']['total_saved_formatted'] }} ({{ $analytics['savings']['savings_percent'] }}%)</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations Hub: Tabs for Create ZIP, Online Extractor, and Storage Files -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex space-x-2">
                    <button type="button" onclick="switchTab('tab-upload')" id="btn-tab-upload"
                            class="px-4 py-2 rounded-xl text-sm font-bold bg-white text-sky-600 shadow-sm border border-slate-200 flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload &amp; Create Encrypted ZIP
                    </button>
                    <button type="button" onclick="switchTab('tab-extract')" id="btn-tab-extract"
                            class="px-4 py-2 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-box-open text-emerald-600"></i> Online ZIP Extractor (Unzip)
                    </button>
                    <button type="button" onclick="switchTab('tab-storage')" id="btn-tab-storage"
                            class="px-4 py-2 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-folder-tree text-indigo-600"></i> Select Storage Files
                    </button>
                </div>

                <a href="{{ route('zip.create.from.directory') }}"
                   onclick="return confirm('Create a ZIP archive of all files inside storage/app/public/files?')"
                   class="inline-flex items-center px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-900 shadow-sm transition">
                    <i class="fa-solid fa-folder-zipper mr-2"></i> Zip Entire Storage Directory
                </a>
            </div>

            <!-- Tab 1: Upload Files & Create ZIP -->
            <div id="tab-upload" class="p-6">
                <form action="{{ route('zip.create.from.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <!-- Left 2 Cols: Drag & Drop Files -->
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Select Files to Archive</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="uploaded_files[]" id="fileInput" multiple required onchange="handleFileSelect(this)">
                                <label for="fileInput" class="file-input-label text-center flex flex-col justify-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-sky-500 mb-2"></i>
                                    <span class="font-semibold text-slate-700">Click to browse or drag &amp; drop files here</span>
                                    <span class="text-xs text-slate-400 mt-1">Supports any documents, images, and archives (Max 20MB per file)</span>
                                </label>
                            </div>
                            <div id="selectedFileList" class="mt-3 space-y-1 max-h-36 overflow-y-auto text-xs text-slate-600"></div>
                        </div>

                        <!-- Right Col: Advanced Compression & Encryption Options -->
                        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
                            <h5 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <i class="fa-solid fa-sliders text-sky-600"></i> Security &amp; Compression
                            </h5>

                            <!-- Password Field -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">
                                    <i class="fa-solid fa-lock text-indigo-500 mr-1"></i> Optional Password (AES-256)
                                </label>
                                <div class="relative">
                                    <input type="password" name="password" id="zipPassword" placeholder="Leave blank for open archive"
                                           class="w-full text-sm px-3 py-2 pr-10 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                    <button type="button" onclick="togglePasswordVisibility('zipPassword', this)"
                                            class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                <span class="text-[11px] text-slate-400">When set, files are encrypted with AES-256 bit security.</span>
                            </div>

                            <!-- Compression Level -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">
                                    <i class="fa-solid fa-compress text-sky-500 mr-1"></i> Compression Level
                                </label>
                                <select name="compression_level" class="w-full text-sm px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                    <option value="normal" selected>Normal (Standard Deflate - Balanced)</option>
                                    <option value="maximum">Maximum / Ultra (Smallest File Size)</option>
                                    <option value="store">Store (Fastest - No compression)</option>
                                </select>
                            </div>

                            <!-- Exclude Filters -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">
                                    <i class="fa-solid fa-filter text-amber-500 mr-1"></i> Exclude Extensions (Comma-separated)
                                </label>
                                <input type="text" name="exclude_ext" placeholder="log, tmp, git, ds_store"
                                       class="w-full text-sm px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <span class="text-[11px] text-slate-400">Files with these extensions will be omitted.</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2">
                            <i class="fa-solid fa-file-zipper"></i> Create &amp; Download ZIP Archive
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab 2: Online ZIP Extractor -->
            <div id="tab-extract" class="p-6 hidden">
                <form action="{{ route('zip.extract') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="max-w-2xl mx-auto space-y-5">
                        <div class="text-center mb-4">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-2 text-xl">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <h4 class="text-lg font-bold text-slate-800">1-Click Online ZIP Extractor</h4>
                            <p class="text-xs text-slate-500">Upload any ZIP archive to decompress and extract files directly onto the server.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Select ZIP Archive (.zip)</label>
                            <input type="file" name="zip_file" accept=".zip" required
                                   class="w-full text-sm border border-slate-300 rounded-xl px-4 py-3 bg-slate-50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Decryption Password (If archive is password-protected)</label>
                            <input type="password" name="password" placeholder="Enter password or leave blank if unprotected"
                                   class="w-full text-sm px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <div class="text-center pt-2">
                            <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition inline-flex items-center gap-2">
                                <i class="fa-solid fa-box-open"></i> Extract Archive on Server
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 3: Select from Storage Files -->
            <div id="tab-storage" class="p-6 hidden">
                <form action="{{ route('zip.create.from.files') }}" method="POST">
                    @csrf
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h5 class="text-sm font-bold text-slate-800">Storage Files in storage/app/public/files</h5>
                            <p class="text-xs text-slate-400">Select which files to bundle into a compressed archive</p>
                        </div>
                        <button type="button" onclick="loadStorageFiles()" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center gap-1">
                            <i class="fa-solid fa-arrows-rotate"></i> Refresh List
                        </button>
                    </div>

                    <div id="storageFilesContainer" class="border border-slate-200 rounded-xl p-4 bg-slate-50 max-h-56 overflow-y-auto space-y-2 mb-4">
                        <p class="text-xs text-slate-400 text-center py-4">Click "Refresh List" to scan files.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Password (Optional)</label>
                            <input type="password" name="password" placeholder="Leave blank for no password"
                                   class="w-full text-sm px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Compression Level</label>
                            <select name="compression_level" class="w-full text-sm px-3 py-2 border border-slate-300 rounded-lg">
                                <option value="normal">Normal (Balanced)</option>
                                <option value="maximum">Maximum (Ultra)</option>
                                <option value="store">Store (Fast)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow transition flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-file-zipper"></i> Zip Selected Files
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History & Explorer Table Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-slate-800">Archive History &amp; Inspection</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">{{ $histories->total() }}</span>
                </div>

                <!-- Search & Filters -->
                <form action="{{ route('zip.index') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <select name="encrypted" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-2.5 py-2 text-slate-600 bg-white">
                        <option value="">Security: All</option>
                        <option value="1" {{ request('encrypted') === '1' ? 'selected' : '' }}>🔒 Encrypted Only</option>
                        <option value="0" {{ request('encrypted') === '0' ? 'selected' : '' }}>Open Archives</option>
                    </select>

                    <select name="source" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-2.5 py-2 text-slate-600 bg-white">
                        <option value="">Source: All</option>
                        <option value="upload" {{ request('source') === 'upload' ? 'selected' : '' }}>Uploads</option>
                        <option value="directory" {{ request('source') === 'directory' ? 'selected' : '' }}>Directory</option>
                        <option value="selected_files" {{ request('source') === 'selected_files' ? 'selected' : '' }}>Selected Files</option>
                    </select>

                    <div class="relative flex-grow md:flex-grow-0">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search archive name..."
                               class="text-xs border border-slate-300 rounded-lg pl-8 pr-3 py-2 w-full md:w-52 focus:outline-none focus:ring-1 focus:ring-sky-500">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-400 text-xs"></i>
                    </div>

                    @if($search || request('encrypted') !== null || request('source'))
                        <a href="{{ route('zip.index') }}" class="text-xs text-slate-500 hover:text-slate-800 px-2 py-2">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <th class="py-3.5 px-4">ID</th>
                            <th class="py-3.5 px-4">Archive Details</th>
                            <th class="py-3.5 px-4">Compressed / Original</th>
                            <th class="py-3.5 px-4">Space Savings</th>
                            <th class="py-3.5 px-4">Security</th>
                            <th class="py-3.5 px-4">Source</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Created</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($histories as $history)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-400">#{{ $history->id }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-zipper text-sky-500"></i>
                                    <span>{{ $history->zip_name }}</span>
                                </div>
                                @if($history->checksum_sha256)
                                    <span class="text-[10px] text-slate-400 font-mono block truncate max-w-xs" title="SHA-256: {{ $history->checksum_sha256 }}">
                                        SHA: {{ substr($history->checksum_sha256, 0, 16) }}...
                                    </span>
                                @endif
                                <span class="text-[11px] text-slate-500">{{ $history->total_files }} files bundled</span>
                            </td>
                            <td class="py-3 px-4">
                                <strong class="text-slate-800 font-semibold">{{ $history->zip_size }}</strong>
                                @if($history->original_size_bytes > 0)
                                    <span class="text-slate-400 block text-[11px]">from {{ $history->formatted_original_size }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($history->savings_percent > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 text-[11px]">
                                        <i class="fa-solid fa-arrow-down mr-1"></i> Saved {{ $history->savings_percent }}%
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Stored</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($history->is_encrypted)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-indigo-100 text-indigo-800 text-[11px]">
                                        <i class="fa-solid fa-lock text-[10px] mr-1"></i> AES-256
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600 text-[11px]">
                                        <i class="fa-solid fa-lock-open text-[10px] mr-1 text-slate-400"></i> Open
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="capitalize px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[11px]">
                                    {{ str_replace('_', ' ', $history->source) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($history->status === 'success')
                                    <span class="text-emerald-600 font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Success
                                    </span>
                                @else
                                    <span class="text-rose-600 font-semibold flex items-center gap-1" title="{{ $history->remarks }}">
                                        <i class="fa-solid fa-circle-xmark text-[10px]"></i> Failed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $history->created_at->format('M d, Y h:i A') }}</td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <!-- Download -->
                                    <a href="{{ route('zip.history.download', $history->id) }}"
                                       class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 transition"
                                       title="Download ZIP Archive">
                                        <i class="fa-solid fa-download text-sm"></i>
                                    </a>

                                    <!-- Inspect Modal Button -->
                                    <button type="button" onclick="inspectArchive('{{ $history->id }}')"
                                            class="p-1.5 rounded-lg text-sky-600 hover:bg-sky-50 hover:text-sky-700 transition"
                                            title="Inspect &amp; Explore In-Archive Files">
                                        <i class="fa-solid fa-eye text-sm"></i>
                                    </button>

                                    <!-- Extract Button -->
                                    <button type="button" onclick="openExtractModal('{{ $history->id }}', '{{ $history->zip_name }}', {{ $history->is_encrypted ? 'true' : 'false' }})"
                                            class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 hover:text-amber-700 transition"
                                            title="Extract Archive on Server">
                                        <i class="fa-solid fa-box-open text-sm"></i>
                                    </button>

                                    <!-- Delete -->
                                    <form action="{{ route('zip.history.destroy', $history->id) }}" method="POST" onsubmit="return confirm('Delete this ZIP file and history entry?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition" title="Delete Archive">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2 block text-slate-300"></i>
                                No archive files found matching your search.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200">
                {{ $histories->links() }}
            </div>
        </div>

    </div>

    <!-- Modal 1: In-Archive File Explorer Modal -->
    <div id="inspectModal" class="fixed inset-0 z-50 hidden overflow-y-auto modal-backdrop flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                        <i class="fa-solid fa-folder-tree"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-base" id="modalArchiveName">Archive Explorer</h4>
                        <p class="text-xs text-slate-500" id="modalArchiveMeta">Inspecting compressed content without extracting</p>
                    </div>
                </div>
                <button type="button" onclick="closeInspectModal()" class="text-slate-400 hover:text-slate-700 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Content Table -->
            <div class="p-6 overflow-y-auto flex-grow space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                    <div>
                        <span class="text-slate-400 block">Total Files:</span>
                        <strong id="modalTotalFiles" class="text-slate-800 font-bold">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Uncompressed:</span>
                        <strong id="modalUncompressed" class="text-slate-800 font-bold">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Compressed:</span>
                        <strong id="modalCompressed" class="text-sky-600 font-bold">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Savings Ratio:</span>
                        <strong id="modalSavings" class="text-emerald-600 font-bold">-</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-600 uppercase font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Entry Name</th>
                                <th class="py-2.5 px-3">Size</th>
                                <th class="py-2.5 px-3">Packed</th>
                                <th class="py-2.5 px-3">Ratio</th>
                                <th class="py-2.5 px-3">CRC</th>
                                <th class="py-2.5 px-3 text-right">Download</th>
                            </tr>
                        </thead>
                        <tbody id="modalFilesBody" class="divide-y divide-slate-100 font-mono">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button type="button" onclick="closeInspectModal()" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition">
                    Close Explorer
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Server Extract Modal (with password input if encrypted) -->
    <div id="extractModal" class="fixed inset-0 z-50 hidden overflow-y-auto modal-backdrop flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-amber-50">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-amber-600 text-lg"></i>
                    <h4 class="font-bold text-slate-800 text-sm">Extract Archive on Server</h4>
                </div>
                <button type="button" onclick="closeExtractModal()" class="text-slate-400 hover:text-slate-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="extractHistoryForm" method="POST" action="">
                @csrf
                <div class="p-6 space-y-4 text-xs">
                    <p class="text-slate-600">
                        Decompress <strong id="extractModalArchiveName" class="text-slate-900 font-semibold font-mono">archive.zip</strong> into a dedicated folder inside <code class="bg-slate-100 px-1 py-0.5 rounded text-sky-700">storage/app/extracted/</code>.
                    </p>

                    <div id="extractPasswordWrapper">
                        <label class="block font-semibold text-slate-700 mb-1">
                            <i class="fa-solid fa-lock text-indigo-500 mr-1"></i> Decryption Password
                        </label>
                        <input type="password" name="password" placeholder="Enter archive password"
                               class="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">This archive was encrypted with AES-256. Provide password to extract.</span>
                    </div>
                </div>

                <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex justify-end gap-2">
                    <button type="button" onclick="closeExtractModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition">
                        Confirm &amp; Extract
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // --- Tab Switcher ---
        function switchTab(tabId) {
            ['tab-upload', 'tab-extract', 'tab-storage'].forEach(id => {
                document.getElementById(id).classList.add('hidden');
                const btn = document.getElementById('btn-' + id);
                btn.className = 'px-4 py-2 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-2';
            });
            document.getElementById(tabId).classList.remove('hidden');
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.className = 'px-4 py-2 rounded-xl text-sm font-bold bg-white text-sky-600 shadow-sm border border-slate-200 flex items-center gap-2';
        }

        // --- File Select Display ---
        function handleFileSelect(input) {
            const list = document.getElementById('selectedFileList');
            list.innerHTML = '';
            if (input.files && input.files.length > 0) {
                Array.from(input.files).forEach((file, index) => {
                    const sizeKB = (file.size / 1024).toFixed(1) + ' KB';
                    const item = document.createElement('div');
                    item.className = 'flex items-center justify-between p-1.5 bg-slate-50 rounded border border-slate-100';
                    item.innerHTML = `<span><i class="fa-regular fa-file text-slate-400 mr-1.5"></i> ${file.name}</span><span class="font-mono text-slate-400">${sizeKB}</span>`;
                    list.appendChild(item);
                });
            }
        }

        // --- Password Eye Toggle ---
        function togglePasswordVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }

        // --- Load Storage Files Dynamically ---
        function loadStorageFiles() {
            const container = document.getElementById('storageFilesContainer');
            container.innerHTML = '<p class="text-xs text-slate-400 text-center py-4"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Scanning files...</p>';

            fetch("{{ route('zip.list.files') }}")
                .then(res => res.json())
                .then(data => {
                    container.innerHTML = '';
                    if (data.success && data.files.length > 0) {
                        data.files.forEach(f => {
                            const row = document.createElement('label');
                            row.className = 'flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200 cursor-pointer hover:bg-slate-50 text-xs';
                            row.innerHTML = `
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="selected_files[]" value="${f.path}" class="rounded text-indigo-600">
                                    <span class="font-medium text-slate-700">${f.name}</span>
                                </div>
                                <span class="font-mono text-slate-400">${f.size}</span>
                            `;
                            container.appendChild(row);
                        });
                    } else {
                        container.innerHTML = '<p class="text-xs text-slate-400 text-center py-4">No files found inside storage/app/public/files.</p>';
                    }
                })
                .catch(() => {
                    container.innerHTML = '<p class="text-xs text-rose-500 text-center py-4">Failed to load files.</p>';
                });
        }

        // --- In-Archive File Explorer Modal ---
        function inspectArchive(id) {
            const modal = document.getElementById('inspectModal');
            modal.classList.remove('hidden');

            document.getElementById('modalArchiveName').innerText = 'Loading archive index...';
            document.getElementById('modalFilesBody').innerHTML = '<tr><td colspan="6" class="text-center py-6 text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Reading ZIP internal headers...</td></tr>';

            fetch('/zip/inspect/' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('modalArchiveName').innerText = data.archive_name;
                        document.getElementById('modalArchiveMeta').innerText = data.is_encrypted ? 'Encrypted with AES-256' : 'Standard Open Archive';
                        document.getElementById('modalTotalFiles').innerText = data.total_files;
                        document.getElementById('modalUncompressed').innerText = data.total_uncompressed_formatted;
                        document.getElementById('modalCompressed').innerText = data.total_compressed_formatted;
                        document.getElementById('modalSavings').innerText = data.overall_savings_percent + '% Saved';

                        const tbody = document.getElementById('modalFilesBody');
                        tbody.innerHTML = '';

                        data.files.forEach(f => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50 transition';
                            const lockIcon = f.is_encrypted ? '<i class="fa-solid fa-lock text-indigo-500 text-[10px] mr-1"></i>' : '';
                            const downloadBtn = !f.is_dir ? `
                                <a href="/zip/download-file/${id}?file=${encodeURIComponent(f.name)}"
                                   class="px-2 py-1 bg-sky-50 text-sky-600 rounded hover:bg-sky-100 font-sans font-semibold text-[11px] inline-flex items-center gap-1">
                                    <i class="fa-solid fa-download"></i> Extract File
                                </a>
                            ` : '<span class="text-slate-400 font-sans text-[10px]">Folder</span>';

                            tr.innerHTML = `
                                <td class="py-2 px-3 font-medium text-slate-800">
                                    ${lockIcon}${f.name}
                                </td>
                                <td class="py-2 px-3 text-slate-600">${f.size_formatted}</td>
                                <td class="py-2 px-3 text-slate-500">${f.compressed_formatted}</td>
                                <td class="py-2 px-3 text-emerald-600">${f.ratio}%</td>
                                <td class="py-2 px-3 text-slate-400">${f.crc}</td>
                                <td class="py-2 px-3 text-right">${downloadBtn}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        document.getElementById('modalFilesBody').innerHTML = `<tr><td colspan="6" class="text-center py-6 text-rose-500">${data.message}</td></tr>`;
                    }
                })
                .catch(() => {
                    document.getElementById('modalFilesBody').innerHTML = '<tr><td colspan="6" class="text-center py-6 text-rose-500">Failed to inspect archive.</td></tr>';
                });
        }

        function closeInspectModal() {
            document.getElementById('inspectModal').classList.add('hidden');
        }

        // --- Extract Modal ---
        function openExtractModal(id, name, isEncrypted) {
            document.getElementById('extractModalArchiveName').innerText = name;
            document.getElementById('extractHistoryForm').action = '/zip/history/extract/' + id;
            const passWrapper = document.getElementById('extractPasswordWrapper');
            if (isEncrypted) {
                passWrapper.classList.remove('hidden');
            } else {
                passWrapper.classList.add('hidden');
            }
            document.getElementById('extractModal').classList.remove('hidden');
        }

        function closeExtractModal() {
            document.getElementById('extractModal').classList.add('hidden');
        }

        // --- Chart.js: 30-Day Growth & Source Distribution ---
        document.addEventListener('DOMContentLoaded', function () {
            fetch("{{ route('zip.analytics') }}")
                .then(res => res.json())
                .then(data => {
                    // Line Chart
                    const ctxGrowth = document.getElementById('growthChart').getContext('2d');
                    new Chart(ctxGrowth, {
                        type: 'line',
                        data: {
                            labels: data.chart.labels,
                            datasets: [
                                {
                                    label: 'Compressed Size (KB)',
                                    data: data.chart.sizes_kb,
                                    borderColor: '#0284c7',
                                    backgroundColor: 'rgba(2, 132, 199, 0.1)',
                                    fill: true,
                                    tension: 0.3,
                                    yAxisID: 'y',
                                },
                                {
                                    label: 'Archives Created',
                                    data: data.chart.counts,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    borderDash: [4, 4],
                                    tension: 0.2,
                                    yAxisID: 'y1',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: { display: true, text: 'Size (KB)' },
                                    beginAtZero: true
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: { display: true, text: 'Count' },
                                    grid: { drawOnChartArea: false },
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });

                    // Donut Chart
                    const ctxSource = document.getElementById('sourceChart').getContext('2d');
                    new Chart(ctxSource, {
                        type: 'doughnut',
                        data: {
                            labels: data.sources.labels,
                            datasets: [{
                                data: data.sources.data,
                                backgroundColor: ['#0284c7', '#059669', '#f59e0b', '#64748b'],
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                            }
                        }
                    });
                })
                .catch(err => {
                    console.warn('Analytics loading error:', err);
                });
        });
    </script>
</body>
</html>