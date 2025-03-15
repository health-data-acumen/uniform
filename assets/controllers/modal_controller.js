import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['dialog'];

  open() {
    this.dialogTarget.showModal();
    document.body.classList.add('overflow-hidden');

    this.dialogTarget.querySelector('input, textarea')?.focus();

    this.dispatch('opened');
  }

  close() {
    this.dialogTarget.close();
    document.body.classList.remove('overflow-hidden');

    this.dispatch('closed');
  }

  toggle() {
    this.dialogTarget.open ? this.close() : this.open();
  }
}
