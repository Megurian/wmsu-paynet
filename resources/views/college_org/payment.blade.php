@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<h2 class="text-2xl font-bold mb-4">Dashboard</h2>
<p>Welcome, {{ Auth::user()->name }}. </p>

<div class="mt-6 max-w-md">
    <label class="block text-sm font-medium text-gray-700 mb-1">Search Student</label>
    <input type="text" id="studentSearch" placeholder="Enter name or student ID"
        class="w-full border rounded-lg px-3 py-2 focus:ring-red-600 focus:border-red-600" autocomplete="off">
    
    <ul id="searchResults" class="border rounded mt-1 bg-white hidden max-h-60 overflow-auto"></ul>
</div>

<script>
const searchInput = document.getElementById('studentSearch');
const resultsList = document.getElementById('searchResults');

searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    if (!query) {
        resultsList.innerHTML = '';
        resultsList.classList.add('hidden');
        return;
    }

    fetch(`/college/students/search?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            resultsList.innerHTML = '';
            if (data.length === 0) {
                resultsList.classList.add('hidden');
                return;
            }

            data.forEach(student => {
                const li = document.createElement('li');
                li.textContent = `${student.name} (${student.student_id})`;
                li.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';
                li.addEventListener('click', () => {
                    searchInput.value = `${student.name} (${student.student_id})`;
                    resultsList.innerHTML = '';
                    resultsList.classList.add('hidden');
                    // You can optionally store student.id in a hidden field for payment form
                });
                resultsList.appendChild(li);
            });
            resultsList.classList.remove('hidden');
        });
});

// Hide dropdown if clicked outside
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !resultsList.contains(e.target)) {
        resultsList.classList.add('hidden');
    }
});
</script>
@endsection
