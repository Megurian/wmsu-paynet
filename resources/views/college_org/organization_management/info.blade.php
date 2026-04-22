<div class="bg-white p-8 rounded shadow">

    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center md:text-left">
        Organization Information
    </h2>

    {{-- TYPE BADGE --}}
    <div class="mb-6 text-sm text-gray-600">
        @if($organization->mother_organization_id)
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full">
                University Organization Office
            </span>
        @else
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full">
                College Organization
            </span>
        @endif
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:space-x-12 space-y-6 md:space-y-0">

        <!-- LOGO -->
        <form id="logoForm"
              action="{{ route('college_org.info.updateLogo') }}"
              method="POST"
              enctype="multipart/form-data"
              class="relative flex-shrink-0 text-center md:text-left">

            @csrf
            @method('PUT')

            <label class="block text-gray-700 font-medium mb-2">
                Organization Logo
            </label>

            <label for="organization_logo" class="cursor-pointer relative inline-block">

                @if($organization?->logo)
                    <img src="{{ asset('storage/' . $organization->logo) }}"
                         class="h-48 w-48 object-cover rounded border border-gray-300 mx-auto md:mx-0">
                @else
                    <div class="h-48 w-48 flex items-center justify-center rounded border border-gray-300 bg-gray-100 text-gray-500 mx-auto md:mx-0">
                        No Logo
                    </div>
                @endif

                <div class="absolute bottom-2 right-2 bg-red-800 text-white rounded-full p-2 hover:bg-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6-6 3 3-6 6H9v-3z" />
                    </svg>
                </div>
            </label>

            <input type="file"
                   name="organization_logo"
                   id="organization_logo"
                   class="hidden"
                   onchange="confirmLogoUpdate(this)">
        </form>

        <!-- NAME -->
        <form action="{{ route('college_org.info.updateName') }}"
              method="POST"
              class="flex-1">

            @csrf
            @method('PUT')

            <label class="block text-gray-700 font-medium mb-2">
                Organization Name
            </label>

            <div class="flex items-center space-x-2">

                <input type="text"
                       name="organization_name"
                       value="{{ $organization->name ?? '' }}"
                       class="flex-1 border rounded px-3 py-3 focus:outline-none focus:ring focus:ring-red-700 text-lg"
                       placeholder="Enter Organization Name">

                <button type="button"
                        onclick="openConfirmModal({
                            title: 'Update Organization Name',
                            message: 'Do you want to proceed?',
                            confirmText: 'Update',
                            onConfirm: () => this.closest('form').submit()
                        })"
                        class="px-6 py-3 bg-red-800 text-white rounded hover:bg-red-700 whitespace-nowrap">

                    Update
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function confirmLogoUpdate(input) {
    if (input.files.length === 0) return;

    openConfirmModal({
        title: 'Update Logo',
        message: 'Do you want to update the organization logo?',
        confirmText: 'Upload',
        onConfirm: () => document.getElementById('logoForm').submit()
    });
}
</script>