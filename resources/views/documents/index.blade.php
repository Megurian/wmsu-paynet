@extends('layouts.dashboard')

@section('title', 'Documents')

@section('content')
<div class="p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Documents</h1>
            <p class="text-gray-600">Manage your important documents like Accreditation Certification and Resolution of Collection</p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
                <ul class="text-red-700">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-green-800">✓ {{ session('success') }}</p>
            </div>
        @endif

        <!-- Document Upload Section -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Upload Document</h2>

            <form action="{{ $role === 'university_org' ? route('university_org.documents.store') : route('college_org.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Document Type Select -->
                    <div>
                        <label for="document_type" class="block text-sm font-semibold text-gray-700 mb-2">
                            Document Type <span class="text-red-600">*</span>
                        </label>
                        <select 
                            id="document_type" 
                            name="document_type" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        >
                            <option value="">-- Select Document Type --</option>
                            <option value="Accreditation Certification">Accreditation Certification</option>
                            <option value="Resolution of Collection">Resolution of Collection</option>
                        </select>
                        @error('document_type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Input -->
                    <div>
                        <label for="file" class="block text-sm font-semibold text-gray-700 mb-2">
                            Choose File <span class="text-red-600">*</span>
                        </label>
                        <input 
                            type="file" 
                            id="file" 
                            name="file" 
                            required
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        />
                        <p class="text-sm text-gray-500 mt-1">
                            Allowed formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 5MB)
                        </p>
                        @error('file')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button 
                        type="submit" 
                        class="w-full md:w-auto px-6 py-2 bg-red-800 hover:bg-red-900 text-white font-semibold rounded-lg transition duration-200"
                    >
                        Upload Document
                    </button>
                </div>
            </form>
        </div>

        <!-- Documents List Section -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Uploaded Documents</h2>
            </div>

            @if($documents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Document Type</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">File Name</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">File Size</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Related Fee</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Uploaded By</th>
                                <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700">Uploaded Date</th>
                                <th class="px-8 py-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($documents as $document)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-8 py-4 text-sm text-gray-900">
                                        @if($document->document_type === 'Accreditation Certification')
                                            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                                Accreditation
                                            </span>
                                        @elseif($document->document_type === 'Resolution of Collection')
                                            <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold">
                                                Resolution
                                            </span>
                                        @else
                                            <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                                Supporting
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-900">
                                        <div class="font-medium">{{ $document->original_file_name }}</div>
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-600">
                                        {{ number_format($document->file_size / 1024, 2) }} KB
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-600">
                                        @php
                                            $fee = \App\Models\Fee::where('supporting_document_id', $document->id)->first();
                                        @endphp
                                        @if($fee)
                                            <span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-medium">
                                                {{ $fee->fee_name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic">—</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-600">
                                        {{ $document->uploadedBy->name }}
                                    </td>
                                    <td class="px-8 py-4 text-sm text-gray-600">
                                        {{ $document->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-8 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <!-- Preview Button -->
                                            <a 
                                                href="{{ $role === 'university_org' ? route('university_org.documents.preview', $document) : route('college_org.documents.preview', $document) }}" 
                                                target="_blank"
                                                class="inline-flex items-center px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded transition duration-200"
                                                title="Preview document"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Preview
                                            </a>

                                            <!-- Delete Button -->
                                            <form 
                                                action="{{ $role === 'university_org' ? route('university_org.documents.destroy', $document) : route('college_org.documents.destroy', $document) }}" 
                                                method="POST" 
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit"
                                                    class="inline-flex items-center px-3 py-1 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded transition duration-200"
                                                    title="Delete document"
                                                >
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-8 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No documents uploaded yet</h3>
                    <p class="text-gray-600 mb-4">Start by uploading your first document using the form above</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // File size validation on client side
    document.getElementById('file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                alert('File size exceeds 5MB limit. Please choose a smaller file.');
                this.value = '';
            }
        }
    });
</script>
@endsection
