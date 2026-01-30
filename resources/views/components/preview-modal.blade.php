@props(['id' => 'previewModal', 'title' => 'Preview'])

<div id="{{ $id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black bg-opacity-50"></div>

    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4 z-10">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-bold">{{ $title }}</h3>
            <button type="button" class="text-gray-600 hover:text-gray-800" onclick="hidePreviewModal('{{ $id }}')" aria-label="Close preview">×</button>
        </div>

        <div id="{{ $id }}-content" class="p-6 text-sm text-gray-700">
            {{-- Dynamic content will be placed here via showPreviewModal --}}
        </div>

        <div id="{{ $id }}-status" class="px-6 py-2 text-sm text-gray-700"></div>

        <div class="px-6 py-4 border-t flex justify-end gap-3">
            <button type="button" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400" onclick="hidePreviewModal('{{ $id }}')">Cancel</button>
            <button type="button" id="{{ $id }}-confirm" class="bg-red-700 text-white px-4 py-2 rounded hover:bg-red-800">Confirm</button>
        </div>
    </div>
</div>

<script>
    function showPreviewModal(id, htmlContent, formId) {
        const modal = document.getElementById(id);
        if (!modal) return;
        const content = document.getElementById(`${id}-content`);
        content.innerHTML = htmlContent;
        modal.dataset.targetForm = formId || '';

        // Decide whether the Confirm button should be enabled based on current validation state
        const codeTaken = (typeof window !== 'undefined' && typeof window.codeAvailable !== 'undefined' && window.codeAvailable === false);
        const emailTaken = (typeof window !== 'undefined' && typeof window.emailAvailable !== 'undefined' && window.emailAvailable === false);
        const pending = (typeof window !== 'undefined' && ((window.codePending) || (window.emailPending)));
        const pwMismatch = (typeof window !== 'undefined' && window.passwordMismatch === true);

        const shouldDisable = codeTaken || emailTaken || pending || pwMismatch;

        setPreviewConfirmState(id, !shouldDisable, shouldDisable ? 'Fix validation issues before confirming.' : 'Ready to submit.');

        modal.classList.remove('hidden');
        // Attach confirm handler that respects allowSubmit
        const confirmBtn = document.getElementById(`${id}-confirm`);
        confirmBtn.onclick = function() {
            const allow = modal.dataset.allowSubmit === 'true';
            if (!allow) {
                // brief feedback
                const status = document.getElementById(`${id}-status`);
                if (status) {
                    status.textContent = 'Please resolve validation issues before confirming.';
                    status.className = 'px-6 py-2 text-sm text-red-600';
                }
                return;
            }

            const targetFormId = modal.dataset.targetForm;
            if (targetFormId) {
                const f = document.getElementById(targetFormId);
                if (f) {
                    f.submit();
                }
            }
        }
    }

    function setPreviewConfirmState(id, enabled, message = '') {
        const modal = document.getElementById(id);
        const confirmBtn = document.getElementById(`${id}-confirm`);
        const status = document.getElementById(`${id}-status`);
        if (!confirmBtn || !modal) return;
        confirmBtn.disabled = !enabled;
        if (enabled) {
            confirmBtn.classList.remove('opacity-60', 'cursor-not-allowed');
            confirmBtn.classList.remove('bg-gray-400');
            confirmBtn.classList.add('bg-red-700');
            if (status) { status.className = 'px-6 py-2 text-sm text-green-600'; status.textContent = message || 'Ready to submit.'; }
            modal.dataset.allowSubmit = 'true';
        } else {
            confirmBtn.classList.add('opacity-60', 'cursor-not-allowed');
            confirmBtn.classList.remove('bg-red-700');
            confirmBtn.classList.add('bg-gray-400');
            if (status) { status.className = 'px-6 py-2 text-sm text-gray-700'; status.innerHTML = message || 'Verifying...'; }
            modal.dataset.allowSubmit = 'false';
        }
    }

    function hidePreviewModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
    }
</script>