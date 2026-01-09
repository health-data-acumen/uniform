import { Controller } from '@hotwired/stimulus';

/**
 * Bulk Actions Controller
 * Manages bulk selection and action buttons visibility
 */
export default class extends Controller {
    static targets = ['button', 'counter', 'selectAll', 'checkbox', 'submissionIds'];
    static outlets = ['form'];
    static values = {
        totalCount: Number,
    };

    connect() {
        this.updateVisibility();
        
        // Update hidden input when form is about to submit
        const form = this.element.querySelector('form');
        if (form) {
            form.addEventListener('submit', () => {
                this.updateSubmissionIds();
            });
        }
    }

    /**
     * Handle individual checkbox change
     */
    checkboxChanged() {
        this.updateVisibility();
        this.updateSelectAllState();
    }

    /**
     * Handle select all checkbox
     */
    selectAllChanged(event) {
        const isChecked = event.target.checked;
        this.checkboxTargets.forEach((checkbox) => {
            checkbox.checked = isChecked;
        });
        this.updateVisibility();
    }

    /**
     * Update visibility of bulk action buttons
     */
    updateVisibility() {
        const selectedCount = this.getSelectedCount();
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

        // Update hidden input with selected IDs
        this.updateSubmissionIds();
    }

    /**
     * Update the hidden input field with selected submission IDs
     */
    updateSubmissionIds() {
        if (this.hasSubmissionIdsTarget) {
            const selectedIds = this.getSelectedIds();
            this.submissionIdsTarget.value = selectedIds.join(',');
        }
    }

    /**
     * Update select all checkbox state
     */
    updateSelectAllState() {
        if (!this.hasSelectAllTarget) return;

        const selectedCount = this.getSelectedCount();
        const totalCount = this.checkboxTargets.length;

        if (selectedCount === 0) {
            this.selectAllTarget.checked = false;
            this.selectAllTarget.indeterminate = false;
        } else if (selectedCount === totalCount) {
            this.selectAllTarget.checked = true;
            this.selectAllTarget.indeterminate = false;
        } else {
            this.selectAllTarget.checked = false;
            this.selectAllTarget.indeterminate = true;
        }
    }

    /**
     * Get count of selected checkboxes
     */
    getSelectedCount() {
        return this.checkboxTargets.filter((checkbox) => checkbox.checked).length;
    }

    /**
     * Get all selected submission IDs
     */
    getSelectedIds() {
        return this.checkboxTargets
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value);
    }
}
