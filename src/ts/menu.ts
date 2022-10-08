document.addEventListener('click', event => {
    const target = event.target as HTMLElement;
    const id = target.dataset.iglooMenu;
    if (!id) {
        return;
    }

    event.preventDefault()
    const menus = document.querySelectorAll('[data-igloo-menu="' + id + '"]:not(button)');
    menus.forEach(menu => menu.classList.toggle('invisible'));
});
