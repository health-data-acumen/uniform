import { Controller } from '@hotwired/stimulus';

/**
 * Bulk Action Controller
 * Manages bulk action buttons visibility and form submission
 */
export default class extends Controller {
    static targets = ['button', 'counter', 'form'];

    connect() {
        this.updateVisibility(0, []);
        
        // Listen for selection changes from bulk-select controller
        this.element.addEventListener('bulk-select:changed', (event) => {
            this.handleSelectionChanged(event.detail);
        });

        // Update form inputs when form is about to submit
        if (this.hasFormTarget) {
            this.formTarget.addEventListener('submit', () => {
                this.updateFormInputs();
            });
        }
    }

    /**
     * Handle selection changed event
     */
    handleSelectionChanged(detail) {
        this.updateVisibility(detail.count, detail.ids);
    }

    /**
     * Update visibility of bulk action buttons
     */
    updateVisibility(selectedCount, selectedIds) {
        const hasSelection = selectedCount > 0;

        // Show/hide bulk action buttons
        if (this.hasButtonTarget) {
            this.buttonTargets.forEach((button) => {
                if (hasSelection) {
                    button.classList.remove('hidden');
                } else {
                    button.classList.add('hidden');
                }
            });
        }

        // Update counter text
        if (this.hasCounterTarget && hasSelection) {
            this.counterTarget.textContent = `${selectedCount} selected`;
        }

        // Store selected IDs for form submission
        this.selectedIds = selectedIds;
    }

    /**
     * Update the form with selected submission IDs as array inputs
     */
    updateFormInputs() {
        if (!this.hasFormTarget || !this.selectedIds) return;

        const form = this.formTarget;

        // Remove existing ids[] inputs
        form.querySelectorAll('input[name="ids[]"]').forEach((input) => input.remove());

        // Add new inputs for each selected ID
        this.selectedIds.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
    }
}
