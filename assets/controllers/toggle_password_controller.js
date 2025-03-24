import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['input', 'button'];
  static values = {
    showLabel: String,
    hideLabel: String
  };

  connect() {
    this.#updateLabel();
  }

  toggle() {
    this.inputTarget.type = this.inputTarget.type === 'password' ? 'text' : 'password';
    this.#updateLabel();
  }

  #updateLabel() {
    this.buttonTarget.textContent = this.inputTarget.type === 'password' ? this.showLabelValue : this.hideLabelValue;
  }
}
