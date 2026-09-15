<script>
(function () {
    const counters = {};

    document.querySelectorAll('[data-items]').forEach((container) => {
        counters[container.dataset.items] = container.querySelectorAll('[data-row]').length;
    });

    document.querySelectorAll('[data-add-row]').forEach((button) => {
        button.addEventListener('click', () => {
            const key = button.dataset.addRow;
            const template = document.getElementById('tpl-' + key);
            const container = document.querySelector('[data-items="' + key + '"]');
            if (!template || !container) {
                return;
            }

            const index = counters[key] ?? container.querySelectorAll('[data-row]').length;
            counters[key] = index + 1;

            const html = template.innerHTML.replaceAll('__INDEX__', String(index));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const row = wrapper.firstElementChild;
            if (row) {
                container.appendChild(row);
            }
        });
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-row]');
        if (!button) {
            return;
        }

        const row = button.closest('[data-row]');
        const container = button.closest('[data-items]');
        if (!row || !container) {
            return;
        }

        if (container.querySelectorAll('[data-row]').length === 1) {
            row.querySelectorAll('input, textarea').forEach((input) => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });
            return;
        }

        row.remove();
    });
})();
</script>
