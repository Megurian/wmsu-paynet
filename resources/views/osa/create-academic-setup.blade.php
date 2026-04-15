@extends('layouts.dashboard')
@php
    use Carbon\Carbon;

    $oldSemesterPreset = old('semester_preset', 'semestral');
    $oldSemesterLengths = old('semester_lengths', []);
    $oldSemesterStartsAt = old('semester_starts_at', []);
    $oldSemesterWillEndAt = old('semester_will_end_at', []);

    $presetOptions = [
        'semestral' => [
            'label' => 'Semestral System',
            'description' => '2 semesters with an optional summer term.',
        ],
        'trimester' => [
            'label' => 'Trimester',
            'description' => '3 evenly managed academic terms.',
        ],
        'quadmester' => [
            'label' => 'Quadmester',
            'description' => '4 shorter academic terms.',
        ],
        'custom' => [
            'label' => 'Custom',
            'description' => 'Set the count and relative length of each term.',
        ],
    ];
@endphp

@section('title', 'Create Academic Year')
@section('page-title', 'Create Academic Year')

@section('content')
<div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-xs uppercase tracking-[0.24em] text-gray-400">OSA setup</p>
        <h2 class="mt-2 text-3xl font-bold text-gray-800">Create Academic Year</h2>
        <p class="mt-1 text-sm text-gray-500">
            Generate the academic-year schedule, then manually fine-tune each semester date before saving.
        </p>
    </div>

    <a href="{{ route('osa.setup') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
        Back to OSA Setup
    </a>
</div>

