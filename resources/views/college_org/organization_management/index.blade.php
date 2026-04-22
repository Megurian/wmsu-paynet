@extends('layouts.dashboard')

@section('title', 'Organization Management')
@section('page-title', 'Organization Management')

@section('content')

<div class="bg-white rounded-lg shadow">

    <div class="border-b">
        <nav class="flex space-x-6 px-6 pt-4">
            <button onclick="switchTab('info')" id="tab-info"
                class="tab-btn pb-2 border-b-2 border-red-700 font-semibold text-red-700">
                Organization Info
            </button>

            <button onclick="switchTab('officers')" id="tab-officers"
                class="tab-btn pb-2 border-b-2 border-transparent text-gray-500 hover:text-red-700">
                Officers
            </button>
        </nav>
    </div>

    <div class="p-6">

        <div id="content-info" class="tab-content">
            @include('college_org.organization_management.info')
        </div>

        <div id="content-officers" class="tab-content hidden">
            @include('college_org.organization_management.officers')
        </div>

    </div>

</div>

<script>
    function switchTab(tab) {
        // Hide all
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

        // Remove active styles
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('border-red-700', 'text-red-700', 'font-semibold');
            el.classList.add('border-transparent', 'text-gray-500');
        });

        // Show selected
        document.getElementById('content-' + tab).classList.remove('hidden');

        // Activate tab
        const activeTab = document.getElementById('tab-' + tab);
        activeTab.classList.add('border-red-700', 'text-red-700', 'font-semibold');
        activeTab.classList.remove('border-transparent', 'text-gray-500');
    }
</script>

@endsection