import ModalController from './modal_controller.js';

/* stimulusFetch: 'lazy' */
export default class extends ModalController {
  static targets = ['dialog', 'content', 'placeholder'];

  /**
   * @type {MutationObserver}
   */
  observer;

  initialize() {
    this.updateDialogVariant = this.updateDialogVariant.bind(this);
  }

  connect() {
    this.#registerObserver();
    document.addEventListener(
      'turbo:before-fetch-request',
      this.updateDialogVariant,
    );
  }

  disconnect() {
    this.observer?.disconnect();
    document.removeEventListener(
      'turbo:before-fetch-request',
      this.updateDialogVariant,
    );
  }

  showPlaceholder() {
    if (this.hasContentTarget && this.hasPlaceholderTarget) {
      this.contentTarget.replaceChildren(
        this.placeholderTarget.content.cloneNode(true),
      );
    }
  }

  updateDialogVariant(evt) {
    /** @type {CustomEvent<{ fetchRequest: FetchRequest }>} */
    const { fetchRequest } = evt.detail;
    if (!fetchRequest) {
      return;
    }

    if (fetchRequest.target.dataset.modalVariant?.length) {
      this.dialogTarget.setAttribute(
        'data-variant',
        fetchRequest.target.dataset.modalVariant,
      );
    } else if (this.dialogTarget.hasAttribute('data-variant')) {
      this.dialogTarget.removeAttribute('data-variant');
    }
  }

  #registerObserver() {
    if (!this.hasContentTarget) {
      return;
    }

    this.observer = new MutationObserver(() => {
      const shouldClose = this.contentTarget.children.length === 0;

      if (shouldClose) {
        this.close();
        return;
      }

      if (!this.dialogTarget.open) {
        this.open();
      }
    });
    this.observer.observe(this.contentTarget, { childList: true });
  }
}
