(() => {
    const modal = document.getElementById('confirm-modal');
    const title = document.getElementById('confirm-modal-title');
    const message = document.getElementById('confirm-modal-message');
    const confirmButton = document.getElementById('confirm-modal-submit');
    const cancelButton = document.getElementById('confirm-modal-cancel');
    const closeTriggers = modal ? modal.querySelectorAll('[data-confirm-close]') : [];
    let activeForm = null;
    let activeSubmitter = null;

    if (!modal || !title || !message || !confirmButton || !cancelButton) {
        return;
    }

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        activeForm = null;
        activeSubmitter = null;
    };

    const openModal = (form, confirmSource, submitter = null) => {
        activeForm = form;
        activeSubmitter = submitter;
        title.textContent = confirmSource.dataset.confirmTitle || 'Antes de continuar';
        message.textContent = confirmSource.dataset.confirmMessage || 'Esta accion no se puede deshacer.';
        confirmButton.textContent = confirmSource.dataset.confirmButton || 'Confirmar';
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.confirmed === 'true') {
            delete form.dataset.confirmed;
            return;
        }

        const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
        const confirmSource = submitter && submitter.dataset.confirmMessage
            ? submitter
            : (form.dataset.confirmMessage ? form : null);

        if (!confirmSource) {
            return;
        }

        event.preventDefault();
        openModal(form, confirmSource, submitter);
    });

    confirmButton.addEventListener('click', () => {
        if (!activeForm) {
            closeModal();
            return;
        }

        activeForm.dataset.confirmed = 'true';
        if (activeSubmitter && typeof activeForm.requestSubmit === 'function') {
            activeForm.requestSubmit(activeSubmitter);
        } else {
            if (activeSubmitter && activeSubmitter.name) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = activeSubmitter.name;
                hiddenInput.value = activeSubmitter.value;
                activeForm.appendChild(hiddenInput);
            }

            activeForm.submit();
        }
        closeModal();
    });

    cancelButton.addEventListener('click', closeModal);
    closeTriggers.forEach((trigger) => trigger.addEventListener('click', closeModal));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
