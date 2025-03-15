import { config, visit } from '@hotwired/turbo';

config.forms.confirm = (message, element) => {
  const confirmDialog = document.querySelector('[data-turbo-confirm-dialog]');

  if (!confirmDialog) {
    return confirm(message);
  }

  const confirmButton = confirmDialog.querySelector(
    '[data-behaviour="confirm"]',
  );
  const cancelButton = confirmDialog.querySelector('[data-behaviour="cancel"]');

  // Customizing the dialog
  // Start by setting the title and message
  const $title = confirmDialog.querySelector('[data-part="title"]');
  const $message = confirmDialog.querySelector('[data-part="message"]');

  if ($title) {
    $title.textContent = element.dataset.confirmTitle || 'Are you sure ?';
  }
  $message.textContent = message;

  // Then, we can customize the buttons
  const submitMethod = element.querySelector('input[name="_method"]')?.value;
  confirmButton.textContent = element.dataset.confirmLabel || 'Continue';
  confirmButton.dataset.variant =
    submitMethod?.toLowerCase() === 'delete' ? 'destructive' : 'primary';

  return new Promise((resolve) => {
    confirmButton?.addEventListener(
      'click',
      () => {
        confirmDialog.close();
        resolve(true);
      },
      { once: true },
    );

    cancelButton?.addEventListener(
      'click',
      () => {
        confirmDialog.close();
        resolve(false);
      },
      { once: true },
    );

    confirmDialog.showModal();
  });
};

document.addEventListener('turbo:before-fetch-request', (evt) => {
  const { fetchOptions } = evt.detail;
  const turboFrameId = fetchOptions.headers['Turbo-Frame'];
  if (turboFrameId && evt.target.dataset.turboFrameRedirect !== 'false') {
    fetchOptions.headers['Turbo-Frame-Redirect'] = 1;
  }
});

document.addEventListener('turbo:before-fetch-response', (evt) => {
  const { fetchResponse } = evt.detail;
  if (fetchResponse.response.headers.has('X-Turbo-Location')) {
    evt.preventDefault();
    visit(fetchResponse.response.headers.get('X-Turbo-Location'), {
      action: 'advance',
    });
  }
});
