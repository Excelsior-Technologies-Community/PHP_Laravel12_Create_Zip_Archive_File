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
                </div>
            @endif

            @if(session('success'))
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
            </div>
        </div>
    </div>

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
</body>
</html>