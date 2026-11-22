document.querySelectorAll('[data-repeat]').forEach((group) => {
    const rows = group.querySelector('[data-rows]');
    const template = group.querySelector('template');
    const add = group.querySelector('[data-add]');
    let nextIndex = rows.children.length;

    add.addEventListener('click', () => {
        const row = template.content.cloneNode(true);

        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace('__INDEX__', String(nextIndex));
        });

        rows.append(row);
        nextIndex++;
        rows.lastElementChild.querySelector('input:not([type="hidden"])').focus();
    });

    rows.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove]');

        if (remove) {
            remove.closest('[data-row]').remove();
            add.focus();
        }
    });
});
