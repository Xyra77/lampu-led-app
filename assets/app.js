(() => {
    const rupiah = (amount) => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(amount) || 0);

    function updateTotals() {
        document.querySelectorAll('[data-line-collection]').forEach((collection) => {
            let total = 0;
            collection.querySelectorAll('.line-item').forEach((row) => {
                const select = row.querySelector('select[name="products[]"]');
                const qtyInput = row.querySelector('input[name="quantities[]"]');
                const priceInput = row.querySelector('input[name="prices[]"]');
                const selected = select && select.options[select.selectedIndex];
                const qty = Math.max(0, parseInt(qtyInput?.value || '0', 10) || 0);
                let price = 0;
                if (priceInput) {
                    price = Math.max(0, parseInt(priceInput.value || '0', 10) || 0);
                } else if (selected) {
                    price = Math.max(0, parseInt(selected.dataset.price || '0', 10) || 0);
                }
                const subtotal = qty * price;
                total += subtotal;
                const lineTotal = row.querySelector('[data-line-subtotal]');
                if (lineTotal) lineTotal.textContent = rupiah(subtotal);
                const productNote = row.querySelector('[data-product-note]');
                if (productNote && selected && selected.value) {
                    const stock = selected.dataset.stock;
                    productNote.textContent = stock !== undefined ? `Stok saat ini: ${stock} unit` : '';
                } else if (productNote) {
                    productNote.textContent = '';
                }
            });
            const totalTarget = document.querySelector(collection.dataset.totalTarget || '');
            if (totalTarget) totalTarget.textContent = rupiah(total);
        });
    }

    document.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-add-line]');
        if (addButton) {
            const collection = document.getElementById(addButton.dataset.addLine);
            const template = document.getElementById(addButton.dataset.template);
            if (collection && template) {
                collection.appendChild(template.content.cloneNode(true));
                updateTotals();
            }
        }

        const removeButton = event.target.closest('[data-remove-line]');
        if (removeButton) {
            const row = removeButton.closest('.line-item');
            const collection = row?.closest('[data-line-collection]');
            if (row && collection) {
                const rows = collection.querySelectorAll('.line-item');
                if (rows.length > 1) {
                    row.remove();
                } else {
                    row.querySelectorAll('select').forEach((control) => control.selectedIndex = 0);
                    row.querySelectorAll('input').forEach((control) => {
                        if (control.type === 'number') control.value = control.name === 'quantities[]' ? '1' : '0';
                    });
                }
                updateTotals();
            }
        }
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-line-collection] select, [data-line-collection] input')) updateTotals();
    });
    document.addEventListener('input', (event) => {
        if (event.target.matches('[data-line-collection] input')) updateTotals();
    });
    document.addEventListener('DOMContentLoaded', updateTotals);
    updateTotals();
})();