@if($errors->any())
<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
    <ul class="list-disc pl-5 text-sm">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="mb-8 grid gap-4 xl:grid-cols-2">
    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Current context</p>
                <h3 class="mt-1 text-xl font-semibold text-gray-900">Latest academic year</h3>
            </div>
            @if($latestSchoolYear)
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Active</span>
            @endif
        </div>

        @if($latestSchoolYear)
            <div class="mt-4 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                <p class="text-sm font-semibold text-gray-800">
                    AY {{ Carbon::parse($latestSchoolYear->sy_start)->year }}–{{ Carbon::parse($latestSchoolYear->sy_end)->year }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ Carbon::parse($latestSchoolYear->sy_start)->format('F d, Y') }} – {{ Carbon::parse($latestSchoolYear->sy_end)->format('F d, Y') }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @forelse($existingSemesters as $semesterName)
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700 shadow-sm">
                            {{ $semesterName }}
                        </span>
                    @empty
                        <span class="text-sm text-gray-500">No semesters added yet.</span>
                    @endforelse
                </div>
            </div>
        @else
            <p class="mt-4 text-sm text-gray-500">No active academic year exists yet.</p>
        @endif
    </div>

    <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6 text-amber-900 shadow-sm">
        <p class="text-xs uppercase tracking-[0.2em] text-amber-700/80">Important</p>
        <h3 class="mt-1 text-xl font-semibold">The schedule is a suggestion, not a lock.</h3>
        <p class="mt-3 text-sm leading-6 text-amber-900/90">
            The system generates a timeline for convenience, but each semester start and end date can still be edited manually to account for breaks, delayed openings, or calendar changes.
        </p>
    </div>
</div>

<form id="academicYearForm" method="POST" action="{{ route('osa.setup.store') }}" class="grid gap-6 xl:grid-cols-2 xl:items-start" onsubmit="return confirmNewSchoolYear()">
        @csrf

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="sy_start" value="{{ old('sy_start') }}" class="w-full rounded-xl border-gray-300 focus:border-red-600 focus:ring-red-600" required>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="sy_end" value="{{ old('sy_end') }}" class="w-full rounded-xl border-gray-300 focus:border-red-600 focus:ring-red-600" required>
                </div>
            </div>

            <div>
                <p class="mb-3 text-sm font-semibold text-gray-800">Semester preset</p>
                <div class="relative">
                    <input type="hidden" name="semester_preset" id="semesterPresetInput" value="{{ $oldSemesterPreset }}">

                    <button type="button"
                            id="semesterPresetButton"
                            class="flex w-full items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-left shadow-sm transition hover:border-red-300 hover:bg-red-50/60 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <div>
                            <span class="block text-sm font-semibold text-gray-900" id="semesterPresetLabel">Semestral System</span>
                            <span class="block text-xs text-gray-500" id="semesterPresetDescription">2 semesters with an optional summer term.</span>
                        </div>
                        <svg class="h-5 w-5 text-gray-500 transition-transform" id="semesterPresetChevron" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div id="semesterPresetMenu" class="absolute left-0 right-0 top-full z-20 mt-2 hidden max-h-64 overflow-y-auto overflow-x-hidden rounded-2xl border border-gray-200 bg-white shadow-xl sm:max-h-72">
                        @foreach($presetOptions as $value => $option)
                            <button type="button"
                                    class="preset-option group flex w-full items-start gap-3 border-b border-gray-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-red-50"
                                    data-value="{{ $value }}"
                                    data-label="{{ $option['label'] }}"
                                    data-description="{{ $option['description'] }}">
                                <div class="mt-0.5 h-4 w-4 rounded-full border border-gray-300 bg-white group-hover:border-red-500 group-hover:bg-red-500"></div>
                                <div class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-gray-900">{{ $option['label'] }}</span>
                                    <span class="block text-xs text-gray-500">{{ $option['description'] }}</span>
                                </div>
                                <div class="relative flex-shrink-0 self-center">
                                    <span class="preset-option-tooltip pointer-events-none absolute right-full top-1/2 z-30 mr-3 hidden -translate-y-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2 text-xs font-medium text-white shadow-lg group-hover:block">
                                        {{ $option['description'] }}
                                    </span>
                                    <svg class="h-4 w-4 text-gray-400 opacity-0 transition group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div id="semestralOptions" class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                <label class="flex items-center gap-3 text-sm font-medium text-gray-700">
                    <input type="checkbox" id="includeSummer" name="include_summer" value="1" class="h-4 w-4 rounded border-gray-300 text-red-700 focus:ring-red-600" {{ old('include_summer') ? 'checked' : '' }}>
                    Include summer term for semestral setup
                </label>
            </div>

            <div id="customOptions" class="hidden rounded-2xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom semester count</label>
                        <input type="number" id="customSemesterCount" name="custom_semester_count" min="1" max="12" value="{{ old('custom_semester_count', 3) }}" class="w-full rounded-xl border-gray-300 focus:border-red-600 focus:ring-red-600">
                    </div>
                    <div class="flex items-end">
                        <p class="text-xs leading-5 text-gray-500">The preview will divide the academic-year span based on the relative length of each semester.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Semester lengths</p>
                    <p class="mt-1 text-xs leading-5 text-gray-500">Adjust each semester length before the system generates the final timeline.</p>
                </div>

                <div id="semesterLengthsContainer" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3"></div>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                The generated semester windows are only a starting point. You can edit each semester date before saving.
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm space-y-4 xl:sticky xl:top-6 self-start">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-gray-400">Timeline</p>
                    <h3 class="mt-1 text-xl font-semibold text-gray-900">Editable semester timeline</h3>
                </div>
                <span id="previewBadge" class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Semestral System</span>
            </div>

            <p id="previewSummary" class="text-sm text-gray-500">
                Pick the academic year dates to generate suggested semester windows, then edit them if the calendar needs breaks or overrides.
            </p>

            <div id="semesterTimelineContainer" class="space-y-3"></div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <a href="{{ route('osa.setup') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 text-center">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-red-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800">
                    Create Academic Year
                </button>
            </div>
        </div>
    </form>

<script>
    const semesterPresetInput = document.getElementById('semesterPresetInput');
    const semesterPresetButton = document.getElementById('semesterPresetButton');
    const semesterPresetMenu = document.getElementById('semesterPresetMenu');
    const semesterPresetLabel = document.getElementById('semesterPresetLabel');
    const semesterPresetDescription = document.getElementById('semesterPresetDescription');
    const semesterPresetChevron = document.getElementById('semesterPresetChevron');
    const presetOptions = Array.from(document.querySelectorAll('.preset-option'));
    const semestralOptions = document.getElementById('semestralOptions');
    const customOptions = document.getElementById('customOptions');
    const includeSummer = document.getElementById('includeSummer');
    const customSemesterCount = document.getElementById('customSemesterCount');
    const semesterLengthsContainer = document.getElementById('semesterLengthsContainer');
    const semesterTimelineContainer = document.getElementById('semesterTimelineContainer');
    const previewSummary = document.getElementById('previewSummary');
    const previewBadge = document.getElementById('previewBadge');
    const oldPreset = @json($oldSemesterPreset);
    const oldSemesterLengths = @json($oldSemesterLengths);
    const oldSemesterStartsAt = @json($oldSemesterStartsAt);
    const oldSemesterWillEndAt = @json($oldSemesterWillEndAt);

    function parseDateInput(value) {
        if (!value) {
            return null;
        }

        const parts = value.split('-').map(Number);
        if (parts.length !== 3 || parts.some(Number.isNaN)) {
            return null;
        }

        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function formatDateInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function formatPreviewDate(date) {
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    }

    function addDays(date, days) {
        const nextDate = new Date(date.getTime());
        nextDate.setDate(nextDate.getDate() + days);
        return nextDate;
    }

    function ordinalLabel(number) {
        if ([11, 12, 13].includes(number % 100)) {
            return `${number}th`;
        }

        return `${number}${{ 1: 'st', 2: 'nd', 3: 'rd' }[number % 10] || 'th'}`;
    }

    function getSelectedPreset() {
        return semesterPresetInput?.value || 'semestral';
    }

    function getPresetTitle(preset) {
        return {
            semestral: 'Semestral System',
            trimester: 'Trimester',
            quadmester: 'Quadmester',
            custom: 'Custom',
        }[preset] || 'Semestral System';
    }

    function getSemesterLabels(preset = getSelectedPreset()) {
        if (preset === 'semestral') {
            return includeSummer.checked ? ['1st SEMESTER', '2nd SEMESTER', 'SUMMER'] : ['1st SEMESTER', '2nd SEMESTER'];
        }

        if (preset === 'trimester') {
            return ['1st SEMESTER', '2nd SEMESTER', '3rd SEMESTER'];
        }

        if (preset === 'quadmester') {
            return ['1st SEMESTER', '2nd SEMESTER', '3rd SEMESTER', '4th SEMESTER'];
        }

        const count = Math.min(Math.max(parseInt(customSemesterCount.value, 10) || 0, 1), 12);

        return Array.from({ length: count }, (_, index) => ordinalLabel(index + 1) + ' SEMESTER');
    }

    function getDefaultSemesterLengths(preset = getSelectedPreset()) {
        if (preset === 'semestral') {
            return includeSummer.checked ? [5, 5, 1] : [5, 5];
        }

        if (preset === 'trimester') {
            return [4, 4, 4];
        }

        if (preset === 'quadmester') {
            return [3, 3, 3, 3];
        }

        const count = Math.min(Math.max(parseInt(customSemesterCount.value, 10) || 0, 1), 12);

        return Array.from({ length: count }, () => 1);
    }

    function getSemesterLengths() {
        return Array.from(semesterLengthsContainer.querySelectorAll('input'))
            .map((input) => parseInt(input.value, 10));
    }

    function allocateSemesterDays(totalDays, weights) {
        const allocations = [];
        let remainingDays = totalDays;
        let remainingWeight = weights.reduce((sum, weight) => sum + weight, 0);

        weights.forEach((weight, index) => {
            const semestersRemaining = weights.length - index;
            let days;

            if (semestersRemaining === 1) {
                days = remainingDays;
            } else {
                const idealDays = Math.floor((remainingDays * weight) / remainingWeight);
                const minimumDaysNeededForRest = semestersRemaining - 1;
                days = Math.max(1, Math.min(idealDays, remainingDays - minimumDaysNeededForRest));
            }

            allocations.push(days);
            remainingDays -= days;
            remainingWeight -= weight;
        });

        return allocations;
    }

    function renderSemesterLengthInputs(labels, values = []) {
        const fallbackValues = values.length ? values : (Array.isArray(oldSemesterLengths) ? oldSemesterLengths : []);

        semesterLengthsContainer.innerHTML = '';

        labels.forEach((label, index) => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <label class="block rounded-xl border border-gray-200 bg-white px-3 py-2 shadow-sm">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">${label}</span>
                    <input type="number" name="semester_lengths[]" min="1" value="${fallbackValues[index] || getDefaultSemesterLengths()[index] || 1}" class="w-full border-0 p-0 text-sm text-gray-900 focus:ring-0" />
                </label>
            `;
            semesterLengthsContainer.appendChild(wrapper.firstElementChild);
        });
    }

    function syncSemesterLengthInputs() {
        const labels = getSemesterLabels();
        const currentValues = Array.from(semesterLengthsContainer.querySelectorAll('input')).map((input) => input.value);

        if (semesterLengthsContainer.children.length !== labels.length) {
            renderSemesterLengthInputs(labels, currentValues);
        }
    }

    function getCurrentTimelineValues() {
        if (!semesterTimelineContainer) {
            return [];
        }

        return Array.from(semesterTimelineContainer.querySelectorAll('[data-semester-row]')).map((row) => ({
            start: row.querySelector('input[name="semester_starts_at[]"]')?.value || '',
            end: row.querySelector('input[name="semester_will_end_at[]"]')?.value || '',
        }));
    }

    function buildSuggestedSemesterTimeline(startDate, endDate, preset) {
        const weights = getSemesterLengths();
        const labels = getSemesterLabels(preset);

        if (weights.length !== labels.length || weights.some((value) => !Number.isFinite(value) || value <= 0)) {
            return [];
        }

        const totalDays = Math.floor((endDate - startDate) / 86400000) + 1;
        const allocations = allocateSemesterDays(totalDays, weights);

        let cursor = new Date(startDate.getTime());

        return allocations.map((days, index) => {
            const semesterEnd = index === allocations.length - 1 ? new Date(endDate.getTime()) : addDays(cursor, days - 1);
            const item = {
                label: labels[index],
                start: new Date(cursor.getTime()),
                end: semesterEnd,
            };

            cursor = addDays(semesterEnd, 1);

            return item;
        });
    }

    function renderSemesterTimeline(items, previousValues = []) {
        const fallbackStarts = Array.isArray(oldSemesterStartsAt) ? oldSemesterStartsAt : [];
        const fallbackEnds = Array.isArray(oldSemesterWillEndAt) ? oldSemesterWillEndAt : [];

        semesterTimelineContainer.innerHTML = items.map((item, index) => {
            const preservedValues = previousValues[index] || {};
            const startValue = preservedValues.start || fallbackStarts[index] || formatDateInput(item.start);
            const endValue = preservedValues.end || fallbackEnds[index] || formatDateInput(item.end);

            return `
                <div data-semester-row class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${item.label}</p>
                            <p class="mt-1 text-xs text-gray-500">Suggested window: ${formatPreviewDate(item.start)} to ${formatPreviewDate(item.end)}</p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">Editable</span>
                    </div>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Start Date</label>
                            <input type="date" name="semester_starts_at[]" value="${startValue}" class="w-full rounded-xl border-gray-300 focus:border-red-600 focus:ring-red-600">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">End Date</label>
                            <input type="date" name="semester_will_end_at[]" value="${endValue}" class="w-full rounded-xl border-gray-300 focus:border-red-600 focus:ring-red-600">
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updatePresetUi() {
        const preset = getSelectedPreset();
        const selectedOption = presetOptions.find((option) => option.dataset.value === preset);

        if (selectedOption) {
            semesterPresetLabel.textContent = selectedOption.dataset.label || getPresetTitle(preset);
            semesterPresetDescription.textContent = selectedOption.dataset.description || '';
        }

        presetOptions.forEach((option) => {
            const isSelected = option.dataset.value === preset;
            option.classList.toggle('bg-red-50', isSelected);
            option.classList.toggle('ring-1', isSelected);
            option.classList.toggle('ring-red-200', isSelected);
            option.classList.toggle('font-semibold', isSelected);
        });

        semestralOptions.classList.toggle('hidden', preset !== 'semestral');
        customOptions.classList.toggle('hidden', preset !== 'custom');

        previewBadge.textContent = getPresetTitle(preset);
    }

    function openPresetMenu() {
        semesterPresetMenu.classList.remove('hidden');
        semesterPresetChevron.classList.add('rotate-180');
    }

    function closePresetMenu() {
        semesterPresetMenu.classList.add('hidden');
        semesterPresetChevron.classList.remove('rotate-180');
    }

    function setPreset(value) {
        if (!semesterPresetInput) {
            return;
        }

        semesterPresetInput.value = value;
        closePresetMenu();
        const currentValues = Array.from(semesterLengthsContainer.querySelectorAll('input')).map((input) => input.value);
        renderSemesterLengthInputs(getSemesterLabels(value), currentValues);
        updateAcademicYearPreview();
    }

    function updateAcademicYearPreview() {
        const startInput = document.querySelector('input[name="sy_start"]');
        const endInput = document.querySelector('input[name="sy_end"]');
        const startDate = parseDateInput(startInput?.value);
        const endDate = parseDateInput(endInput?.value);
        const preset = getSelectedPreset();

        updatePresetUi();
        syncSemesterLengthInputs();

        if (!startDate || !endDate) {
            semesterTimelineContainer.innerHTML = '';
            previewSummary.textContent = 'Pick the academic year dates to generate suggested semester windows, then edit them if the calendar needs breaks or overrides.';
            return;
        }

        if (startDate > endDate) {
            semesterTimelineContainer.innerHTML = '';
            previewSummary.textContent = 'The start date must be earlier than the end date.';
            return;
        }

        const suggestedTimeline = buildSuggestedSemesterTimeline(startDate, endDate, preset);

        if (!suggestedTimeline.length) {
            semesterTimelineContainer.innerHTML = '';
            previewSummary.textContent = 'Enter valid semester lengths to generate the preview.';
            return;
        }

        const currentValues = getCurrentTimelineValues();

        renderSemesterTimeline(suggestedTimeline, currentValues);
        previewSummary.textContent = `The selected academic year is divided into ${suggestedTimeline.length} editable semester block${suggestedTimeline.length === 1 ? '' : 's'}.`;
    }

    function confirmNewSchoolYear() {
        const start = document.querySelector('input[name="sy_start"]').value;
        const end = document.querySelector('input[name="sy_end"]').value;
        const preset = getSelectedPreset();
        const labels = getSemesterLabels(preset);
        const weights = getSemesterLengths();
        const semesterStartInputs = Array.from(document.querySelectorAll('input[name="semester_starts_at[]"]'));
        const semesterEndInputs = Array.from(document.querySelectorAll('input[name="semester_will_end_at[]"]'));
        const schoolYearStart = parseDateInput(start);
        const schoolYearEnd = parseDateInput(end);

        if (!start || !end) {
            alert('Please select both start and end dates.');
            return false;
        }

        if (start > end) {
            alert('Start date cannot be after the end date.');
            return false;
        }

        if (weights.length !== labels.length || weights.some((value) => !Number.isFinite(value) || value <= 0)) {
            alert('Please enter valid semester lengths for every term.');
            return false;
        }

        if (semesterStartInputs.length !== labels.length || semesterEndInputs.length !== labels.length) {
            alert('Please generate the semester timeline before submitting the form.');
            return false;
        }

        let previousEndDate = null;

        for (let index = 0; index < labels.length; index++) {
            const semesterStart = parseDateInput(semesterStartInputs[index]?.value);
            const semesterEnd = parseDateInput(semesterEndInputs[index]?.value);

            if (!semesterStart || !semesterEnd) {
                alert(`Please enter valid start and end dates for ${labels[index]}.`);
                return false;
            }

            if (semesterStart > semesterEnd) {
                alert(`${labels[index]} must start on or before its end date.`);
                return false;
            }

            if (schoolYearStart && semesterStart < schoolYearStart) {
                alert(`${labels[index]} starts before the selected academic year begins.`);
                return false;
            }

            if (schoolYearEnd && semesterEnd > schoolYearEnd) {
                alert(`${labels[index]} ends after the selected academic year ends.`);
                return false;
            }

            if (previousEndDate && semesterStart <= previousEndDate) {
                alert(`${labels[index]} must start after the previous semester ends.`);
                return false;
            }

            previousEndDate = semesterEnd;
        }

        return confirm('Create this academic year and save the suggested semester schedule with your manual date edits?');
    }

    document.addEventListener('DOMContentLoaded', () => {
        semesterPresetInput.value = oldPreset || 'semestral';

        if (oldPreset === 'custom' && Array.isArray(oldSemesterLengths) && oldSemesterLengths.length) {
            customSemesterCount.value = oldSemesterLengths.length;
        }

        renderSemesterLengthInputs(getSemesterLabels(), Array.isArray(oldSemesterLengths) ? oldSemesterLengths : []);

        if (semesterPresetButton && semesterPresetMenu) {
            semesterPresetButton.addEventListener('click', () => {
                semesterPresetMenu.classList.contains('hidden') ? openPresetMenu() : closePresetMenu();
            });
        }

        presetOptions.forEach((option) => {
            option.addEventListener('click', () => {
                setPreset(option.dataset.value || 'semestral');
            });
        });

        customSemesterCount.addEventListener('input', () => {
            const currentValues = Array.from(semesterLengthsContainer.querySelectorAll('input')).map((input) => input.value);
            renderSemesterLengthInputs(getSemesterLabels(), currentValues);
            updateAcademicYearPreview();
        });

        document.querySelectorAll('input[name="sy_start"], input[name="sy_end"], #includeSummer, #customSemesterCount')
            .forEach((input) => input.addEventListener('input', updateAcademicYearPreview));

        semesterLengthsContainer.addEventListener('input', updateAcademicYearPreview);

        document.addEventListener('click', (event) => {
            if (!semesterPresetButton || !semesterPresetMenu) {
                return;
            }

            const isInsidePresetPicker = semesterPresetButton.contains(event.target) || semesterPresetMenu.contains(event.target);
            if (!isInsidePresetPicker) {
                closePresetMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closePresetMenu();
            }
        });

        if (oldPreset === 'custom' && Array.isArray(oldSemesterLengths) && oldSemesterLengths.length) {
            renderSemesterLengthInputs(getSemesterLabels(), oldSemesterLengths);
            Array.from(semesterLengthsContainer.querySelectorAll('input')).forEach((input, index) => {
                input.value = oldSemesterLengths[index] || 1;
            });
        }

        updateAcademicYearPreview();
    });
</script>
@endsection
