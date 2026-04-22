@extends('layouts.dashboard')

@section('title', 'Organization Management')
@section('page-title', 'Organization Management')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h1 class="text-2xl font-bold text-gray-800">Organization Management</h1>
        <p class="text-gray-500 mt-1">
            Manage your organization profile, branding, and officers.
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">

        <div class="border-b px-6">
            <nav class="flex space-x-8">
                <button onclick="switchTab('info')" id="tab-info"
                    class="tab-btn py-4 border-b-2 text-sm font-semibold border-red-700 text-red-700">
                    Organization Info
                </button>

                <button onclick="switchTab('officers')" id="tab-officers"
                    class="tab-btn py-4 border-b-2 text-sm font-semibold border-transparent text-gray-500 hover:text-red-700">
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
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-red-700', 'text-red-700');
        el.classList.add('border-transparent', 'text-gray-500');
    });

    document.getElementById('content-' + tab).classList.remove('hidden');

    const activeTab = document.getElementById('tab-' + tab);
    activeTab.classList.add('border-red-700', 'text-red-700');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
}

function confirmLogoUpdate(input) {
    if (!input.files || !input.files[0]) return;

    openConfirmModal({
        title: 'Update Logo',
        message: 'Do you want to update the organization logo?',
        confirmText: 'Upload',
        onConfirm: () => {
            document.getElementById('logoForm').submit();
        },
        onCancel: () => {
            input.value = ""; // reset file input if cancelled
        }
    });
}
</script>

@endsection