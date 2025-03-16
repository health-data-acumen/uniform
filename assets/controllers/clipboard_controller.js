import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['source', 'button'];
  static values = {
    successText: { type: String, default: 'Copied!' }
  };

  connect() {
    console.log('Clipboard controller connected');
  }

  copy() {
    const value = (this.sourceTarget.value ?? this.sourceTarget.textContent).trim()

    navigator.clipboard
      .writeText(value)
      .then(() => {
        console.log('Copied to clipboard');
        this.dispatch('copied', {detail: { value }});

        if (this.hasButtonTarget && this.hasSuccessTextValue) {
          const oldContent = this.buttonTarget.innerHTML;
          this.buttonTarget.textContent = this.successTextValue;
          setTimeout(() => {
            this.buttonTarget.innerHTML = oldContent;
          }, 2000);
        }
      })
  }
}
