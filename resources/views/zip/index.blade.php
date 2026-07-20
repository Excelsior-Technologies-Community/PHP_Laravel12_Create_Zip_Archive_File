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
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    <div class="container mx-auto px-4 py-8">

        <div class="max-w-7xl mx-auto">

            <!-- Header -->

            <div class="text-center mb-10">

                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg mb-4">

                    <i class="fas fa-file-archive text-3xl text-white"></i>

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
</body>

</html>