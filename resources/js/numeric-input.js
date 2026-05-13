document.addEventListener('input', (event) => {
    const input = event.target;

    if (!(input instanceof HTMLInputElement) || !input.dataset.numericInput) {
        return;
    }

    input.value = input.value.replace(/\D+/g, '');
});
