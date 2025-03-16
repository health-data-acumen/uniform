import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['content'];
  static values = { open: Boolean };

  openValueChanged() {
    this.contentTarget.classList.toggle('hidden', !this.openValue);
    this.contentTarget.setAttribute('aria-hidden', !this.openValue);
    this.element.setAttribute('aria-expanded', this.openValue.toString());
  }

  toggle() {
    this.openValue = !this.openValue;
  }
}
