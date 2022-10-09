document.addEventListener('click', event => {
    let target = event.target as HTMLElement|null;
    while (target && !target.dataset.iglooMenu) {
        target = target.parentElement;
    }

    const id = target?.dataset.iglooMenu;
    if (!id) {
        return;
    }

    event.preventDefault()
    const menus = document.querySelectorAll('[data-igloo-menu="' + id + '"]:not(button)');
    menus.forEach(menu => menu.classList.toggle('tw-invisible'));
});
