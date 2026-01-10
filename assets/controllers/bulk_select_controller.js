import { Controller } from '@hotwired/stimulus';

/**
 * Bulk Select Controller
 * Manages checkbox selection state and select all functionality
 */
export default class extends Controller {
    static targets = ['selectAll', 'checkbox'];
    static values = {
        totalCount: Number,
    };

    connect() {
        this.updateSelectAllState();
    }

    /**
     * Handle individual checkbox change
     */
    checkboxChanged() {
        this.updateSelectAllState();
        this.dispatchSelectionChanged();
    }

    /**
     * Handle select all checkbox
     */
    selectAllChanged(event) {
        const isChecked = event.target.checked;
        this.checkboxTargets.forEach((checkbox) => {
            checkbox.checked = isChecked;
        });
        this.dispatchSelectionChanged();
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

    /**
     * Dispatch custom event when selection changes
     */
    dispatchSelectionChanged() {
        const event = new CustomEvent('bulk-select:changed', {
            detail: {
                count: this.getSelectedCount(),
                ids: this.getSelectedIds(),
            },
            bubbles: true,
        });
        this.element.dispatchEvent(event);
    }
}
