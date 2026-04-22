<div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Organization Information</h2>
        <p class="text-sm text-gray-500 mt-1">
            Update your organization name and logo. These will be visible across the system.
        </p>
    </div>

    <div class="mb-6">
        @if($organization->mother_organization_id)
            <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                University Organization Office
            </span>
        @else
            <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                College Organization
            </span>
        @endif
    </div>

    <div class="grid md:grid-cols-2 gap-10">

        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Organization Logo</h3>
            <p class="text-xs text-gray-500 mb-4">
                Click the image to upload a new logo.
            </p>

            <form id="logoForm"
                  action="{{ route('college_org.info.updateLogo') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <label for="organization_logo" class="cursor-pointer relative inline-block">

                    @if($organization?->logo)
                        <img src="{{ asset('storage/' . $organization->logo) }}"
                             class="h-40 w-40 object-cover rounded-lg border">
                    @else
                        <div class="h-40 w-40 flex items-center justify-center rounded-lg border bg-gray-100 text-gray-400">
                            No Logo
                        </div>
                    @endif

                    <div class="absolute bottom-2 right-2 bg-red-700 text-white p-2 rounded-full shadow">
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
        </div>

        <!-- NAME -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Organization Name</h3>
            <p class="text-xs text-gray-500 mb-4">
                Keep your organization name clear and official.
            </p>

            <form action="{{ route('college_org.info.updateName') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="flex gap-2">
                    <input type="text"
                        name="organization_name"
                        value="{{ $organization->name }}"
                        class="flex-1 border rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-700 text-sm">

                    <button type="button"
                        onclick="openConfirmModal({
                            title: 'Update Organization Name',
                            message: 'Do you want to proceed?',
                            confirmText: 'Update',
                            onConfirm: () => this.closest('form').submit()
                        })"
                        class="px-5 py-3 bg-red-700 text-white rounded-lg hover:bg-red-600 text-sm">

                        Save
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>