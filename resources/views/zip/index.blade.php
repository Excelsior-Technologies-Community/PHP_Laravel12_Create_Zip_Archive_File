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
            padding: 1rem;
            background-color: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.3s;
            cursor: pointer;
            min-height: 120px;
        }
        
        .file-input-label:hover {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        
        .file-input-label.dragover {
            border-color: #10b981;
            background-color: #dcfce7;
            border-style: solid;
        }
        
        .file-item {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Laravel Zip Archive Creator</h1>
            <p class="text-gray-600 mb-8">Create zip files from existing files, uploads, or directories</p>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Method 1: From Existing Files -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        <i class="fas fa-file-archive mr-2 text-blue-500"></i>Create Zip from Existing Files
                    </h2>
                    
                    <div id="fileList" class="mb-4 p-4 border border-gray-200 rounded max-h-60 overflow-y-auto">
                        <p class="text-gray-500">Loading files...</p>
                    </div>

                    <form id="zipForm" action="{{ route('zip.create.from.files') }}" method="POST">
                        @csrf
                        <input type="hidden" name="selected_files" id="selectedFiles">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-compress mr-2"></i>Create Zip from Selected Files
                        </button>
                    </form>
                </div>

                <!-- Method 2: Upload Files - FIXED VERSION -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        <i class="fas fa-upload mr-2 text-green-500"></i>Upload Files & Create Zip
                    </h2>
                    
                    <form id="uploadForm" action="{{ route('zip.create.from.upload') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Select Files to Upload
                            </label>
                            
                            <!-- Custom File Input Area -->
                            <div class="file-input-wrapper mb-3">
                                <div class="file-input-label" id="dropArea">
                                    <div class="text-center">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                        <p class="text-gray-700 font-medium">Click to select files</p>
                                        <p class="text-gray-500 text-sm mt-1">or drag and drop here</p>
                                        <p class="text-xs text-gray-400 mt-2">Maximum 10MB per file</p>
                                    </div>
                                </div>
                                <input type="file" name="uploaded_files[]" id="uploaded_files" multiple 
                                       class="w-full">
                            </div>
                            
                            <!-- Selected Files Display -->
                            <div id="selectedFilesContainer" class="hidden">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Selected Files:</span>
                                    <span id="fileCount" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">0 files</span>
                                </div>
                                <div id="selectedFilesList" class="max-h-40 overflow-y-auto border border-gray-200 rounded-md p-2 space-y-2">
                                    <!-- Files will be listed here -->
                                </div>
                            </div>
                            
                            <!-- Progress Indicator -->
                            <div id="uploadProgress" class="upload-progress mt-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-600"></div>
                                    <span class="text-sm text-green-600">Creating zip archive...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="upload-button">
                            <button type="submit" id="uploadButton" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <i class="fas fa-file-upload mr-2"></i>
                                <span>Upload & Create Zip</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Method 3: From Directory -->
                <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        <i class="fas fa-folder mr-2 text-purple-500"></i>Create Zip from Directory
                    </h2>
                    <p class="text-gray-600 mb-4">Create a zip archive containing all files from the storage directory.</p>
                    
                    <form action="{{ route('zip.create.from.directory') }}" method="GET">
                        <button type="submit" class="w-full md:w-auto bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-6 rounded">
                            <i class="fas fa-folder-open mr-2"></i>Zip Entire Directory
                        </button>
                    </form>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-3">How to Use:</h3>
                <ul class="list-disc list-inside text-blue-700 space-y-2">
                    <li>Select files from the list and create a zip archive</li>
                    <li>Upload new files and compress them immediately</li>
                    <li>Compress all files from a specific directory</li>
                    <li>Download the generated zip file automatically</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load file list for Method 1
            fetch('{{ route("zip.list.files") }}')
                .then(response => response.json())
                .then(files => {
                    const fileListContainer = document.getElementById('fileList');
                    const selectedFilesInput = document.getElementById('selectedFiles');
                    let selectedFiles = [];
                    
                    if (files.length === 0) {
                        fileListContainer.innerHTML = '<p class="text-gray-500">No files found in storage. Upload some files first.</p>';
                        return;
                    }
                    
                    let fileListHTML = '<div class="space-y-2">';
                    
                    files.forEach(file => {
                        const fileSize = (file.size / 1024).toFixed(2);
                        const modifiedDate = new Date(file.modified * 1000).toLocaleString();
                        
                        fileListHTML += `
                            <div class="flex items-center justify-between p-2 border border-gray-100 hover:bg-gray-50 rounded">
                                <div class="flex items-center">
                                    <input type="checkbox" id="file_${file.name}" value="${file.path}" 
                                           class="file-checkbox mr-3 h-4 w-4 text-blue-600 rounded">
                                    <label for="file_${file.name}" class="cursor-pointer">
                                        <i class="fas fa-file text-gray-400 mr-2"></i>
                                        <span class="font-medium">${file.name}</span>
                                        <span class="text-xs text-gray-500 ml-2">(${fileSize} KB)</span>
                                    </label>
                                </div>
                                <span class="text-xs text-gray-400">${modifiedDate}</span>
                            </div>
                        `;
                    });
                    
                    fileListHTML += '</div>';
                    fileListContainer.innerHTML = fileListHTML;
                    
                    // Add checkbox event listeners
                    document.querySelectorAll('.file-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                selectedFiles.push(this.value);
                            } else {
                                selectedFiles = selectedFiles.filter(file => file !== this.value);
                            }
                            selectedFilesInput.value = JSON.stringify(selectedFiles);
                        });
                    });
                })
                .catch(error => {
                    console.error('Error loading files:', error);
                    document.getElementById('fileList').innerHTML = 
                        '<p class="text-red-500">Error loading files. Make sure you have files in storage/app/public/files</p>';
                });

            // Form submission for Method 1
            document.getElementById('zipForm').addEventListener('submit', function(e) {
                const selectedFiles = JSON.parse(document.getElementById('selectedFiles').value || '[]');
                if (selectedFiles.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one file.');
                }
            });

            // ============================================
            // FIXED UPLOAD FUNCTIONALITY FOR METHOD 2
            // ============================================
            
            const fileInput = document.getElementById('uploaded_files');
            const dropArea = document.getElementById('dropArea');
            const selectedFilesContainer = document.getElementById('selectedFilesContainer');
            const selectedFilesList = document.getElementById('selectedFilesList');
            const fileCount = document.getElementById('fileCount');
            const uploadButton = document.getElementById('uploadButton');
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
                    selectedFilesContainer.classList.add('hidden');
                    uploadButton.disabled = true;
                    return;
                }
                
                // Update file count
                fileCount.textContent = `${files.length} file${files.length > 1 ? 's' : ''}`;
                
                // Clear previous list
                selectedFilesList.innerHTML = '';
                
                // Validate files and create list items
                let totalSize = 0;
                let hasInvalidFile = false;
                
                Array.from(files).forEach((file, index) => {
                    totalSize += file.size;
                    
                    // Check file size (10MB limit)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    const isValidSize = file.size <= maxSize;
                    
                    if (!isValidSize) {
                        hasInvalidFile = true;
                    }
                    
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-100';
                    
                    fileItem.innerHTML = `
                        <div class="flex items-center truncate">
                            <i class="fas fa-file ${isValidSize ? 'text-green-500' : 'text-red-500'} mr-3"></i>
                            <div class="truncate">
                                <p class="text-sm font-medium text-gray-800 truncate" title="${file.name}">${file.name}</p>
                                <p class="text-xs ${isValidSize ? 'text-gray-500' : 'text-red-500'}">
                                    ${fileSizeMB} MB
                                    ${!isValidSize ? ' (Too large!)' : ''}
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="removeFile(${index})" class="text-red-500 hover:text-red-700 p-1">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    
                    selectedFilesList.appendChild(fileItem);
                });
                
                // Show selected files container
                selectedFilesContainer.classList.remove('hidden');
                
                // Update total size display
                const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);
                const totalSizeText = document.createElement('div');
                totalSizeText.className = 'text-xs text-gray-500 mt-2 text-center';
                totalSizeText.textContent = `Total size: ${totalSizeMB} MB`;
                selectedFilesList.appendChild(totalSizeText);
                
                // Enable/disable upload button based on validation
                uploadButton.disabled = files.length === 0 || hasInvalidFile;
                
                // Update button text with count
                uploadButton.querySelector('span').textContent = 
                    `Upload & Create Zip (${files.length} file${files.length > 1 ? 's' : ''})`;
            }
            
            // Handle form submission
            uploadForm.addEventListener('submit', function(e) {
                const files = fileInput.files;
                
                if (files.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one file to upload.');
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
                            size: (file.size / (1024 * 1024)).toFixed(2)
                        });
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    let errorMessage = 'Some files exceed the 10MB limit:\n\n';
                    largeFiles.forEach(file => {
                        errorMessage += `• ${file.name} (${file.size} MB)\n`;
                    });
                    errorMessage += '\nPlease remove or reduce the size of these files.';
                    alert(errorMessage);
                    return;
                }
                
                // Show progress indicator
                uploadForm.classList.add('uploading');
            });
            
            // Global function to remove file
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
            };
        });
    </script>
</body>
</html>